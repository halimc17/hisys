<?php
// ini_set('display_errors',0);
// error_reporting(0);
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
include_once('lib/HtmlExcel.php');

$method         = checkPostGet('method', '');
$param          = $_POST;
$param['dosis'] = str_replace(",","",$param['dosis']);
$param['jumlah']= str_replace(",","",$param['jumlah']);
$kodeorg        = checkPostGet('kodeorg', '');
$jenis          = checkPostGet('jenis', '');
$tipe           = checkPostGet('tipe', '');
$divisi         = checkPostGet('divisi', '');
$pupuk          = checkPostGet('pupuk', '');
$periode        = checkPostGet('periode', '');
$tahun        = checkPostGet('tahun', '');
$nmorg          = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$jab            = getPostingJabatan('rekppk');

switch ($method) {
	case'getluas':
		$str = "SELECT * FROM " . $dbname . ".setup_blok where kodeorg = '".$param['blok']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			echo $bar['luasareaproduktif']."##".$bar['jumlahpokok']."##".$bar['kodetanah'];
		}
	
	break;
	case'getdata':
		$pupuk=$org=$divisi=$blok=$tt="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		if($param['sumber']=='kodeorg'){
			$str = "select * from " . $dbname . ".organisasi where kodeorganisasi like '".$kodeorg."%'";
			$res = fetchdata($str);
			foreach($res as $bar){
				if($bar['tipe']=='AFDELING'){
					$divisi.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
				}else if($bar['tipe']=='BLOK'){
					$blok.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
				}
			}
			
			$sql = "SELECT distinct tahuntanam FROM " . $dbname . ".setup_blok where kodeorg like '".$kodeorg."%' order by tahuntanam";
			$res = fetchdata($sql);
			foreach($res as $bar){
				$tt.="<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
			}
		}
		if($param['sumber']=='divisi'){
			$str = "select * from " . $dbname . ".organisasi where kodeorganisasi like '".$param['divisi']."%'";
			$res = fetchdata($str);
			foreach($res as $bar){
				if($bar['tipe']=='AFDELING'){
					$divisi.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
				}else if($bar['tipe']=='BLOK'){
					$blok.="<option value=" . $bar['kodeorganisasi'] . ">" . $bar['kodeorganisasi'] . " - " . $bar['namaorganisasi'] . "</option>";
				}
			}
			
			$sql = "SELECT distinct tahuntanam FROM " . $dbname . ".setup_blok where kodeorg like '".$param['divisi']."%' order by tahuntanam";
			$res = fetchdata($sql);
			foreach($res as $bar){
				$tt.="<option value=" . $bar['tahuntanam'] . ">" . $bar['tahuntanam'] . "</option>";
			}
		}
		if($param['sumber']=='tt'){
			$str = "SELECT * FROM " . $dbname . ".setup_blok where kodeorg like '".$param['divisi']."%'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$optnm=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodeorg']."'");
				$blok.="<option value=" . $bar['kodeorg'] . ">" . $bar['kodeorg'] . " - " . $optnm[$bar['kodeorg']] . "</option>";
			}
		}
		
		echo $divisi."##".$tt."##".$blok;
	break;
    case'update':
		$sql = "select * from " . $dbname . ".kebun_rekomendasipupuk where blok='" . $param['blok'] . "' and kodebarang='" . $param['pupukold'] . "' and aplikasi='" . $param['aplold'] . "' and periodepemupukan='".trim($param['periodeold'])."' and posting='1'";
		$jlhbrs = count(fetchdata($sql));
		if ($jlhbrs > 0) {
			exit("Error : Data sudah diposting.");
		}
		$data = array(
			'luas'            => $param['luas'],
			'pokok'           => $param['pokok'],
			'kodebarang'      => $param['pupuk'],
			'dosis'           => $param['dosis'],
			'jumlah'          => $param['jumlah'],
			'aplikasi'        => $param['apl'],
			'satuan'          => "Kg",
			'periodepemupukan'=> $param['periode'],
			'jenistanah'      => $param['jenistanah'],
			'updateby'        => $_SESSION['standard']['userid'],
			'lastupdate'      => date("Y-m-d H:i:s")
		);
		
		$where = "blok='" . $param['blok'] . "' and kodebarang='" . $param['pupukold'] . "' and aplikasi='" . $param['aplold'] . "' and periodepemupukan='" . $param['periodeold'] . "'";

		$str = updateQuery($dbname,'kebun_rekomendasipupuk',$data,$where); #exit("error".$str);
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
	break;
    case'insert':
        try {
		$owlPDO->beginTransaction();
			#cek data
			$sql = "select * from " . $dbname . ".kebun_rekomendasipupuk where blok='" . $param['blok'] . "' and kodebarang='" . $param['pupuk'] . "' and aplikasi='" . $param['apl'] . "' and periodepemupukan='".trim($param['periode'])."' and posting='1'";
			$jlhbrs = count(fetchdata($sql));
			if ($jlhbrs > 0) {
				throw new PDOException("Data sudah diposting.");
			}
			
			$sql = "delete from " . $dbname . ".kebun_rekomendasipupuk where blok='" . $param['blok'] . "' and kodebarang='" . $param['pupuk'] . "' and aplikasi='" . $param['apl'] . "' and periodepemupukan='".trim($param['periode'])."'";
			$owlPDO->exec($sql);
			$optsts = makeOption($dbname,'setup_blok','kodeorg,statusblok',"kodeorg='".$param['blok']."'");
			$data = array(
				'kodeorg'         => $param['kodeorg'],
				'blok'            => $param['blok'],
				'statusblok'      => $optsts[$param['blok']],
				'tahuntanam'      => $param['tt'],
				'luas'            => $param['luas'],
				'pokok'           => $param['pokok'],
				'kodebarang'      => $param['pupuk'],
				'dosis'           => $param['dosis'],
				'jumlah'          => $param['jumlah'],
				'aplikasi'        => $param['apl'],
				'satuan'          => "Kg",
				'periodepemupukan'=> $param['periode'],
				'jenistanah'      => $param['jenistanah'],
				'updateby'        => $_SESSION['standard']['userid'],
				'lastupdate'      => date("Y-m-d H:i:s")

			);
			
			$cols = array();
			foreach($data as $key=>$row) {
					$cols[] = $key;
			}

			$query = insertQuery($dbname,'kebun_rekomendasipupuk',$data,$cols);
			#try {$owlPDO->exec($query);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
			$owlPDO->exec($query);
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Error, " . addslashes($e->getMessage());
			die();
		}
	break;
    
    case'deletedetail':
		$sql = "select * from " . $dbname . ".kebun_rekomendasipupuk where blok='" . $param['blok'] . "' and kodebarang='" . $param['pupuk'] . "' and aplikasi='" . $param['apl'] . "' and periodepemupukan='".trim($param['periode'])."' and posting='1'";
		$jlhbrs = count(fetchdata($sql));
		if ($jlhbrs > 0) {
			exit("Error : Data sudah diposting.");
		}
			
        $str = "delete from " . $dbname . ".kebun_rekomendasipupuk where blok='" . $param['blok'] . "' and kodebarang='" . $param['pupuk'] . "' and aplikasi='" . $param['apl'] . "' and periodepemupukan='".trim($param['periode'])."'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
	break;
    case'delete':
		$sql = "select * from " . $dbname . ".kebun_rekomendasipupuk where kodeorg='" . $param['kodeorg'] . "' and kodebarang='" . $param['pupuk'] . "' and blok like '" . $param['divisi'] . "%' and periodepemupukan like '" . $param['tahun'] . "%'  and posting='1'";
		$jlhbrs = count(fetchdata($sql));
		if ($jlhbrs > 0) {
			exit("Error : Ada data sudah diposting.");
		}
			
        $str = "delete from " . $dbname . ".kebun_rekomendasipupuk where kodeorg='" . $param['kodeorg'] . "' and kodebarang='" . $param['pupuk'] . "' and blok like '" . $param['divisi'] . "%' and periodepemupukan like '" . $param['tahun'] . "%'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
	break;
	case'posting':
        $str = "update " . $dbname . ".kebun_rekomendasipupuk set posting='1', postingby='".$_SESSION['standard']['userid']."', postingdate='".date("Y-m-d H:i:s")."' where kodeorg='" . $param['kodeorg'] . "' and kodebarang='" . $param['pupuk'] . "' and blok like '" . $param['divisi'] . "%' and periodepemupukan like '" . $param['tahun'] . "%'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;	
	case'unposting':
        $str = "update " . $dbname . ".kebun_rekomendasipupuk set posting='0', postingby='".$_SESSION['standard']['userid']."', postingdate='".date("Y-m-d H:i:s")."' where kodeorg='" . $param['kodeorg'] . "' and kodebarang='" . $param['pupuk'] . "' and blok like '" . $param['divisi'] . "%' and periodepemupukan like '" . $param['tahun'] . "%'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;	
	case'loaddatadetail':
		$where = "";
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$where = "";
		} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where = " and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."')";
		} else {
			$where = " and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		}
		
		if($param['kodeorg']!=''){
			$where.=" and kodeorg like '%".$param['kodeorg']."%'";
		}
		if($param['divisi']!=''){
			$where.=" and blok like '%".$param['divisi']."%'";
		}
		if($param['tt']!=''){
			$where.=" and periodepemupukan like '%".$param['tt']."%'";
		}
		if($param['pupuk']!=''){
			$where.=" and kodebarang like '%".$param['pupuk']."%'";
		}
        
		$limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = intval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
        $sql = "select * from " . $dbname . ".kebun_rekomendasipupuk where 1=1 " . $where . "";
        $jlhbrs = count(fetchdata($sql));
        
        $tab = "";
		$no = 0;
        $no = $maxdisplay;
		
		$arrapl=array('1'=>'Satu','2'=>'Dua','3'=>'Tiga','4'=>'Empat','5'=>'Lima','6'=>'Enam','7'=>'Tujuh','8'=>'Delapan','9'=>'Sembilan','10'=>'Sepuluh','11'=>'Sebelas','12'=>'Dua Belas','1e'=>'Extra Satu','2e'=>'Extra Dua','3e'=>'Extra Tiga');


        $str = "SELECT * FROM " . $dbname . ".kebun_rekomendasipupuk where 1=1 " . $where . "  order by lastupdate desc limit " . $offset . "," . $limit . "";
        $res = fetchdata($str);
        foreach($res as $bar){
            $isi = '';
            $no+=1;
			$optppk=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
			
            $tab.="<tr class=rowcontent  id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td>" . $bar['kodeorg'] . "</td>";
            $tab.="<td>" . substr($bar['blok'],0,6) . " - " . $nmorg[substr($bar['blok'],0,6)] . "</td>";
            $tab.="<td align=center width=50px>".$bar['tahuntanam']."</td>";
            $tab.="<td>".$nmorg[$bar['blok']]."</td>";
            $tab.="<td>".$bar['kodebarang']." -".$optppk[$bar['kodebarang']]."</td>";
            $tab.="<td align=right>".number_format($bar['luas'],2)."</td>";
            $tab.="<td align=right>".number_format($bar['pokok'],0)."</td>";
            $tab.="<td align=left>".$arrapl[$bar['aplikasi']]."</td>";
            $tab.="<td align=right>".$bar['jenistanah']."</td>";
            $tab.="<td align=right>".number_format($bar['dosis'],2)."</td>";
            $tab.="<td align=right>".number_format($bar['jumlah'],2)."</td>";
            $tab.="<td align=right>".$bar['periodepemupukan']."</td>";
            $tab.="<td>" . getNamaKaryawan($bar['updateby']) . "</td>";
            
			$prd=explode("-",$bar['periodepemupukan']);
			$thn=$prd[0];
			$bln=$prd[1];
			if($bar['posting']=='0'){				
				$isi.="<td align=center width=30px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
					onclick=\"editdetail('".$bar['kodeorg']."','".substr($bar['blok'],0,6)."','".$bar['blok']."','".$bar['tahuntanam']."','".$bar['luas']."','".$bar['pokok']."','".$bar['aplikasi']."','".trim($bar['jenistanah'])."','".$bar['dosis']."','".$bar['jumlah']."','".$bar['kodebarang']."','".$bln."','".$thn."');\" ></td>";
					
				$isi.="<td align=center width=30px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
					onclick=\"deldetail('".$bar['blok']."','".$bar['periodepemupukan']."','".$bar['kodebarang']."','".$bar['aplikasi']."');\" ></td>";
			}else{
				$isi.="<td colspan=2 width=60px>Posted</td>";
			}
		  
            $tab.=$isi;
        }
        $tab.="</tr>";
        $tab.="</table>";
		$totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = ($page == $er - 1) ? 'selected' : '';
            $isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
        }
        $footd = "";
        $footd.="</tr><tr><td colspan=16 align=center>";
        if ($page == '0') {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['pref'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddatadetail(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
        }
        $footd.="<select id=\"pagesdet\" name=\"pages\" style=\"width:50px\" onchange=\"getPageDetail()\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['lanjut'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddatadetail(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
        }
        $footd.="</td>
            </tr>";
        echo $tab . "####" . $footd;
		
	break;
    case'loaddata':
        $where = "";
		if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			$where = "";
		} else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			$where = " and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk='".$_SESSION['empl']['kodeorganisasi']."')";
		} else {
			$where = " and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		}
		
		if($param['kodeorg']!=''){
			$where.=" and kodeorg like '%".$param['kodeorg']."%'";
		}
		if($param['divisi']!=''){
			$where.=" and blok like '%".$param['divisi']."%'";
		}
		if($param['tt']!=''){
			$where.=" and periodepemupukan like '%".$param['tt']."%'";
		}
		if($param['pupuk']!=''){
			$where.=" and kodebarang like '%".$param['pupuk']."%'";
		}
		
        $limit = 20;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {
            $page = floatval($_POST['page']);
            if ($page < 0)
                $page = 0;
        }
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
        $sql = "select * from " . $dbname . ".kebun_rekomendasipupuk where 1=1 " . $where . " group by substr(periodepemupukan,1,4),substr(blok,1,6),kodebarang";
        $jlhbrs = count(fetchdata($sql));
        
        $tab = "";
		$no = 0;
        $no = $maxdisplay;
		
        $str = "SELECT kodeorg,substr(periodepemupukan,1,4) as tahun,substr(blok,1,6) as divisi,kodebarang,sum(jumlah) as jumlah,updateby,posting FROM " . $dbname . ".kebun_rekomendasipupuk where 1=1 " . $where . " group by tahun,divisi,kodebarang order by tahun desc, kodeorg asc limit " . $offset . "," . $limit . "";
        $res = fetchdata($str);
		if($jlhbrs>0){			
			foreach($res as $bar){
				$optppk=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$bar['kodebarang']."'");
				$isi = '';
				$no+=1;
				$tab.="<tr class=rowcontent style=height:25px id=tr_$no>";
				$tab.="<td align=center>" . $no . "</td>";
				$tab.="<td align=center>" . $bar['tahun'] . "</td>";
				$tab.="<td>" . $bar['kodeorg'] . " - " . $nmorg[$bar['kodeorg']] . "</td>";
				$tab.="<td>" . $bar['divisi'] . " - " . $nmorg[$bar['divisi']] . "</td>";
				$tab.="<td>" . $bar['kodebarang'] . " - " . $optppk[$bar['kodebarang']] . "</td>";
				$tab.="<td align=right>".numb_format($bar['jumlah'],2)."</td>";
				$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
				$tab.="<td>" . @$nmkar[$bar['updateby']] . "</td>";
				
				if ($bar['posting'] == 0) {				
					$isi.="<td align=center width=30px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' 
						onclick=\"edit('".$bar['kodeorg']."','".$bar['divisi']."','".$bar['kodebarang']."','".$bar['tahun']."');\" ></td>";
						
					$isi.="<td align=center width=30px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' 
						onclick=\"del('".$bar['kodeorg']."','".$bar['divisi']."','".$bar['kodebarang']."','".$bar['tahun']."');\" ></td>";
						
					$isi.="<td align=center width=30px><img src=images/icons/04/16/01.png class=resicon class=zImgBtn height='30'  title='Posting' 
						onclick=\"posting('".$bar['kodeorg']."','".$bar['divisi']."','".$bar['kodebarang']."','".$bar['tahun']."');\" ></td>";	
				}else {
					if(in_array($_SESSION['empl']['jabatan'],$jab)){
						$icon="images/icons/04/16/04.png";
						$title="Unposting";
						$unpost=" onclick=\"unposting('".$bar['kodeorg']."','".$bar['divisi']."','".$bar['kodebarang']."','".$bar['tahun']."');\" ";
					}else {
						$icon="images/icons/04/16/02.png";
						$title="Posted";
						$unpost='';
					}
					$isi.="<td width=30px></td><td width=30px></td>";
					$isi.="<td align=center width=30px><img src=".$icon." class=resicon class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";
				}	
					
				$isi.="<td align=center width=30px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='HTML' onclick=\"detailData('".$bar['divisi']."','".$bar['kodebarang']."','".$bar['tahun']."','event','html');\" ></td>";
				$isi.="<td align=center width=30px><img src=images/excel.jpg class=zImgBtn class=zImgBtn height='30'  title='Excel' onclick=\"detailExcel('".$bar['divisi']."','".$bar['kodebarang']."','".$bar['tahun']."','event','excel');\" ></td>";	
					
				$tab.=$isi;
				$tab.="</tr>";
			}
		}
		
        $totrows = ceil($jlhbrs / $limit);
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = ($page == $er - 1) ? 'selected' : '';
            $isiRow.="<option value='" . $er . "' " . $sel . ">" . $er . "</option>";
        }
        $footd = "";
        $footd.="</tr>
                     <tr><td colspan=12 align=center>";
        if ($page == '0') {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['pref'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
        }
        $footd.="<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
        if (($page + 1) == $totrows) {
            $footd.="<button class=mybutton disabled=true>" . $_SESSION['lang']['lanjut'] . "</button>";
        } else {
            $footd.="<button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
        }
        $footd.="</td>
            </tr>";
        echo $tab . "####" . $footd;
	break;
	case'showupload':
		$tab="";
		$tab.="<fieldset style=float:left><legend>Tempalte</legend>";
		$tab.="<table border=0>
				<tr>
					<td>Download : 
					<button class=mybutton><a href='tool_slave_getExample.php?form=REKPUPUK' target='frame'>Templ_Upload</a></button>&nbsp;
					<button class=mybutton><a href='tool_slave_getExample.php?form=MASTERREKPPK' target='frame'>Master (Blok, Barang)</a></button>&nbsp;
					</td>
				</tr><tr>
					<td colspan=3><hr></td>
				</tr><tr>
					<td colspan=3>
						<form id=frm name=frm enctype=multipart/form-data method=post action=kebun_slave_5dosispupuk_upload.php target=frame>
							<input type=hidden name=jenisdata id=jenisdata value='REKPUPUK'>
							<input type=hidden name=MAX_FILE_SIZE value=1024000>
							File : <input name=filex type=file id=filex class=mybutton>
							Field separated by : 
							<select name=pemisah>
								<option value=','>, (comma)</option>
							</select>
							<input type=button class=mybutton id=previewupload value=".$_SESSION['lang']['preview']." title='Submit this File' onclick=submitFile()>
							<input type=button class=mybutton  value=".$_SESSION['lang']['back']." title='Back' onclick=add_new_data()>
						</form>
					</td>
				</tr></table>";
		$tab.="</fieldset>";
		$tab.="<iframe frameborder=0 style=width:100%;height:450px; name=frame></iframe>";
		
		
		echo $tab;
	break;
	
	case'preview':
		$theme=$_SESSION['theme'];
		if($theme=='skyblue' || $theme==''){
		  $men='menu.css';
		  $gen='generic.css';
		}else if($theme=='red'){
		  $men='menuRed.css';
		  $gen='genericRed.css';  
		}else{
		  $men='menuGray.css';
		  $gen='genericGray.css';  
		}               
		if($tipe=='excel'){
			$border="border=1";
		}else{
			$tab="<link rel=stylesheet type=text/css href=style/".$gen.">";
			$border="border=0";
		}
		
		
		$rbulan = month_inbetween($tahun."-01",$tahun."-12");
		$tab.="<fieldset><legend>Preview</legend>
				<table cellpading=1 cellspacing=1 ".$border." class=sortable>
					<thead>
						<tr class=rowheader>
							<td align=center rowspan=2>" . $_SESSION['lang']['nourut'] . "</td>
							<td align=center rowspan=2>" . $_SESSION['lang']['divisi'] . "</td>
							<td align=center rowspan=2>" . $_SESSION['lang']['blok'] . "</td>
							<td align=center rowspan=2 width=50px>" . $_SESSION['lang']['tahuntanam'] . "</td>
							<td align=center rowspan=2>" . $_SESSION['lang']['luas'] . "</td>
							<td align=center rowspan=2>" . $_SESSION['lang']['pokok'] . "</td>
							<td align=center rowspan=2>SPH</td>
							<td align=center rowspan=2>" . $_SESSION['lang']['pupuk'] . "</td>
							<td align=center rowspan=2>Apl</td>
							";
						foreach($rbulan as $bln){							
							$tab.="<td align=center colspan=2>".numToMonth(substr($bln,5,2),'E','short')."</td>";
						}
						$tab.="<td align=center colspan=2>" . $_SESSION['lang']['total'] . "</td>";
						$tab.="</tr>";
						$tab.="<tr class=rowheader>";
						foreach($rbulan as $bln){							
							$tab.="<td align=center>" . $_SESSION['lang']['dosis']."</td>";
							$tab.="<td align=center>" . $_SESSION['lang']['jumlah']."</td>";
						}
						$tab.="<td align=center>" . $_SESSION['lang']['dosis']."</td>";
						$tab.="<td align=center>" . $_SESSION['lang']['jumlah']."</td>";						
					$tab.="</tr>
					</thead>
					 <tbody>";
					$str = "select * from " . $dbname . ".kebun_rekomendasipupuk where blok like '".$divisi."%' and periodepemupukan like '".$tahun."%' and kodebarang='".$pupuk."'";
					$res = fetchdata($str);
					$data=$jlh=$dss=array();
					foreach($res as $bar){
						$aplikasi[$bar['aplikasi']]=$bar['aplikasi'];
						$jnsppk[$bar['kodebarang']]=$bar['kodebarang'];
						$data[$bar['blok']]=$bar['blok'];
						$tt[$bar['blok']]=$bar['tahuntanam'];
						$luas[$bar['blok']]=$bar['luas'];
						$pokok[$bar['blok']]=$bar['pokok'];
						$jlh[$bar['blok']][$bar['kodebarang']][$bar['aplikasi']][$bar['periodepemupukan']]+=$bar['jumlah'];
						$dss[$bar['blok']][$bar['kodebarang']][$bar['aplikasi']][$bar['periodepemupukan']]+=$bar['dosis'];
					}
					
					$no=0;
					$row="rowspan=".((count($jnsppk)*count($aplikasi)))."";
					foreach($data as $kdblok){
						$no+=1;
						$tab.="<tr class=rowcontent style=vertical-align:top;>";
						$tab.="<td align=center ".$row.">".$no."</td>";
						$tab.="<td align=center ".$row.">".substr($kdblok,0,6)."</td>";
						$tab.="<td align=center ".$row.">".$nmorg[$kdblok]."</td>";
						$tab.="<td align=center ".$row.">".$tt[$kdblok]."</td>";
						$tab.="<td align=center ".$row.">".numb_format($luas[$kdblok],2)."</td>";
						$tab.="<td align=center ".$row.">".numb_format($pokok[$kdblok])."</td>";
						$tab.="<td align=center ".$row.">".numb_format($pokok[$kdblok]/$luas[$kdblok],2)."</td>";
						$tluas+=$luas[$kdblok];$tpokok+=$pokok[$kdblok];
						$nbrg=0;
						foreach($jnsppk as $kdbrg){
							$nbrg+=1;
							if($nbrg>1){
								$tab.="</tr>";
								$tab.="<tr class=rowcontent>";
							}
							$optppk=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kdbrg."'");
							$tab.="<td rowspan=".count($aplikasi).">".$kdbrg." - ".$optppk[$kdbrg]."</td>";
							$napl=0;
							foreach($aplikasi as $apl){
								$napl+=1;
								if($napl>1){
									$tab.="</tr>";
									$tab.="<tr class=rowcontent>";								
								}	
								$tab.="<td align=center style=font-style:italic;>".$apl."</td>";
								foreach($rbulan as $bln){
									$tab.="<td align=right>".numb_format($dss[$kdblok][$kdbrg][$apl][$bln],2)."</td>";
									$tab.="<td align=right>".numb_format($jlh[$kdblok][$kdbrg][$apl][$bln],2)."</td>";
									$tdss[$kdblok][$kdbrg][$apl]+=$dss[$kdblok][$kdbrg][$apl][$bln];
									$tjlh[$kdblok][$kdbrg][$apl]+=$jlh[$kdblok][$kdbrg][$apl][$bln];
									
									$gtjlh[$kdbrg][$bln]+=$jlh[$kdblok][$kdbrg][$apl][$bln];
									$gtdss[$kdbrg][$bln]+=$dss[$kdblok][$kdbrg][$apl][$bln];
								}
								$tab.="<td align=right>".numb_format($tdss[$kdblok][$kdbrg][$apl],2)."</td>";
								$tab.="<td align=right>".numb_format($tjlh[$kdblok][$kdbrg][$apl],2)."</td>";
								
							}
						}						
					}	
					$tab.="</tr>";
					$tab.="<tr class=rowcontent>";
					$tab.="</tr>";
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center colspan=4 rowspan=".count($jnsppk).">S U B  T O T A L</td>";
					$tab.="<td align=right rowspan=".count($jnsppk).">".numb_format($tluas,2)."</td>";
					$tab.="<td align=right rowspan=".count($jnsppk).">".numb_format($tpokok)."</td>";
					$tab.="<td align=right rowspan=".count($jnsppk).">".numb_format($tpokok/$tluas,2)."</td>";
					$nbrg=0;
					foreach($jnsppk as $kdbrg){
						$nbrg+=1;
						if($nbrg>1){
							$tab.="</tr>";
							$tab.="<tr class=rowcontent>";
						}
						$optppk=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$kdbrg."'");
						$tab.="<td>".$kdbrg." - ".$optppk[$kdbrg]."</td>";
						$tab.="<td></td>";
						foreach($rbulan as $bln){							
							$tab.="<td align=right>".numb_format($gtjlh[$kdbrg][$bln]/$tpokok,2)."</td>";
							$tab.="<td align=right>".numb_format($gtjlh[$kdbrg][$bln],2)."</td>";
							$gttjlh[$kdbrg]+=$gtjlh[$kdbrg][$bln];
							$grandttl[$bln]+=$gtjlh[$kdbrg][$bln];
						}
						$tab.="<td align=right>".numb_format($gttjlh[$kdbrg]/$tpokok,2)."</td>";
						$tab.="<td align=right>".numb_format($gttjlh[$kdbrg],2)."</td>";
					}
					$tab.="</tr>";
					$tab.="<tr class=rowcontent>";
					$tab.="</tr>";
					$tab.="<tr class=rowcontent>";
					$tab.="<td align=center colspan=9>G R A N D  T O T A L</td>";
					foreach($rbulan as $bln){							
						$tab.="<td align=right>".numb_format($grandttl[$bln]/$tpokok,2)."</td>";
						$tab.="<td align=right>".numb_format($grandttl[$bln],2)."</td>";
						$gtt+=$grandttl[$bln];
					}
					$tab.="<td align=right>".numb_format($gtt/$tpokok,2)."</td>";
					$tab.="<td align=right>".numb_format($gtt,2)."</td>";
					$tab.="</tr>";
					
					$tab.="</tbody>
				 </table>
			</fieldset>
		";
		if($tipe=='excel'){
			$nop = "rekomendasi_pupuk.xls";
			$xls = new HtmlExcel();
			$xls->setCss($css);
			$xls->addSheet("pupuk", $tab);
			$xls->headers($nop);
			echo $xls->buildFile();
		}else{
			echo $tab;
		}
			
	break;
}

function numb_format($a,$d=0){
	$n = hidezerodecimal($a,$d);
	if($n=='0' or $n==''){
		$n="";
	}else{
		$n=$n;
	}
	return $n;
}
?>	