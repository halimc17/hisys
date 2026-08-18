<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$param = $_POST;
$method = checkPostGet('method','');
$unit = checkPostGet('unit','');
$afdeling = checkPostGet('afdeling','');
$blok = checkPostGet('blok','');
$hamparan = checkPostGet('hamparan','');
$kavling = checkPostGet('kavling','');
$tahuntanam = checkPostGet('tahuntanam','');
$nama = checkPostGet('nama','');
$status = checkPostGet('status','');
$id = checkPostGet('id','');
$tanggalpengajuan  = tanggalsystemn(checkPostGet('tanggalpengajuan', ''));
$notrans = checkPostGet('notrans','');

$find_nama = checkPostGet('find_nama','');
$find_unit = checkPostGet('find_unit','');
$find_tt = checkPostGet('find_tt','');

$path   = "fileupload/kavling/";

// $noinvoice = checkPostGet('noinvoice', '');
$kriteriaefil = checkPostGet('kriteriaefil', '');
$emodul = "KAV";

// $nmorg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
// $nmvhc=makeOption($dbname, 'vhc_5jenisvhc', 'jenisvhc,namajenisvhc');
// $nmvhc['GLOBAL']='GLOBAL';
switch ($method) {
    case'getblok':
    $optafd2="<option value=''></option>";
    $sorg="select distinct kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='BLOK' and induk = '".$afdeling."'";
	$qorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
	$qorg->setFetchMode(PDO::FETCH_ASSOC);
    while($rorg=$qorg->fetch()){
		$select='';
		if($blok==$rorg['kodeorganisasi']){
			$select="selected";
        }
            $optafd2.="<option value='".$rorg['kodeorganisasi']."' ".$select.">".$rorg['namaorganisasi']."</option>";
	}
	echo $optafd2;
    break;

    case'getafdeling':
    $optafd2="<option value=''></option>";
	// $sql = "SELECT a.*, b.namasupplier FROM ".$dbname.".kebun_5namakud a LEFT JOIN ".$dbname.".log_5supplier b ON a.kodesupplier = b.supplierid where a.kodeunit = '".$unit."' order by a.afdeling asc";
	$sql = "SELECT a.*, b.namasupplier FROM ".$dbname.".kebun_5namakud a LEFT JOIN ".$dbname.".log_5supplier b ON a.kodesupplier = b.supplierid where a.afdeling like '".$unit."%' order by a.afdeling asc";
	$qry = $owlPDO->query($sql) or die(print " Gagal: " . PDOException::getMessage());
	$qry->setFetchMode(PDO::FETCH_ASSOC);
	while ($bar = $qry->fetch()) {
		$select='';
		if($afdeling==$bar['afdeling']){
			$select="selected";
        }
	    $optafd2.="<option value=" . $bar['afdeling'] . " ".$select.">" . $bar['afdeling'] . " - " . $bar['namasupplier'] . "</option>";
	}
	echo $optafd2;
    break;

	case 'insert':
		try {
		$owlPDO->beginTransaction();
			if(($unit=='')or($afdeling=='')or($hamparan=='')or($kavling=='')or($nama=='')){
				throw new PDOException("Gagal 0: Silakan mengisi form");
			}
		
			$str = "select * from ".$dbname.".kebun_5kavling where kodeunit='".$unit."' and afdeling='".$afdeling."' and kodeblok='".$blok."' and no_hamp='".$hamparan."'and no_kavl='".$kavling."'";
			$res = fetchdata($str);
			if(count($res)>0){
				throw new PDOException("Gagal 1: ".$kavling." sudah terdaftar di: ".$unit."/".$afdeling."/".$blok."/".$hamparan);
			}
			
			$data = array();
			$data = array(
				'kodeunit' => $unit,
				'afdeling' => $afdeling,
				'kodeblok' => $blok,
				'no_hamp' => $hamparan,
				'no_kavl' => $kavling,
				't_tnm' => $tahuntanam,
				'nama' => $nama,
				'aktif' => $status,
				'lastuser' => $_SESSION['standard']['userid']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
					$cols[] = $key;
			}
			$str = insertQuery($dbname,'kebun_5kavling',$data,$cols);
			$owlPDO->exec($str);

			$str = "select id from ".$dbname.".kebun_5kavling where kodeunit='".$unit."' and afdeling='".$afdeling."' and kodeblok='".$blok."' and no_hamp='".$hamparan."' and no_kavl='".$kavling."' ";
			$res = fetchdata($str);
			$idbaru=$res[0]['id'];

	        // insert
			$data = array();
			$data = array(
				'id' => $idbaru,
				'kodeunit' => $unit,
				'afdeling' => $afdeling,
				'kodeblok' => $blok,
				'no_hamp' => $hamparan,
				'no_kavl' => $kavling,
				't_tnm' => $tahuntanam,
				'nama' => $nama,
				'aktif' => '1',
				'status' => '0',
				'lastuser' => $_SESSION['standard']['userid']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
					$cols[] = $key;
			}
			$str = insertQuery($dbname,'kebun_5kavling_update',$data,$cols);
			$owlPDO->exec($str);

			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Warning, " . addslashes($e->getMessage());
			die();
		}
	break;
    case'loaddata':
		$tab="<table border=0 cellpadding=1 class=sortable cellspacing=1>
				<thead>
					<tr class=rowheader style=font-weight:bold>
						<td align=center rowspan=2>No</td> 
						<td align=center rowspan=2>".$_SESSION['lang']['unit']."</td> 
						<td align=center rowspan=2>KUD</td> 
						<td align=center rowspan=2>".$_SESSION['lang']['kodeblok']."</td> 
						<td align=center rowspan=2>Hamparan</td> 
						<td align=center rowspan=2>Kavling</td> 
						<td align=center rowspan=2>".$_SESSION['lang']['tahuntanam']."</td> 
						<td align=center rowspan=2>".$_SESSION['lang']['nama']."</td> 
						<td align=center rowspan=2>".$_SESSION['lang']['status']."</td> 
						<td align=center rowspan=2>".$_SESSION['lang']['updateby']."</td> 
						<td align=center rowspan=2 width=400px>".$_SESSION['lang']['action']."</td> 
					</tr>
				</thead>
			<tbody>";
		$limit = 20;
        $page = 0;
        if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		
		$where="";
		if($find_nama!=''){ 
			$where.=" and nama LIKE  '%".$find_nama."%'";
		}
		if($find_unit!=''){ 
			$where.=" and kodeunit LIKE  '%".$find_unit."%'";
		}
		
	
		$ql2 = "select count(*) as jmlhrow from " . $dbname . ".kebun_5kavling where 0=0 ".$where.""; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal 1: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
            $jlhbrs = $jsl->jmlhrow;
        }

        //kamusnamakud
		$ql2 = "select a.afdeling,a.kodesupplier,b.namasupplier from ".$dbname.".kebun_5namakud a left join ".$dbname.".log_5supplier b on a.kodesupplier = b.supplierid where 1 "; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal 2: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
			$kamusnama[$jsl->afdeling]=$jsl->namasupplier;
        }

        // cek apakah ada pengajuan perubahan
		$ql2 = "select * from ".$dbname.".kebun_5kavling_update where status in  ('0','2','3','9') "; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal 2: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
			$statuskav[$jsl->id]=$jsl->status;
			$upkav[$jsl->id]['tanggal']=$jsl->tanggal;
			$upkav[$jsl->id]['notransaksi']=$jsl->notransaksi;
			$upkav[$jsl->id]['kodeunit']=$jsl->kodeunit;
			$upkav[$jsl->id]['afdeling']=$jsl->afdeling;
			$upkav[$jsl->id]['kodeblok']=$jsl->kodeblok;
			$upkav[$jsl->id]['no_hamp']=$jsl->no_hamp;
			$upkav[$jsl->id]['no_kavl']=$jsl->no_kavl;
			$upkav[$jsl->id]['t_tnm']=$jsl->t_tnm;
			$upkav[$jsl->id]['nama']=$jsl->nama;
			$upkav[$jsl->id]['aktif']=$jsl->aktif;
        }
        // cek status approval
		$ql2 = "select * from ".$dbname.".approval where status in ('0','1','2','3','9') and jenispersetujuan = 'SKAV' "; 
        $query2=$owlPDO->query($ql2) or die(print " Gagal 2: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while ($jsl = $query2->fetch()) {
			$statapproval[$jsl->notransaksi][$jsl->level]['karyawanid']=$jsl->karyawanid;
			$statapproval[$jsl->notransaksi][$jsl->level]['status']=$jsl->status;
			$statapproval[$jsl->notransaksi][$jsl->level]['komentar']=$jsl->komentar;
			$statapproval[$jsl->notransaksi][$jsl->level]['tanggal']=$jsl->tanggal;
        }

		$nmKar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

		$str="SELECT * from ".$dbname.".listfileupload where kriteriaefil = 'SKAV'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$kamusefil[$bar['notransaksi']]['namafile']=$bar['namafile'];
			$kamusefil[$bar['notransaksi']]['icon']=seticonfile($bar['formaticon']);
		}
		$pathx   = "fileupload/kavling/";

		$arrsts=array();
				$arrsts=array('1'=>'<font color=green>Aktif</font>','0'=>'<font color=red>Non Aktif</font>');
		$no = 0;
		$str = "select * from ".$dbname.".kebun_5kavling where 0=0 ".$where." order by kodeblok, no_hamp, no_kavl, nama, id desc LIMIT ".$offset.",".$limit."";
		$res = $owlPDO->query($str) or die(print " Gagal 3: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
        while ($bar = $res->fetch()){
			$no++;
			$tab.="<tr style=vertical-align:top class=rowcontent id=tr_$no>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td>".$bar['kodeunit']."</td>";
            $tab.="<td>".$bar['afdeling']." - ".$kamusnama[$bar['afdeling']]."</td>";
            $tab.="<td>".$bar['kodeblok']."</td>";
            $tab.="<td>".$bar['no_hamp']."</td>";
            $tab.="<td>".$bar['no_kavl']."</td>";
            $tab.="<td>".$bar['t_tnm']."</td>";
            $tab.="<td>".$bar['nama']."</td>";
            $tab.="<td align=center>".@$arrsts[$bar['aktif']]."</td>";
            $tab.="<td>".@getNamaKaryawan($bar['lastuser'])."</td>";
            
            $tab.="<td>
				<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"edit('".$bar['id']."','".$bar['kodeunit']."','".$bar['afdeling']."','".$bar['kodeblok']."','".$bar['no_hamp']."','".$bar['no_kavl']."','".$bar['t_tnm']."','".$bar['nama']."','".$bar['aktif']."');\" >&nbsp;	<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('" . $bar['id'] . "');\" >&nbsp;	";
			if($statuskav[$bar['id']]=='0'){
				$tab.="<img src=images/hot.png class=resicon  title='Ajukan' onclick=\"formajukan('".$bar['kodeunit']."','".$bar['id']."');\" > Klik tombol merah untuk mengajukan perubahan data Kavling.";
			}	
			if($statuskav[$bar['id']]=='3'){
				$karz1=$nmKar[$statapproval[$upkav[$bar['id']]['notransaksi']]['1']['karyawanid']];
				$karz2=$nmKar[$statapproval[$upkav[$bar['id']]['notransaksi']]['2']['karyawanid']];
				$statz1=$statapproval[$upkav[$bar['id']]['notransaksi']]['1']['komentar'];
				$statz2=$statapproval[$upkav[$bar['id']]['notransaksi']]['2']['komentar'];

				$tab.="Pengajuan ditolak/revisi. Silakan edit, kemudian ajukan ulang. [".$karz1.": ".$statz1."] [".$karz2.": ".$statz2."]";
			}	
			if($statuskav[$bar['id']]=='9'){
				$karz1=$nmKar[$statapproval[$upkav[$bar['id']]['notransaksi']]['1']['karyawanid']];
				$karz2=$nmKar[$statapproval[$upkav[$bar['id']]['notransaksi']]['2']['karyawanid']];
				$statz1=$statapproval[$upkav[$bar['id']]['notransaksi']]['1']['status'];
				$statz2=$statapproval[$upkav[$bar['id']]['notransaksi']]['2']['status'];

				// tambah filenya
				$tab.="<a href='".$pathx.$kamusefil[$upkav[$bar['id']]['notransaksi']]['namafile']."' download><img src=".$kamusefil[$upkav[$bar['id']]['notransaksi']]['icon']." class=resicon></a> ";
				// $tab.=$upkav[$bar['id']]['notransaksi'];

				$tab.="Perubahan sedang diajukan: </br>".$upkav[$bar['id']]['kodeblok']."/".$upkav[$bar['id']]['no_hamp']."/".$upkav[$bar['id']]['no_kavl']."/".$upkav[$bar['id']]['t_tnm']."/".$upkav[$bar['id']]['nama']."/".$arrsts[$upkav[$bar['id']]['aktif']]."</br>ke: ".$karz1." [".$statz1."], ".$karz2." [".$statz2."]";
			}	
			$tab.="</td>";
            $tab.="</tr>";
        }
		$totrows=ceil($jlhbrs/$limit);
		if($totrows==0){
			$totrows=1;
		}
		$isiRow='';
		for($er=1;$er<=$totrows;$er++){
		  $sel = ($page==$er-1)? 'selected': '';
		  $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
		}

		$tab.="<tr><td colspan=7 align=center>";
		$tab.="<button class=mybutton onclick=loaddata(".($page-1).");>Prev</button>";
		$tab.="<select id=\"pages\" name=\"pages\" onchange=\"getPage(this.value)\">".$isiRow."</select>";
		$tab.="<button class=mybutton onclick=loaddata(".($page+1).");>Next</button>";
		$tab.="</td></tr>";
	
		echo $tab;
	break;

	case'submitfile':
		// $param=$_POST;
        $tgl = date("YmdHis");
        $his = date("His");
        $nmTemp=str_replace('-','',str_replace('/','',$param['notrans']));
        // echo"<pre>";
        // print_r($_FILES['file']);
        // print_r($param);
        // echo"</pre>";
        // exit('error');

        if($tanggalpengajuan == '--'){
            exit("Warning: Tanggal pengajuan masih kosong");
        }
        
        for($i=1; $i<=$param['maxaproval']; $i++){
            if($param['persetujuan'][$i]=='') {
                exit("Warning: Persetujuan ".$i." belum dipilih.");
            }
        }

		if($param['fileupload']!=''){
			if($_FILES['file']['error']==0){    
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$filename = $kriteriaefil."_".$nmTemp."_".$his."".$filetype;
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.rar')||($filetype=='.gz')||($filetype=='.zip')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					$qwenotrans=$tanggalpengajuan."_".$notrans;

					$str = "insert into ".$dbname.".listfileupload values ('','".$qwenotrans."','".$filename."','".$filetype."','".$kriteriaefil."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
					// exit("error: ".$str);
					try{
						$owlPDO->beginTransaction();
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename,$file_tmpname);

						$qwenotrans=$tanggalpengajuan."_".$notrans;

			            #= delete 1st untuk aprovalnya
			            $str = "DELETE FROM ".$dbname.".approval WHERE notransaksi = '".$qwenotrans."' AND jenispersetujuan = 'SKAV'";
			            $owlPDO->exec($str);
			            
			            $str = "UPDATE ".$dbname.".kebun_5kavling_update set status = '9', tanggal = '".$tanggalpengajuan."', notransaksi = '".$qwenotrans."' WHERE id = '".$notrans."'";
			            $owlPDO->exec($str);

			            for($i=1;$i<=$param['maxaproval'];$i++){
			                #= insert
			                $str = "INSERT INTO ".$dbname.".approval 
			                       (notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
			                       VALUES
			                       ('".$qwenotrans."','SKAV','".$i."','".$param['persetujuan'][$i]."','0','','','0000-00-00 00:00:00')";   
							// throw new PDOException("Gagal 0: ".$str);
			                $owlPDO->exec($str);
			            }
			            $owlPDO->commit();

					}
					catch(PDOException $e){
						$owlPDO->rollback();
			            echo "Warning: Gagal melakukan pengajuan \n" . $e->getMessage();
						// echo " Gagal," . addslashes($e->getMessage());
					}
				}else{
					exit("Warning : Format file upload tidak boleh ".$filetype);
				}
			}else{
				exit("Warning : Ukuran file terlalu besar ".$_FILES['file']['error'].": UPLOAD_ERR_INI_SIZE");
			}
		}else{
			exit("Warning: Silakan lampirkan surat");
		}
    break;

    case 'posting':
    
        try {
            $owlPDO->beginTransaction();
            
            if($tanggalpengajuan == ''){
                exit("Warning: Tanggal pengajuan masih kosong");
            }
            
            for($i=1; $i<=$param['maxaproval']; $i++){
                if($param['persetujuan'][$i]=='') {
                    exit("Warning: Persetujuan ".$i." belum dipilih.");
                }
            }

            $qwenotrans=$tanggalpengajuan."_".$notrans;

            #= delete 1st untuk aprovalnya
            $str = "DELETE FROM ".$dbname.".approval WHERE notransaksi = '".$qwenotrans."' AND jenispersetujuan = 'SKAV'";
            $owlPDO->exec($str);
            
            $str = "UPDATE ".$dbname.".kebun_5kavling_update set status = '9', tanggal = '".$tanggalpengajuan."', notransaksi = '".$qwenotrans."' WHERE id = '".$notrans."'";
            $owlPDO->exec($str);

            for($i=1;$i<=$param['maxaproval'];$i++){
                #= insert
                $str = "INSERT INTO ".$dbname.".approval 
                       (notransaksi, jenispersetujuan, level, karyawanid, status, komentar, keterangan, tanggal)
                       VALUES
                       ('".$qwenotrans."','SKAV','".$i."','".$param['persetujuan'][$i]."','0','','','0000-00-00 00:00:00')";   
                $owlPDO->exec($str);
            }
            
            $owlPDO->commit();
            
        } catch(PDOException $e) {
        
        $owlPDO->rollback();
            echo "Warning: Gagal melakukan pengajuan \n" . addslashes($e->getMessage());

        }
    break;	

    case 'formajukan':
        $countApp = getCountApproval('SKAV',$unit);

        $tab = "<fieldset style=width:95%>
                    <legend>".$_SESSION['lang']['persetujuan']."</legend>
                    <table>
                        <tr>
                            <td>".$_SESSION['lang']['tanggal']."</td>
                            <td>:</td>
                            <td><input type=text class=myinputtext id=tanggalpengajuan readonly onmousemove=setCalendar(this.id) onkeypress=return false;  maxlength=10 style=width:152px;/></td>
                        </tr>";

        for($i=1; $i<=$countApp; $i++){
            $arrList = listApprove($i,'SKAV',$unit);
            $optpersetujuan = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";

            foreach($arrList as $key=>$val){
                $optpersetujuan .= "<option value='".$val['karyawanid']."'>".$val['nama']."</option>";
            }

            $tab .= "<tr>
                        <td>".$_SESSION['lang']['persetujuan']." ".$i."</td> 
                        <td>:</td>
                        <td><select style=\"width:154px;\" id=persetujuan".$i.">".$optpersetujuan."</select></td>
                    </tr>";  
        }   
			$emodul = "KAV";
			// @$arrmodul = getmodulefil($emodul);
			$str="select * from ".$dbname.".fil_5mapcriteria where modul='".$emodul."' and status='1'";
			$res=fetchdata($str); 
			foreach($res as $key=>$val){
				$optemodul[$val['id']]['id'] = $val['id'];
				$optemodul[$val['id']]['kriteria'] = $val['kriteria'];
			}

			foreach($optemodul as $key=>$val){
				@$optkriteria.="<option value='".$key."'>".$val['kriteria']."</option>";
			}

			$tab .="<tr>
					<td>".$_SESSION['lang']['kriteria']."</td>
					<td>:</td>
					<td>
						<select id='kriteriaefil'>". $optkriteria."</select>
					</td>
				</tr>
				<tr>				
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' class=mybutton>
				</td>
			</tr>";


        if ($countApp > 0) {
        	$html = "<button class=mybutton onclick=submitfile('".$id."','".$countApp."')>".$_SESSION['lang']['save']."</button>"; 
        } else {
        	$html = "<span style=color:red>Belum ada persetujuan untuk Unit ".$unit." dan Jenis Persetujuan SKAV</span>";
        }

        $tab .= "       <tr>
                            <td colspan=2></td>
                            <td>".$html."</td>
                        </tr>
                    </table>
                </fieldset>";

        echo $tab;
    break;



	case 'delete':
		$str = "select * from ".$dbname.".kebun_spbpetani where id_kavling='".$id."'";
		$res = fetchdata($str);
		if(count($res)>0){
			exit("Warning : Tidak dapat dihapus karena ".$id." sudah terdaftar pada menu Kebun - Transaksi - Pembayaran TBS - Pembayaran TBS Petani");
		}		

        // cek apakah sudah ada yang diajukan
		$str = "select * from ".$dbname.".kebun_5kavling_update where id='".$id."' and status = '9' ";
		$res = fetchdata($str);
		if(count($res)>0){
			exit("Warning : Tidak dapat dihapus karena ".$id." sedang dalam proses persetujuan. Silakan menunggu persetujuan selesai sebelum menghapus data.");
		}
		
		$str = "delete from ".$dbname.".kebun_5kavling where id = '".$id."'";
        try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
		$str = "delete from ".$dbname.".kebun_5kavling_update where id = '".$id."' and status != '1' ";
        try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}
		
	break;
	case'update':
		$where = "id='".$id."'";
		// $str = "update " . $dbname . ".kebun_5kavling set kodeunit = '".$unit."',afdeling = '".$afdeling."',kodeblok = '".$blok."',no_hamp = '".$hamparan."',no_kavl = '".$kavling."',t_tnm = '".$tahuntanam."',nama = '".$nama."',aktif = '".$status."',lastuser = '".$_SESSION['standard']['userid']."' where ".$where."";
		// update cuman bisa selain unit n afdeling
		$str = "update " . $dbname . ".kebun_5kavling set kodeblok = '".$blok."',no_hamp = '".$hamparan."',no_kavl = '".$kavling."',t_tnm = '".$tahuntanam."',nama = '".$nama."',aktif = '".$status."',lastuser = '".$_SESSION['standard']['userid']."' where ".$where."";
        // try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal," . addslashes($e->getMessage());}

        // cek apakah sudah ada yang diajukan
		$str = "select * from ".$dbname.".kebun_5kavling_update where id='".$id."' and status = '9' ";
		$res = fetchdata($str);
		if(count($res)>0){
			exit("Warning : Tidak dapat diupdate karena ".$id." sedang dalam proses persetujuan. Silakan menunggu persetujuan selesai sebelum mengubah data.");
		}

		// hapus data sebelumnya jika sudah ada yang status masih 0
		$str = "delete from ".$dbname.".kebun_5kavling_update where id = '".$id."' and status = '0' ";
        try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal 1," . addslashes($e->getMessage());}

        // insert
		$data = array();
		$data = array(
			'id' => $id,
			'kodeunit' => $unit,
			'afdeling' => $afdeling,
			'kodeblok' => $blok,
			'no_hamp' => $hamparan,
			'no_kavl' => $kavling,
			't_tnm' => $tahuntanam,
			'nama' => $nama,
			'aktif' => $status,
			'status' => '0',
			'lastuser' => $_SESSION['standard']['userid']
		);
		
		$cols = array();
		foreach($data as $key=>$row) {
				$cols[] = $key;
		}
		$str = insertQuery($dbname,'kebun_5kavling_update',$data,$cols);
        try{$owlPDO->exec($str);}catch(PDOException $e){echo " Gagal 2," . addslashes($e->getMessage());}

	break;


    default:
}
?>
