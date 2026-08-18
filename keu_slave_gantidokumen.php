<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}


$str = "select *  from " . $dbname . ".keu_5akun";
$res = fetchdata($str);
foreach($res as $bar){
	$nmakun[$bar['noakun']]=$bar['namaakun'];
	$tipeakun[$bar['noakun']]=$bar['tipeakun'];
}
$str = "select *  from " . $dbname . ".setup_klpkegiatan";
$res = fetchdata($str);
foreach($res as $bar){
	$nmkel[$bar['kodeklp']]=$bar['namakelompok'];
}


$str = "select *  from " . $dbname . ".organisasi where induk=''";
$res = fetchdata($str);
foreach($res as $bar){
	$holding=$bar['kodeorganisasi'];
}



$path   = "fileupload/keu_gantidokumen/";
$str="select * from ".$dbname.".setup_filesize where transaksi='keu_gantidokumen'";
$res=fetchdata($str);
foreach($res as $bar){
	$filesize=$bar['filesize'];
}
$optkriteria="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$emodul = "GDOKFIN";
@$arrmodul = getmodulefil($emodul);
foreach($arrmodul as $key=>$val){
	@$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
}

$arrstatus=array('1'=>'AKTIF','0'=>'NON AKTIF');
$arrpremi=array('1'=>'Premi di BKM dikunci','0'=>'Premi di BKM tidak dikunci');

