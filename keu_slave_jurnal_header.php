<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

# Get Attr
$proses = $_GET['proses'];
$data = $_POST;
$jenispersetujuan='JM';
$createtime=date("Y-m-d H:i:s");

switch($proses) {
	
	/*
	case'getkeg':
		$optkeg="<option value=''></option>";
		if(substr($data['kodeasset'],0,2)=='PB'){
			$str="select * from ".$dbname.".setup_kegiatan where kelompok='PBR'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optkeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']."-".$bar['namakegiatan']."</option>";
			}
		}else{
			$str="select * from ".$dbname.".setup_kegiatan ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				$optkeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']."-".$bar['namakegiatan']."</option>";
			}
		}
		echo $optkeg;
	break;
	*/
	
	case'getkeg':
		$optkeg="<option value=''></option>";
		$str="select * from ".$dbname.".setup_kegiatan where noakun='".$data['noakun']."' ";
		// echo $str;exit("Error:A");
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$optkeg.="<option value=".$bar['kodekegiatan'].">".$bar['kodekegiatan']."-".$bar['namakegiatan']."</option>";
		}
		echo $optkeg;
	break;
	case 'add':
		$tgl = str_replace("-","",tanggalsystemn($data['tanggal']));
		$sPeriode="select * from ".$dbname.".setup_periodeakuntansi  where kodeorg='".$data['kodeunit']."' and tutupbuku=0 order by periode desc";
		$rPeriode=fetchdata($sPeriode);
		$tglakutansi=str_replace("-","", $rPeriode[0]['tanggalmulai']);
			
		if($tglaktansi>$tgl){
			exit("Error:Tanggal transaksi diluar periode aktif  Periode aktif : ".$rPeriode[0]['periode']." , Tanggal transaksi : ".substr(tanggalsystemn($data['tanggal']),0,7)." ");
		}
		
		if($data['noreferensi']==''){
			 exit("Warning:No. Dokumen tidak boleh kosong");
		}
		
                // Validasi Kurs
                if($data['matauang']!='IDR') {
                        $qKurs = selectQuery($dbname,'setup_matauangrate','kurs',"kode='".$data['matauang']."' and daritanggal='".
                                                                 tanggalsystem($data['tanggal'])."'");
                        $resKurs = fetchData($qKurs);
                        if(empty($resKurs)) exit("Warning: Kurs ".$data['matauang']." di tanggal ".$data['tanggal']." belum ada");
                }

        #=============== Get Nomor Jurnal
        $optinduk=makeOption($dbname,"organisasi",'kodeorganisasi,induk',"kodeorganisasi='".$data['kodeunit']."'");
        $whereNo = "kodekelompok='".$data['kodejurnal']."' and kodeorg='".$optinduk[$data['kodeunit']]."' 
				and kodeunit='".$data['kodeunit']."' and periode='".substr(tanggalsystemn($data['tanggal']),0,7)."'";
        $query = selectQuery($dbname,'keu_5kelompokjurnal','nokounter',$whereNo);
		// exit("Error:$query");
        $noKon = fetchData($query);
        $tmpC = $noKon[0]['nokounter'];
        $tmpC++;
        $counter = addZero($tmpC,3);
        $data['nojurnal'] = tanggalsystem($data['tanggal'])."/".$data['kodeunit']."/".$data['kodejurnal']."/".$counter;
        $nojur = $data['nojurnal'];
        #ambil periode akuntansi yang aktif
        $sAktif="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$data['kodeunit']."' and tutupbuku=0 order by periode asc limit 1";
		// echo $sAktif;
        $rAktif=fetchData($sAktif);
        $periodeAktif=str_replace("-","", $rAktif[0]['tanggalmulai']);

        #mencegah input data dengan tanggal lebih kecil dari periode awal akuntansi
           $sekarang=  tanggalsystem($data['tanggal']);
           if($sekarang<$periodeAktif){
                echo "Validation Error : Date out or range, Periode Accounting ".$periodeAktif." ";
           break;                        
           }
         #======================================================
		 
		 
		#= cek jika noref tidak boleh sama dengan transaksi
		$str="select count(*) as jumlahkasbank from ".$dbname.".keu_kasbankht where notransaksi='".$data['noreferensi']."' or novoucher='".$data['noreferensi']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$jumlahkasbank=$bar['jumlahkasbank'];
		}
		
		if($jumlahkasbank>0){
			exit("Warning:Nomor referensi sudah terdaftar di kasbank");
		}
		 
		 

        #=============== Insert Process
        # Column
        $column = array('kodejurnal','tanggal','noreferensi','matauang',
            'nojurnal','tanggalentry','posting','totaldebet','totalkredit',
            'amountkoreksi','autojurnal','kurs');

        # Add Default Data
        $data['tanggal'] = tanggalsystem($data['tanggal']);
        $data['tanggalentry'] = date('Ymd');
        $data['posting'] = 0;
        $data['totaldebet'] = 0;
        $data['totalkredit'] = 0;
        $data['amountkoreksi'] = 0;
        $data['autojurnal'] = 0;
        $data['kurs'] = 0;

        // for($i=1; $i<3; $i++){
            // $per['persetujuan'.$i]=$data['persetujuan'.$i];
            // unset($data['persetujuan'.$i]);
        // }
		
		unset($data['kodeunit']);

        # Query
        $query = insertQuery($dbname,'keu_jurnalht',$data,$column);
        try{
            $owlPDO->exec($query);

            // for($i=1; $i<3; $i++){
                // $str="insert into ".$dbname.".approval (`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`) values 
                      // ('".$data['nojurnal']."','".$jenispersetujuan."','".$i."','".$per['persetujuan'.$i]."')";
                // try{
                    // $owlPDO->exec($str); 
                // }catch(PDOException $e){
                    // echo " Gagal," . addslashes($e->getMessage());
                // }
            // }

        }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        
        $updData = array('nokounter'=>$tmpC);
        $query2 = updateQuery($dbname,'keu_5kelompokjurnal',$updData,$whereNo);
        try{$owlPDO->exec($query2); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
		
		##INSERT FILE UPLOAD TO DB
		foreach($_SESSION['imgjurnalm'] as $key=>$val){
			$strx="insert into ".$dbname.".listfileupload(notransaksi,namafile,formaticon,createdby,createdtime) values('".$nojur."','".$val['namafile']."','".$val['filetype']."','".$_SESSION['standard']['userid']."','".$createtime."')";
			try{
				$owlPDO->exec($strx);
			}catch (PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
			}
		}
		
        echo $nojur;
	break;
    case 'edit':
        $data = $_POST;
        unset($data['nojurnal']);
        $data['tanggal'] = tanggalsystem($data['tanggal']);
		
		
		$tgl = str_replace("-","",$data['tanggal']);
		$sPeriode="select * from ".$dbname.".setup_periodeakuntansi  where kodeorg='".$data['kodeunit']."' and tutupbuku=0 order by periode desc";
		$rPeriode=fetchdata($sPeriode);
		$tglakutansi=str_replace("-","", $rPeriode[0]['tanggalmulai']);
			
		if($tglaktansi>$tgl){
			exit("Error:Tanggal transaksi diluar periode aktif  Periode aktif : ".$rPeriode[0]['periode']." , Tanggal transaksi : ".substr(tanggalsystemn($data['tanggal']),0,7)." ");
		}
		

        // Validasi Kurs
        if($data['matauang']!='IDR') {
                $qKurs = selectQuery($dbname,'setup_matauangrate','kurs',"kode='".$data['matauang']."' and daritanggal='".tanggalsystem($data['tanggal'])."'");
                $resKurs = fetchData($qKurs);
                if(empty($resKurs)) exit("Warning: Kurs ".$data['matauang']." di tanggal ".$data['tanggal']." belum ada");
        }

        // for($i=1; $i<3; $i++){
            // $per['persetujuan'.$i]=$data['persetujuan'.$i];
            // unset($data['persetujuan'.$i]);
        // }

        $query = updateQuery($dbname,'keu_jurnalht',$data,"nojurnal='".$_POST['nojurnal']."'");
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        
        $query="UPDATE `".$dbname."`.`keu_jurnaldt` SET `tanggal` = '".$data['tanggal']."' WHERE `nojurnal` = '".$_POST['nojurnal']."'";
        try{$owlPDO->exec($query); }catch (PDOException $e) {print " Gagal  ! Please print screen and report to IT: " . $e->getMessage() . "<br/>"; die(); }

        // for($i=1; $i<3; $i++){
            // $str="update ".$dbname.".approval set karyawanid='".$per['persetujuan'.$i]."' where notransaksi='".$_POST['nojurnal']."' 
                // and jenispersetujuan='".$jenispersetujuan."' and level='".$i."'";
            // try{
                // $owlPDO->exec($str); 
            // }catch(PDOException $e){
                // echo " Gagal," . addslashes($e->getMessage());
            // }
        // }
		
		##INSERT FILE UPLOAD TO DB
		$strx="delete from ".$dbname.".listfileupload where notransaksi='".$_POST['nojurnal']."'";
		$owlPDO->exec($strx);
		foreach($_SESSION['imgjurnalm'] as $key=>$val){
			$strx="insert into ".$dbname.".listfileupload(notransaksi,namafile,formaticon,createdby,createdtime) values('".$_POST['nojurnal']."','".$val['namafile']."','".$val['filetype']."','".$_SESSION['standard']['userid']."','".$createtime."')";
			try{
				$owlPDO->exec($strx);
			}catch (PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; die(); 
			}
		}
        
        $data['tanggal'] = tanggalnormal($data['tanggal']);
        echo json_encode($data);
            
        break;
    case 'delete':

      
        $query = selectQuery($dbname,'keu_jurnaldt','nojurnal',"nojurnal='".$data['nojurnal']."'");
        $res = fetchData($query);
        if(empty($res)) {
            $qDel = "delete from `".$dbname."`.`keu_jurnalht` where nojurnal='".$data['nojurnal']."'";
                try{
                    $owlPDO->exec($qDel); 

                    $strht = "delete from " . $dbname . ".approval where notransaksi='".$data['nojurnal']."'";
                    try {
                        $owlPDO->exec($strht);
                    } catch (PDOException $e) {
                        print " Gagal: " . $e->getMessage() . "\n";
                        die();
                    }

                }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
        } else {
            echo "Warning : Please delete detail transaction in the first place";
            exit;
        }
        case 'loadHeader':
                #== Get Journal Header
                $period = $_SESSION['org']['period'];
				if($data['unitcr']==''){
					if($_SESSION['empl']['tipelokasitugas']=='KEBUN' || $_SESSION['empl']['tipelokasitugas']=='PABRIK'){
						// $jmlist="substr(nojurnal,10,4) = '".$_SESSION['empl']['lokasitugas']."' ";
						// $listunit = $_SESSION['empl']['lokasitugas'];
						$listunit ="'".$_SESSION['empl']['lokasitugas']."'";
						$jmlist="substr(nojurnal,10,4) = ".$listunit." ";
					}else{
						//$where = "kodeorg in (".getOrgDetail(2).")";
						$listunit = getOrgDetail(2);
						$jmlist="substr(nojurnal,10,4) in (".$listunit.")";
					}
				}else{
					$listunit ="'".$data['unitcr']."'";
					$jmlist="substr(nojurnal,10,4) in (".$listunit.")";
				}
				// echo $jmlist;exit();
				
								
				$arrlistunit=explode(',',str_replace("'","",$listunit));
				// echo"<pre>";
				// print_r($arrlistunit);
				// exit();
				foreach($arrlistunit as $key){
					// $arrker[]=$key;
					
					$str="select * from ".$dbname.".setup_periodeakuntansi where kodeorg='".$key."' and tutupbuku=0 order by periode asc limit 1";
					// echo $str;exit();
					$res=fetchdata($str);
					foreach($res as $bar){
						$dttglmulai[$bar['kodeorg']]=$bar['tanggalmulai'];
					}
				}


				$str="select tanggalmulai from ".$dbname.".setup_periodeakuntansi where kodeorg in (".$listunit.") and tutupbuku=0 order by periode asc limit 1";
				$res=fetchdata($str);
				foreach($res as $bar){
					$tanggalmulai=$bar['tanggalmulai'];
				}
				
				
				
				
               $where = " tanggal>=".$tanggalmulai." and ".$jmlist." and kodejurnal='M' and revisi=0 and autojurnal = '0' ";
			   // echo $where;
			   // exit();
				/*
				  $where = " tanggal>=".$period['start'].
                        " and ".$jmlist." and kodejurnal='M'".
                        " and revisi=0 and noreferensi not like '%/BK/%' and noreferensi not like '%-GI-%' and noreferensi not like '%-GR-%' and noreferensi not like '%/TBM/%' and noreferensi not like '%/TM/%'";
				*/		
						
						
                $query = selectQuery($dbname,'keu_jurnalht',"kodejurnal,nojurnal,tanggal,noreferensi,matauang,totaldebet,totalkredit",$where,"nojurnal desc");
				// exit("Error:$query");
			   $resTab = fetchData($query);

                $table = "";
                foreach($resTab as $key=>$row) {
                        // $tablex = "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
                        // $tablex .= "<td id='pdf_".$key."'><img src='images/".$_SESSION['theme']."/pdf.jpg' ";
                        // $tablex .= "class='zImgBtn' onclick='detailPDF(".$key.",event)'></td>";
                        // $tablex .= "<td id='delHead_".$key."'>";
                        // $tablex .= "<img src='images/".$_SESSION['theme']."/delete.png' ";
                        // $tablex .= "class='zImgBtn' onclick='delHead(".$key.")'></td>";
                        $notablex = 1;
                        foreach($row as $col=>$dat) {
                                if($col=='tanggal') {
                                        $dat = tanggalnormal($dat);
                                }

                                // $query = selectQuery($dbname,'keu_jurnalht','autojurnal',"nojurnal='".$row['nojurnal']."'");
                                // $res = fetchData($query);
                                // $bar =$res[0];
                                // $autojurnal=$bar['autojurnal'];
                                // if ($autojurnal==1){
                                        // continue;
                                // }
								
								if($dttglmulai[substr($row['nojurnal'],9,4)]>$row['tanggal']){
									continue;
								}
												

                                if($notablex==1)
                                {
                                    $notablex=2;
                                    $table .= "<tr id='tr_".$key."' class='rowcontent' style='cursor:pointer'>";
                                    $table .= "<td id='pdf_".$key."'><img src='images/".$_SESSION['theme']."/pdf.jpg' ";
                                    $table .= "class='zImgBtn' onclick='detailPDF(".$key.",event)'></td>";
                                    $table .= "<td id='delHead_".$key."'>";
                                    $table .= "<img src='images/".$_SESSION['theme']."/delete.png' ";
                                    $table .= "class='zImgBtn' onclick='delHead(".$key.")'></td>";
                                }

                                $dtplus=0;
                                $dtmin=0;
                                $krngan=0;
                                $sData=$owlPDO->query("select distinct sum(jumlah) as plus from ".$dbname.".keu_jurnaldt where nojurnal='".$row['nojurnal']."' and jumlah>0");
                                $sData->setFetchMode(PDO::FETCH_ASSOC);
                                $rData= $sData->fetch();
                                $dtplus=$rData['plus'];

								$klik="passEditHeader(".$key.")";
                              
                                $sData=$owlPDO->query("select distinct sum(jumlah) as min , keterangan as keterangan from ".$dbname.".keu_jurnaldt where nojurnal='".$row['nojurnal']."' and jumlah<0");
                                $sData->setFetchMode(PDO::FETCH_ASSOC);
                                $rData= $sData->fetch();
                                $dtmin=$rData['min']*(-1);

                                $sCekData=$owlPDO->query("select sum(jumlah) as selisih from ".$dbname.".keu_jurnaldt where nojurnal='".$row['nojurnal']."'");
                                $sCekData->setFetchMode(PDO::FETCH_ASSOC);
                                $rCekData= $sCekData->fetch();
                                $dbgr="";
                                if(intval($rCekData['selisih'])!=0)
                                {
                                 $dbgr="bgcolor='red'";
                                }
                                // if(number_format($dtplus,0) == 0 && number_format($dtmin,0) == 0)
                                // {
                                 // $dbgr="bgcolor='red'";
                                // }

                                if($col=='totaldebet')
                                {
                                        $table .= "<td id='".$col."_".$key."' onclick='".$klik."' align=right ".$dbgr." title='".$_SESSION['lang']['selisih']." ".intval($rCekData['selisih'])."'>".number_format($dtplus,0)."</td>";
                                }
                                elseif($col=='totalkredit')
                                {
                                        $table .= "<td id='".$col."_".$key."' onclick='".$klik."' align=right ".$dbgr." title='".$_SESSION['lang']['selisih']." ".intval($rCekData['selisih'])."'>".number_format($dtmin,0)."</td>";
                                }
                                else
                                {
                                        $table .= "<td id='".$col."_".$key."' onclick='".$klik."' ".$dbgr.">".$dat."</td>";
                                }

                        }
						if($notablex==2)
                        {
                            $notablex=0;
                            $table .= "<td id='".$col."_".$key."' onclick='".$klik."' ".$dbgr.">".$rData['keterangan']."</td>";
							
							## SHOW IMAGE UPLOAD
							$nox=0;
							$showimg="";
							$strx="select * from ".$dbname.".listfileupload where notransaksi='".$row['nojurnal']."'";
							$resx=fetchData($strx);
							foreach($resx as $valx){
								$nox++;
								if($nox==1){
									$showimg.=$nox.". <a href='fileupload/jm/".$valx['namafile']."' title='Klik disini untuk download file' target='_blank'>".$valx['namafile']."</a>";					
								}else{
									$showimg.="<br>".$nox.". <a href='fileupload/jm/".$valx['namafile']."' title='Klik disini untuk download file' target='_blank'>".$valx['namafile']."</a>";					
								}
							}
							$table .= "<td>".$showimg."</td>";
							
                            $table .= "</tr>";
                        }
                        // $table .= "<td id='".$col."_".$key."' onclick='".$klik."' ".$dbgr." title='".$ttl[$row['nojurnal']]."'>".$stat[$autojurnal]."</td>";
                        //$table .= "</tr>";
                }
                echo $table;
                break;
				
	case'addfileupload':
		$tgl = date("YmdHis");
		$data = $_POST;
		
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $newfilename."_".$tgl."".$filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];

				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					if($_FILES['file']['size'] <= 2048000){
						$newdata = array(
							'namafile'=>$filename,
							'filetype'=>$filetype
						);
						
						if($_SESSION['imgjurnalm'] != array()){
							foreach($_SESSION['imgjurnalm'] as $key=>$row)
							{
								if($row['namafile'] == $filename)
								{
									exit("Warning : Item ini sudah pernah diinput sebelumnya.");
								}
							}
							array_push($_SESSION['imgjurnalm'],$newdata);
						}else{
							array_push($_SESSION['imgjurnalm'],$newdata);
						}
						move_uploaded_file($file_tmpname,"fileupload/jm/$filename");
					}else{
						exit("warning : Ukuran file upload maksimal 2 Mb");
					}
				}else{
					exit("Warning : Format file upload harus .jpg | .jpeg | .png | .pdf | .xls | .xlsx | .doc | .docx");
				}
			}
		}
	break;
	
	case 'loadfiles':
		$tab="";
		$no=0;
		if(count($_SESSION['imgjurnalm']) > 0){
			foreach($_SESSION['imgjurnalm'] as $key=>$val){
				$no++;
				$tab.="<tr class='rowcontent'>";
				$tab.="<td style='text-align:right'>".$no."</td>";
				$tab.="<td title='Klik disini untuk download file'><a href='fileupload/jm/".$val['namafile']."' download>".$val['namafile']."</a></td>";
				$tab.="<td style='text-align:center'>
					<img title='Delete' class=resicon onclick=\"deletefile('".$val['namafile']."')\" src='images/delete_32.png'/
				</td>";
				$tab.="</tr>";
			}
		}else{
			$tab.="<tr class='rowcontent'>";
			$tab.="<td style='text-align:center' colspan=3>".$_SESSION['lang']['datanotfound']."</td>";
			$tab.="</tr>";
		}
        echo $tab;
    break;
	
	case'deletefile':
        foreach($_SESSION['imgjurnalm'] as $key=>$row){
			if($row['namafile'] == $data['namafile']){
				// $path = "fileupload/jm/".$data['namafile'];
				// unlink($path);
				unset($_SESSION['imgjurnalm'][$key]);
			}
		}
    break;
	
    default:
        break;
}
?>