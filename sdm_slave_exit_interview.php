<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
// Prosses
$proses = checkPostGet('proses','');
$hal 	= checkPostGet('hal','');
$txtsearch 	= checkPostGet('txtsearch','');

// POST HEADER
$notransaksi = "";
if(isset($_POST['notransaksi'])){
	$notransaksi = $_POST['notransaksi'];
}
$karyawanid = "";
if(isset($_POST['karyawanid'])){
	$karyawanid = $_POST['karyawanid'];
}
$email = "";
if(isset($_POST['email'])){
	$email = $_POST['email'];
}
$nohp = "";
if(isset($_POST['nohp'])){
	$nohp = $_POST['nohp'];
}
$hrdinterview = "";
if(isset($_POST['hrdinterview'])){
	$hrdinterview = $_POST['hrdinterview'];
}
$tglinterview = "";
if(isset($_POST['hrdinterview'])){
	$tglinterview = date('Y-m-d',strtotime($_POST['tglinterview']));
}
$tanggalkeluar = "";
if(isset($_POST['tanggalkeluar'])){
	$tanggalkeluar = date('Y-m-d',strtotime($_POST['tanggalkeluar']));
}

$result['err'] = "false";
$result['mssg'] = "";
//FUNCTION
function getFormatHtml($data,$value){
	$result = "";
	$val = "";
	if(isset($value)){
		if($value['id'] == $data['id']){
			$val = $value['answer'];
		}
	}
	switch($data['jenis_text']){
		case 'CB':
			$selected = "";
			If($val != "" and $val == $data['id']){
				$selected = "checked";
			}
			$result = '<input type="checkbox" name="jawaban_'.$data['id'].'" value="'.$data['id'].'" '.$selected.'>'.$data['text'];
		break;
		case 'OPT':
			If($val != ""){
				$value = explode(',',$val);
			}
			$result = $data['text'];
			$result .= '<div class="clearfix"></div>';
			$result .= '<ol class="alphabet optjawabanboth">';
			if(count($value)>0){
				foreach($value as $v){
					$result .= '<li class="optexample"><input type="text" value="'.$v.'" class="optjawaban" name="jawaban_'.$data['id'].'[]" maxlength="30" onblur="replaceSpliter(this);"></li>';	
				}
			}else{
				$result .= '<li class="optexample"><input type="text" class="optjawaban" name="jawaban_'.$data['id'].'[]" maxlength="30" onblur="replaceSpliter(this);"></li>';
			}
			$result .= '</ol><a style="cursor:pointer;margin-left:10px;float:left;margin-top: 9px;" title="Tambah" onclick="create_new_field(this);"><img src="images/plus.png" width="15"></a>';
			$result .= '<div class="clearfix"></div>';
		break;
		case 'INP':
			$result = $data['text'];
			$result .= '<br/><input type="text" value="'.$val.'" class="inpjawaban" name="jawaban_'.$data['id'].'" maxlength="255">';
		break;
		case 'BCK':
			$bck = array('B'=>'Sangat Baik','C'=>'Cukup','K'=>'Kurang');
			$result = '<div class="colmn_jawaban_left w-400">';
			$result .= $data['text'];
			$result .= '</div>';
			$result .= '<div class="colmn_jawaban_left">';
			foreach($bck as $k => $v){
				$selected = "";
				If($val != "" and $val == $k){
					$selected = "checked";
				}
				$result .= '<input type="radio" class="inpjawaban" name="jawaban_'.$data['id'].'" value="'.$k.'" '.$selected.'><span>'.$v.'</span>';
			}
			$result .= '</div>';
			$result .= '<div class="clearfix"></div>';
		break;
		case 'PRN':
			$result = "<b>".$data['text']."<b>";
		break;
		case 'URAI':
			$result = $data['text'];
			$result .= '<br/><textarea maxlength="255" name="jawaban_'.$data['id'].'">'.$val.'</textarea>';
		break;
		case 'YN':
			$ynArr = array('Y'=>'Ya','N'=>'Tidak');
			$result = '<div class="colmn_jawaban_left w-400">';
			$result .= $data['text'];
			$result .= '</div>';
			$result .= '<div class="colmn_jawaban_left">';
			foreach($ynArr as $k => $v){
				$selected = "";
				If($val != "" and $val == $k){
					$selected = "checked";
				}
				$result .= '<input type="radio" class="inpjawaban" name="jawaban_'.$data['id'].'" value="'.$k.'" '.$selected.'><span>'.$v.'</span>';
			}
			$result .= '</div>';
			$result .= '<div class="clearfix"></div>';
		break;
	}
	return $result;
}
function findAnswerId($id,$jawaban){
	$result['id'] = "";
	$result['answer'] = "";
	foreach($jawaban as $v){
		if($v['id'] == $id){
			$result = $v;
			break;
		}
	}
	return $result;
}
// OPTION Select
$where = "kodeparameter='PJDHRD'";
$setup_parameterappl =makeOption($dbname,'setup_parameterappl','kodeparameter,nilai',$where);
if($_SESSION['empl']['bagian'] == $setup_parameterappl['PJDHRD']){
	$youAre = "HRD";
}else{
	$youAre = "USER";
}
$where2 = "";
if($youAre == "HRD"){
	$where2 = "";
}elseif($youAre == "USER"){
	$where2 = "karyawanid='".$_SESSION['standard']['userid']."'";
}
$optKar = "";
$datakaryawan=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$where2);
foreach($datakaryawan as $k => $v){
	if($karyawanid == $k){
		$optKar .= "<option value='".$k."' selected>".$v."</option>";
	}else{
		$optKar .= "<option value='".$k."'>".$v."</option>";
	}
}
//list HRD
$where3 = "bagian = '".$setup_parameterappl['PJDHRD']."'";
$datahrd=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$where3);