switch($method){
	case 'loaddata':
		$tab="<table id=mytable class='sortable' cellspacing='1' cellpadding='5' border='0' width=100%>
				<thead>
					<tr class=rowheader>
						<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nourut']."</th>
						<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['notransaksi']."</th>
						<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nodokumen']." Lama</th>
						<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['nodokumen']." Baru</th>
						<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['tanggal']."</th>
						<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['keterangan']."</th>
						<th rowspan=2 style='text-align:center;'>".$_SESSION['lang']['proses']."</th>
						<th style='text-align:center;' colspan=4>".$_SESSION['lang']['action']."</th>
					</tr>
					<tr class=rowheader>
						<th  style='display:none;'></th>
						<th  style='display:none;'></th>
						<th  style='display:none;'></th>
					</tr>
				</thead>
				<tbody >";		

		$str = "SELECT * FROM ".$dbname.".keu_gantidokumen ORDER BY notransaksi ASC";
		$res = fetchdata($str);
		foreach($res as $bar){
			$no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center;'>".$no."</td>";
			$tab.="<td style='text-align:left;'>".$bar['notransaksi']."</td>";
			$tab.="<td style='text-align:left;'>".$bar['nodokumenlama']."</td>";
			$tab.="<td style='text-align:left;'>".$bar['nodokumenbaru']."</td>";
			$tab.="<td style='text-align:center;'>".tanggalnormal($bar['tanggal'])."</td>";
			$tab.="<td style='text-align:left;'>".nl2br($bar['keterangan'])."</td>";
			if($bar['proses'] == '1') {
				$tab.="<td style='text-align:center;'>".$_SESSION['lang']['sudahproses']."</td>";
			}else{
				$tab.="<td style='text-align:center;'>".$_SESSION['lang']['belumproses']."</td>";
			}
			
			
			
			if($bar['posting'] == '0' || $bar['posting'] == '3') {
				$tab.= "<td style='text-align:center;width:25px'>
							<img src='images/application/application_edit.png' class='resicon' title='Edit' onclick=\"editdata('".$bar['notransaksi']."','".$bar['nodokumenlama']."','".$bar['nodokumenbaru']."','".tanggalnormal($bar['tanggal'])."','".$bar['keterangan']."')\";>
						</td>";
				$tab.= "<td style='text-align:center;width:25px'>
							<img src='images/delete_32.png' class='resicon' title='Delete' onclick=del('".$bar['notransaksi']."');>
						</td>";
				$tab.= "<td style='text-align:center;width:25px'>
							<img src='images/zoom.png' class='resicon' title='Detail' onclick=\"viewdetail('".$bar['notransaksi']."')\";>
						</td>";	
				$tab.= "<td style='text-align:center;width:25px'>
							<img src='images/icons/04/10/01.png' class='resicon' title='Posting' onclick=\"post('".$bar['notransaksi']."')\";>
						</td>";
						
			} else {
				
				
				$tab.= "<td style='text-align:center;width:25px'></td>";
				$tab.= "<td style='text-align:center;width:25px'></td>";
				$tab.= "<td style='text-align:center;width:25px'><img src='images/zoom.png' class='resicon' title='Detail' onclick=\"viewdetail('".$bar['notransaksi']."')\";></td>";		
				$tab.= "<td style='text-align:center;'><img src='images/icons/04/10/02.png' class='resicon' title='Posted'\";></td>";		
			}
					
			$tab.="</tr>";
		}
		
		$tab.="</tbody>
			</table>";

		echo $tab;
	break;

	case 'addnew':		
		$tab= "<table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['nodokumen']." Lama</td>
					<td>:</td>
					<td><input type=text class=myinputtext style='width:345px;height:30px;font-size:14px;' id=nodoklama></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['nodokumen']." Baru</td>
					<td>:</td>
					<td><input type=text class=myinputtext style='width:345px;height:30px;font-size:14px;' id=nodokbaru></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['tanggal']."</td>
					<td>:</td>
					<td><input type=text class=myinputtext id=tanggal readonly onmousemove=setCalendar(this.id) onkeypress=return false; maxlength=10 style='width:345px;height:30px;font-size:14px;'/></td>
				</tr>
				<tr>
					<td valign=top>".$_SESSION['lang']['keterangan']."</td>
					<td valign=top>:</td>
					<td><textarea id=keterangan class=myinputtext style='width:345px;height:70px;font-size:12px;'></textarea></td>
				</tr>
				
                <tr>
                    <td colspan=2>
                    	<input type=hidden id=method value=insert>
                    	<input type=hidden class=myinputtext disabled style='width:345px;height:30px;font-size:14px;' id=notransaksi>
                    </td>
                    <td>
						<button onclick=simpan(); style='width:100px;height:30px' class=mybutton>Save</button>						
                    </td>
                </tr>
            </table>";
			
			
			$tab.="<div id=detail>";
			$tab.="<fieldset>
				<legend>" . $_SESSION['lang']['form'] . " " . $_SESSION['lang']['upload'] . "</legend>
				<table cellspacing='1' border='0'>
					<tr>
						<td>".$_SESSION['lang']['kriteria']."</td>
						<td>:</td>
						<td>
							<select id='kriteriaefil'>". $optkriteria."</select>
						</td>
					</tr>
					<tr>
						<td>".$_SESSION['lang']['file']."</td>
						<td>:</td>
						<td>
							<input type='file' name='upload' id='upload' class=mybutton>
						</td>
					</tr>
					<tr>
						<td colspan=2></td>
						<td>
							<button class=mybutton onclick='submitfile()'>Submit</button>
							<button class=mybutton onclick='loadfiles()'>Selesai</button>
						</td>
						
					</tr>
				</table>
				<br>
				<br>
				<table class='sortable' cellspacing='1' border='0' width=100%>
					<thead>
					<tr class=rowheader>
						<td align='center'>".$_SESSION['lang']['nourut']."</td>
						<td align='center'>File Type</td>
						<td align='center'>Kriteria</td>
						<td align='center'>Filename</td>
						<td align='center'>Action</td>
					</tr>
					</thead>
					<tbody id='listfiles'>
					</tbody>
				</table>";
			$tab.="</div>";

		echo $tab;
	break;
	
	
	case'submitfile':
	
		// $filesize=1;
	
		#= jadikan try commi
		try {
			
			$owlPDO->beginTransaction();
			
			$tgl = date("YmdHis");
			$his = date("His");
			$nmTemp=str_replace('-','',str_replace('/','',$param['notransaksi']));

			if ($_FILES['file']['size'] > $filesize){
				throw new PDOException("Ukuran File melebihi ".number_format($filesize/1024,2)." Kb; ukuran file ini ".number_format($_FILES['file']['size']/1024,2)." Kb");
			}

			if($param['fileupload']!=''){
				if($_FILES['file']['error']==0){    
					$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$filename = $param['kriteriaefil']."_".$nmTemp."_".$his."".$filetype;
					$file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
					if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.rar')||($filetype=='.gz')||($filetype=='.zip')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					$str = "insert into ".$dbname.".listfileupload values ('','".$param['notransaksi']."','".$filename."','".$filetype."','".$param['kriteriaefil']."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename,$file_tmpname);
					}else{
						throw new PDOException("Format file upload tidak boleh ".$filetype);
					}
				}
			}
			
			if (!file_exists($path.$filename)) {
				throw new PDOException("File gagal diupload");
			}
			
			$owlPDO->commit();
			
		} catch(PDOException $e) {
		
			$owlPDO->rollback();
			echo "Warningsistem: Gagal melakukan penyimpanan data \n" . addslashes($e->getMessage());

		}			
		
    break;
	
	
	case 'deletefile':
        // $namafile=$param['namafile'];
        $str="delete from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' and namafile='".$param['namafile']."'"; 
		// exit('error'.$str);
        try{
            $owlPDO->exec($str);
            // $pathx = $path.str_replace('/','',$param['namafile']);
            // unlink($pathx);
        }
        catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
	break;
	
	case'loadfiles':
		$form='';
		$str="select * from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' ";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			@$no++;
			@$icon = seticonfile($bar['formaticon']);
			$form.= "<tr class=rowcontent >";
				$form.="<td style='text-align:center'>".$no."</td>";
				$form.="<td align='center'><img src=".$icon." class=zImgBtn></a></td>";
				$form.= "<td>".getcriterianame($bar['kriteriaefil'])."</td>";
				$form.= "<td><a href='".$path.str_replace('/','',$bar['namafile'])."' download>".$bar['namafile']."</td>";
				$form.= "<td><a href='".$path.str_replace('/','',$bar['namafile'])."' download><img src=images/uploader/dwnld8.png class=zImgBtn  title='download'></a>&nbsp<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletefile('".$bar['notransaksi']."','".$bar['namafile']."');\" ></td>";
			$form.= "<tr>";
		}
		echo $form;
    break;  
	
	

	case 'insert':
		try {
			$owlPDO->beginTransaction();
			$notransaksi = generatenotransaksi(tanggalsystemn($param['tanggal']));
			$data = array(
				'notransaksi' => $notransaksi,
				'nodokumenlama' => $param['nodoklama'],
				'nodokumenbaru' => $param['nodokbaru'],
				'tanggal' => tanggalsystemn($param['tanggal']),
				'keterangan' => $param['keterangan'],
				'createby' => $_SESSION['standard']['userid'],
				'createtime' => date('Y-m-d'),
				'posting' => '0',
				'proses' => '0'
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'keu_gantidokumen',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback(); 
			echo "Errorcode, " . addslashes($e->getMessage()); 
			die();
		}

		echo $notransaksi;
		
	break;

	case 'update':		
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'nodokumenlama' => $param['nodoklama'],
					'nodokumenbaru' => $param['nodokbaru'],
					'tanggal' => tanggalsystemn($param['tanggal']),
					'keterangan' => $param['keterangan']
				);
				$where = "notransaksi='".$param['notransaksi']."'";
				 $query = updateQuery($dbname,'keu_gantidokumen',$data,$where); 
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;

	case 'delete':
		try {
			$owlPDO->beginTransaction();
			
			$str = "DELETE FROM ".$dbname.".keu_gantidokumen WHERE notransaksi='".$param['notransaksi']."'";
			$owlPDO->exec($str);
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Errorcode, " . addslashes($e->getMessage());
			die();
		}		
	break;

	case 'formposting':
        $countApp = getCountApproval('GDOKFIN');
        $nmKar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

        $tab = "<table>";

        for($i=1; $i<=$countApp; $i++){
            $optpersetujuan = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

            $str = "SELECT * FROM ".$dbname.".setup_approval WHERE jenispersetujuan='GDOKFIN' AND level='".$i."'";
            $res = fetchdata($str);
            foreach($res as $key=>$val){
                $optpersetujuan .= "<option value='".$val['karyawanid']."'>".$nmKar[$val['karyawanid']]."</option>";
            }

            $tab .= "<tr>
                        <td>".$_SESSION['lang']['persetujuan']." ".$i."</td> 
                        <td>:</td>
                        <td><select style='width:345px;height:30px;font-size:14px;' id=persetujuan".$i.">".$optpersetujuan."</select></td>
                    </tr>";  
        }   
        if ($countApp > 0) {
        	$html = "<button class=mybutton style='width:100px;height:30px' onclick=posting('".$param['notransaksi']."','".$countApp."')>".$_SESSION['lang']['save']."</button>"; 
        } else {
        	$html = "<span style=color:red>Belum ada persetujuan GDOKFIN / Pengajuan Ganti Dokumen Finance</span>";
        }

        $tab .= "       <tr>
                            <td colspan=2></td>
                            <td>".$html."</td>
                        </tr>
                    </table>
                </fieldset>";

        echo $tab;
    break;

    case 'posting':    
        try {
            $owlPDO->beginTransaction();
            
            for($i=1; $i<=$param['maxaproval']; $i++){
                if($param['persetujuan'][$i]=='') {
                    exit("Warning: Persetujuan ".$i." belum dipilih.");
                }
            }

            #= delete 1st untuk aprovalnya
            $str = "DELETE FROM ".$dbname.".approval WHERE notransaksi = '".$param['notransaksi']."' AND jenispersetujuan = 'GDOKFIN'";
            $owlPDO->exec($str);
            
            $str = "UPDATE ".$dbname.".keu_gantidokumen set posting = '9'
                    WHERE notransaksi = '".$param['notransaksi']."'";
            $owlPDO->exec($str);

            for($i=1;$i<=$param['maxaproval'];$i++){
                #= insert
                $str = "INSERT INTO ".$dbname.".approval 
                       (notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
                       VALUES
                       ('".$param['notransaksi']."','GDOKFIN','".$i."','".$param['persetujuan'][$i]."','0','','','0000-00-00 00:00:00')";   
                $owlPDO->exec($str);
            }
            
            $owlPDO->commit();
            
        } catch(PDOException $e) {
        
        $owlPDO->rollback();
            echo "Warning: Gagal melakukan pengajuan \n" . addslashes($e->getMessage());

        }
    break;

	case 'viewdetail':
		$status = array("0"=>"Menunggu Persetujuan","1"=>"Disetujui","3"=>"Ditolak");
		$nmKar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
		$str = "SELECT * FROM ".$dbname.".approval WHERE notransaksi='".$param['notransaksi']."' ORDER BY level ASC";
		$res = fetchdata($str);

		$tab = "<table border=0 cellpadding=5 cellspacing=1 class=sortable style=width:100%>
				<thead>
				<tr class=rowheader>";
		foreach($res as $val){
			$tab .= "<td align=center>Persetujuan ".$val['level']."</td>";
		}
		$tab .= "</tr>
				</thead>
				<tbody>
				<tr style='background-color:#D4ECFF'>";
		foreach($res as $val){
			$tab .= "<td align=center>".$nmKar[$val['karyawanid']]."<br>".$status[$val['status']]."<br>Comment : ".$val['komentar']."</td>";
		}
		$tab .= "</tr>
				</tbody>
				</table><br><br>";
		$tab .="<table class='sortable' cellspacing='1' border='0' width=100%>
					<thead>
					<tr class=rowheader>
						<td align='center'>".$_SESSION['lang']['nourut']."</td>
						<td align='center'>File Type</td>
						<td align='center'>Kriteria</td>
						<td align='center'>Filename</td>
						<td align='center'>Action</td>
					</tr></thead>";
		
	
		$str="select * from ".$dbname.".listfileupload where notransaksi='".$param['notransaksi']."' ";
		// echo $str;
		$res=fetchdata($str);
		foreach($res as $bar){
			@$no++;
			@$icon = seticonfile($bar['formaticon']);
			$tab.= "<tr class=rowcontent >";
				$tab.="<td style='text-align:center'>".$no."</td>";
				$tab.="<td align='center'><img src=".$icon." class=zImgBtn></a></td>";
				$tab.= "<td>".getcriterianame($bar['kriteriaefil'])."</td>";
				$tab.= "<td><a href='".$path.str_replace('/','',$bar['namafile'])."' download>".$bar['namafile']."</td>";
				$tab.= "<td><a href='".$path.str_replace('/','',$bar['namafile'])."' download><img src=images/uploader/dwnld8.png class=zImgBtn  title='download'></a></td>";
			$tab.= "<tr>";
		}
		$tab.="</table>";

        echo $tab;
    break;
}

function generatenotransaksi($tanggal){
	
    global $dbname;
    global $owlPDO;
	
	$tgl = str_replace('-','',$tanggal);
	
	$str = "SELECT notransaksi as nomor FROM ".$dbname.".keu_gantidokumen 
			WHERE tanggal='".$tanggal."'";
	$res = $owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$bar = $res->fetch();

	$notran = str_replace($tgl.'/REPLACEDOC/', '', $bar['nomor']);
	if($notran == ''){
		$nourut = 1;
	} else {
		$nourut = str_replace('0','',$notran) + 1;
	}
		
	$notrans = $tgl."/REPLACEDOC/".addZero($nourut,4);
	return $notrans;
}
?>