// ACTION
switch ($proses) {
	case'loadlist':
		include_once('lib/paging.php');
		// VIEW LIST DATA AFTER INSERT
		$hrdId =array();
		foreach($datahrd as $k => $v){
			$hrdId[] = "'".$k."'";
		}
		if(count($hrdId) > 0){
			$allHRD = "or a.createby in (".implode(",",$hrdId).")";
		}
		$limit = 20;//-Paging-
		$halaman_aktif = $hal; //-Paging-Phalaman saat ini
		$p = new Paging; // -Paging- Class paging
		$posisi = $p->cariPosisi($limit,$halaman_aktif);// -Paging- Posisi Data
		
		$selectExitInterviewHT = "select a.*,b.namakaryawan from sdm_exit_interview_ht a 
		left join datakaryawan b on a.karyawanid = b.karyawanid";
		if($youAre == "USER"){
			$selectExitInterviewHT .= " where a.createby='".$_SESSION['standard']['userid']."'";// View yg hanya di buat sendiri
		}elseif($youAre == "HRD"){
			$selectExitInterviewHT .= " where a.status = '1' ".$allHRD."";// View yg sudah di submit atau yng buat HRD 
		}
		if($txtsearch!=""){
			echo "<b>Search : </b>".$txtsearch;
			$selectExitInterviewHT .= " and a.notransaksi like '%".$txtsearch."%'";// View yg sudah di submit atau yng buat HRD 
		}
		$jmldata = count(fetchData($selectExitInterviewHT));
		$qlimit = " LIMIT $posisi,$limit";
		$ResExitInterviewHT = fetchData($selectExitInterviewHT.$qlimit);
		
		$jml = $p->jumlahHalaman($jmldata,$limit);//-Paging- jumlah data
		
		$html = '<table class="sortable" border="0" cellspacing="1">';
		$html .= "<thead>";
		$html .= "<tr>
					<th>No</th>
					<th>No transaksi</th>
					<th>Nama karyawan</th>
					<th>Email Aktif</th>
					<th>Handphone</th>
					<th>HRD Interview</th>
					<th>Tanggal Interview</th>
					<th>Post</th>
				</tr>";
		$html .= "</thead>";
		$html .= "<tbody>";
		if(count($ResExitInterviewHT) > 0){
			$no = 1;
			foreach($ResExitInterviewHT as $v){
				$html .= '<tr class="rowcontent">
					<td>'.$no.'</td>
					<td>'.$v['notransaksi'].'</td>
					<td>'.$v['namakaryawan'].'</td>
					<td>'.$v['email_aktif'].'</td>
					<td>'.$v['handphone'].'</td>
					<td>'.$v['hrd_interview'].'</td>
					<td>'.$v['tgl_interview'].'</td>
					<td align="center">';
				if($v['status'] == '0'){
					$html .= '<button class="mybutton" onclick="posting(\''.$v['notransaksi'].'\')">Submit</button>';
				}else{
					$html .= '<img src="images/check.png" width="15">';
				}
				$html .= '</td>
				</tr>';
				$no++;
			}
		}
		$html .= "</tbody>";
		echo  $html;
		?>
			<tfoot>
				<tr>
					<td colspan="9" align="center">
					<?php 
						//insert Attribute action ex: href/onclick/onchange/etc..
						$buttonaction = array(
							'first' =>	'onclick="loadlist(\'1\');"',
							'prev' 	=> 	'onclick="loadlist(\''.($halaman_aktif-1).'\')"',
							'next' 	=> 	'onclick="loadlist(\''.($halaman_aktif+1).'\')"',
							'last' 	=> 	'onclick="loadlist(\''.($jml).'\')"',
							'pages'	=> 	'onchange="loadlist(this.value);"'
						);
						echo $p->navHalaman($halaman_aktif,$jml,$buttonaction); //-Paging- Create Element Nav halaman; 
					?>
					</td>
				</tr>
			</tfoot>
		</table>
		<?php	
	break;
	case'loadform':
		// VIEW FORM INSERT/UPDATE
		$departement = "";
		$jabatan = "";
		$tglMasuk = "";
		$tglKeluar = "";
		$namaorganisasi = "";
		$hrd_interview = "";
		$tgl_interview = "";
		$email = "";
		$nohp = "";
		$posting = "0";
		
		$jawaban = array();
		if($karyawanid != ""){
			//Data karyawan
			$selectKaryawan = "select a.*,b.nama as departement,c.namajabatan as namajabatan,d.namaorganisasi as pt from datakaryawan a 
			left join sdm_5departemen b on b.kode = a.bagian
			left join sdm_5jabatan c on c.kodejabatan = a.kodejabatan
			left join organisasi d on d.kodeorganisasi = a.kodeorganisasi
			where a.karyawanid = '".$karyawanid."' limit 1";
			$datakaryawan = fetchData($selectKaryawan)[0];
			$departement = $datakaryawan['departement'];
			$jabatan = $datakaryawan['namajabatan'];
			$tglMasuk = date("d-m-Y",strtotime($datakaryawan['tanggalmasuk']));
			$namaorganisasi = $datakaryawan['pt'];
			
			//Jawaban
			$selectExitInterviewHT = "select * from sdm_exit_interview_ht
			where karyawanid = '".$karyawanid."' limit 1";
			$ResExitInterviewHT = fetchData($selectExitInterviewHT);
			if(count($ResExitInterviewHT) > 0){
				$ResExitInterviewHT = $ResExitInterviewHT[0];
				$notransaksi = $ResExitInterviewHT['notransaksi'];
				$hrd_interview = $ResExitInterviewHT['hrd_interview'];
				$tgl_interview = date("d-m-Y",strtotime($ResExitInterviewHT['tgl_interview']));
				$email = $ResExitInterviewHT['email_aktif'];
				$nohp = $ResExitInterviewHT['handphone'];
				$posting = $ResExitInterviewHT['status'];
				$tglKeluar = date("d-m-Y",strtotime($ResExitInterviewHT['tgl_keluar']));
				
				//Jawaban DT
				$selectExitInterviewDT = "select * from sdm_exit_interview_dt
				where notransaksi = '".$notransaksi."'";
				$jawaban = fetchData($selectExitInterviewDT);
			}
		}
		
		
	?>
		<form method="POST" action="sdm_slave_exit_interview.php?proses=savedata" onsubmit="javascript:inputData(this,afterInsert,validationInsert);return false;">
			<div style="border:1px #000 solid; padding:10px;margin:20px 0px;">
				Kami akan menghargai Anda meluangkan waktu untuk menjawab pertanyaan-pertanyaan berikut sejujur mungkin. Jawaban Anda diperlakukan  rahasia.<br/>
				Kami percaya bahwa informasi ini sangat penting dan akan membantu kami dalam menganalisis faktor yang berkontribusi terhadap kemajuan perusahaan dimasa mendatang.
			</div>
			<table>
				<tr>
					<td>Nama </td>
					<td><select id="datakaryawan" name="karyawanid" onchange="loaddata(this);">
						<option value=""></option>
						<?php echo $optKar; ?>
					</select><img id="nikmandor_find" onclick="z.elSearch('datakaryawan',event)" class="resicon" src="images/onebit_02.png" style="position:relative;top:3px;left:3px;"></td>
					<td><img src="images/obl.png" title="Obligatory"></td>
					<td>Departemen </td>
					<td>: <?php echo $departement; ?></td>
					<td></td>
				</tr>
				<tr>
					<td>No HP </td>
					<td><input value="<?php echo $nohp; ?>" name="nohp" type="text" class="myinputtext" required></td>
					<td><img src="images/obl.png" title="Obligatory"></td>
					<td>Jabatan </td>
					<td>: <?php echo $jabatan; ?></td>
					<td></td>
				</tr>
				<tr>
					<td>Email </td>
					<td><input value="<?php echo $email; ?>" name="email" type="email" class="myinputtext" required></td>
					<td><img src="images/obl.png" title="Obligatory"></td>
					<td>Tanggal Masuk </td>
					<td>: <?php echo $tglMasuk; ?></td>
					<td></td>
				</tr>
				<tr>
					<td>HRD Interview </td>
					<td><select id="hrdinterview" name="hrdinterview">
						<option value=""></option>
						<?php 
							$optHrd = "";
							foreach($datahrd as $k => $v){
								if($hrd_interview == $k){
									echo $optHrd .= "<option value='".$k."' selected>".$v."</option>";
								}else{
									echo $optHrd .= "<option value='".$k."'>".$v."</option>";
								}
							}
						?>
					</select><img id="nikmandor_find" onclick="z.elSearch('hrdinterview',event)" class="resicon" src="images/onebit_02.png" style="position:relative;top:3px;left:3px;"></td>
					<td><img src="images/obl.png" title="Obligatory"></td>
					<td>Perusahaan </td>
					<td>: <?php echo $namaorganisasi; ?></td>
					<td></td>
				</tr>
				<tr>
					<td>Tanggal Interview </td>
					<td><input id="tglinterview" name="tglinterview" type="text" class="myinputtext" value="<?php echo $tgl_interview; ?>" onmousemove="setCalendar(this.id)"></td>
					<td><img src="images/obl.png" title="Obligatory"></td>
					<td>Tanggal Keluar </td>
					<td><input id="tanggalkeluar" name="tanggalkeluar" type="text" class="myinputtext" value="<?php echo $tglKeluar; ?>" onmousemove="setCalendar(this.id)"></td>
					<td></td>
				</tr>
			</table>
			<hr>
				<ol class="pertanyaan">
			<?php 
				
				$query 		= "select  * from  sdm_5question_exit where status = '1' and induk = '0'";
				$pertanyaan = fetchData($query);
				foreach($pertanyaan as $v){
					echo "<li>".getFormatHtml($v,findAnswerId($v['id'],$jawaban))."";
						$subquery	= "select  *
									from    (select * from sdm_5question_exit
											 order by induk, id) sorted,
											(select @pv := '".$v['id']."') initialisation
									where   find_in_set(induk, @pv) > 0
									and     @pv := concat(@pv, ',', id)";
						$listjawaban = fetchData($subquery);
						if(count($listjawaban)>0){
							echo "<ul class='listjawaban'>";
							$idPrtn = 'false';
							foreach($listjawaban as $subv){
								
								if($idPrtn !== $subv['induk'] and $idPrtn !== 'false'){
									$idPrtn = 'false';
									echo "</ul>";
								}
								echo "<li class=".$subv['jenis_text'].">".getFormatHtml($subv,findAnswerId($subv['id'],$jawaban))."";
								if($subv['jenis_text'] == 'PRN'){
									$idPrtn = $subv['id'];
									echo "<ul class='listjawaban'>";
								}
								echo "</li>";
							}
							echo "</ul>";
						}
					echo "</li>";
				}
			?>
			</ol>
			<?php if($posting == "0"){ ?>
			<input class="mybutton" name="submit" type="submit" value="Save" >
			<?php }?>
		</form>
	<?php	
	break;
	case'savedata':
		// ACTION INSERT / UPDATE DATA
		$notrans 	= "EI-".date("YmdHis");
		$queryht	= "select * from  sdm_exit_interview_ht where karyawanid = '".$karyawanid."' limit 1";
		$resHT 		= fetchData($queryht);
		if(count($resHT) > 0){
			if($resHT[0]['status'] == '0'){
				$notrans = $resHT[0]['notransaksi'];
				$qInsert = "update sdm_exit_interview_ht set";
				$qInsert .= " email_aktif = '".$email."',";
				$qInsert .= " handphone = '".$nohp."',";
				$qInsert .= " hrd_interview = '".$hrdinterview."',";
				$qInsert .= " tgl_interview = '".date("Y-m-d",strtotime($tglinterview))."',";
				$qInsert .= " tgl_keluar = '".date("Y-m-d",strtotime($tanggalkeluar))."',";
				$qInsert .= " updateby = '".$_SESSION['standard']['userid']."'";
				$qInsert .= "where karyawanid = '".$karyawanid."';";
				//delete data detail jika ada
				$qInsert .= "delete from sdm_exit_interview_dt where notransaksi = '".$notrans."';";
				
			}else{
				exit("WARNING! Data sudah terposting");
			}
		}else{
			$qInsert = "insert into sdm_exit_interview_ht (notransaksi,karyawanid,email_aktif,handphone,hrd_interview,tgl_interview,createby,createdtime) value";
			$qInsert .= "('".$notrans."','".$karyawanid."','".$email."','".$nohp."','".$hrdinterview."','".date("Y-m-d",strtotime($tglinterview))."','".$_SESSION['standard']['userid']."','".date("Y-m-d H:i:s")."');";
		}
	
		$query 		= "select  * from  sdm_5question_exit where status = '1'";
		$listQuestions = fetchData($query);
		$jawaban = array();
		foreach($listQuestions as $v){
			$nameField = "jawaban_".$v['id'];
			if(isset($_POST[$nameField]) and $_POST[$nameField] != ""){
				$postData = "";
				if(is_array($_POST[$nameField])){
					$postData = implode(",",$_POST[$nameField]);
				}else{
					$postData = $_POST[$nameField];
				}
				$j['id'] = $v['id'];
				$j['answer'] = $postData;
				$jawaban[] = $j;
			}
		}
		//Insert data detail
		$qInsert .= "insert into sdm_exit_interview_dt (notransaksi,id,answer) value ";
		for($i=0; $i<count($jawaban); $i++){
			if($i == (count($jawaban)-1)){
				$qInsert .= "('".$notrans."','".$jawaban[$i]['id']."','".$jawaban[$i]['answer']."');";
			}else{
				$qInsert .= "('".$notrans."','".$jawaban[$i]['id']."','".$jawaban[$i]['answer']."'),";
			}
		}
		//exit("ERROR:".$qInsert);
		try{
            $owlPDO->exec($qInsert);
			$result['err'] = "false";
			$result['mssg'] = "Insert Data Success";
        } catch (PDOException $e) {
            $result['err'] = "true";
			$result['mssg'] = "Gagal: " . $e->getMessage() . "\n";
            die();
        }
	break;
	case 'posting':
		$qInsert = "update sdm_exit_interview_ht set status = '1' where notransaksi = '".$notransaksi."'";
		try{
            $owlPDO->exec($qInsert);
			$result['err'] = "false";
			$result['mssg'] = "Posting Success";
        } catch (PDOException $e) {
			$result['err'] = "true";
			$result['mssg'] = "Gagal: " . $e->getMessage() . "\n";
            die();
        }
		echo json_encode($result);
	break;
	
}

?>