<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/sdm_karyawan_helper.php');
require_once('lib/zLib.php');
$method	= "";
$result = array();
$tipedoc="";
$karyawanid='';
$fileupload='';
$kriteriaefil='';
$tipefile='';
$namafile='';

if(isset($_GET['method'])){
	$method = $_GET['method'];
}
if(isset($_POST['karyawanid'])){
	$karyawanid = $_POST['karyawanid'];
}
if(isset($_POST['tipedoc'])){
	$tipedoc = $_POST['tipedoc'];
}
if(isset($_POST['fileupload'])){
	$fileupload = $_POST['fileupload'];
}
if(isset($_POST['kriteriaefil'])){
	$kriteriaefil = $_POST['kriteriaefil'];
}
if(isset($_POST['tipefile'])){
	$tipefile = $_POST['tipefile'];
}
if(isset($_POST['namafile'])){
	$namafile = $_POST['namafile'];
}
$param = $_POST;if(count($param)==0){$param = $_GET;}
$karyawanidNum = sdmId($karyawanid);

# nama berkas aman: tanpa folder dan karakter khusus
function dok_namaaman($v){
	$v = basename(str_replace('\\', '/', (string)$v));
	$v = preg_replace('/[^A-Za-z0-9._ -]/', '_', $v);
	return ltrim($v, '.');
}
# simpan berkas dari data URI; hanya jpg, png, pdf. Hasil false bila berkas ditolak
function upload_file($pathlocation,$data,$rename){
	if($data == ""){
		return "";
	}
	$file = explode(',',$data);
	if(count($file) < 2){
		return false;
	}
	$stream = base64_decode(str_replace(' ', '+', $file[1]), true);
	if($stream === false || $stream === ""){
		return false;
	}
	$f = finfo_open();
	$mime_type = finfo_buffer($f, $stream, FILEINFO_MIME_TYPE);
	$allowed = array("image/jpeg" => ".jpg", "image/png" => ".png", "application/pdf" => ".pdf");
	if(!isset($allowed[$mime_type])){
		return false;
	}
	if (!file_exists($pathlocation)) {
		mkdir($pathlocation, 0755, true);
	}
	$filename = $rename.$allowed[$mime_type];
	file_put_contents($pathlocation.$filename, $stream);
	return $filename;
}
$str = array();
switch($method){

	case 'showupload':
		$optkriteria = '';
		if(isset($param['jenis']) and preg_match('/^[A-Za-z0-9_-]+$/', $param['jenis'])){
			$restipe = $owlPDO->prepare("select * from ".$dbname.".sdm_5tipedokumen where isactive='1' and id=?");
			$restipe->execute(array($param['jenis']));
		}else{
			$restipe = $owlPDO->prepare("select * from ".$dbname.".sdm_5tipedokumen where isactive='1'");
			$restipe->execute();
		}
		$restipe->setFetchMode(PDO::FETCH_OBJ);
		while($baru=$restipe->fetch()){
			$optkriteria.="<option value='".sdmH($baru->id)."'>".sdmH($baru->namatipe)."</option>";
	    }

		$tab="<table cellspacing='1' border='0' id='uploadpopup'>
			<tr>
				<td>Kriteria</td>
				<td>:</td>
				<td>
					<select id='kriteriaefil'>". $optkriteria."</select>
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='uploadkar' class=mybutton>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button class=mybutton onclick=\"submitfilekar()\">Submit</button>
				</td>
			</tr>
		</table>
		<p />";

		$tab.="
			<table class='sortable' cellpadding=5 cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center'>No.</td>
					<td align='center'>File Type</td>
					<td align='center'>Kriteria</td>
					<td align='center'>Filename</td>
					<td align='center' colspan=2>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		";

		echo $tab;
	break;

	case 'submitfile':
	if ($karyawanid=='') {
		exit('Warning : Silahkan Simpan Data Dahulu');
	}
        $tgl = date("YmdHis");
        $data = $_POST;

        if(isset($data['fileupload']) and $data['fileupload']!='')
        {
            if(isset($_FILES['file']) and $_FILES['file']['error']==0)
            {
                $namaasli = dok_namaaman($_FILES['file']['name']);
                $filetype = strtolower('.'.substr($namaasli,strripos($namaasli,'.')+1));
                $newfilename = substr($namaasli,0,strripos($namaasli,'.'));
                if($newfilename==='' or $newfilename===false)
                {
                    $newfilename = 'dokumen';
                }
                $filename = $newfilename."_".$tgl."".$filetype;
                $file_tmpname = $_FILES['file']['tmp_name'];

                if(in_array($filetype, array('.jpeg','.jpg','.png','.pdf','.xls','.xlsx','.doc','.docx','.rar')))
                {
                    if($_FILES['file']['size'] <= 512000)
                    {
                        move_uploaded_file($file_tmpname,"fileupload/karyawan/$filename");
                    }
                    else
                    {
                        exit("warning : Ukuran file upload maksimal 250kb");
                    }
                }else{
                    exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
                }

                try{
                    $st = $owlPDO->prepare("insert into ".$dbname.".sdm_karyawandokumen (`karyawanid`,`tipedokumen`,`namafile`,`tipefile`,`updateby`) values(?,?,?,?,?)");
                    $st->execute(array($karyawanidNum,$kriteriaefil,$filename,$filetype,$_SESSION['standard']['userid']));
                }catch (PDOException $e){
                    error_log('data karyawan upload: '.$e->getMessage());
                    print "Gagal, DB Error: data tidak dapat disimpan.<br/>";
                    die();
                }
            }
        }
    break;

    case 'loadfileskar':
      	$nmtipe=makeOption($dbname,'sdm_5tipedokumen','id,namatipe');
        $tab = "";
        $no = 0;
        $redy=$owlPDO->prepare("select * from ".$dbname.".sdm_karyawandokumen where karyawanid = ?");
        $redy->execute(array($karyawanidNum));
        $redy->setFetchMode(PDO::FETCH_OBJ);
        while($r=$redy->fetch()){
			$no+=1;
			$tab.="<tr class=rowcontent >
				<td style='text-align:center'>".$no."</td>
				<td style='text-align:center'>".sdmH($r->tipefile)."</td>
				<td style='text-align:center'>".sdmH(isset($nmtipe[$r->tipedokumen]) ? $nmtipe[$r->tipedokumen] : '')."</td>
				<td style='text-align:center'>".sdmH($r->namafile)."</td>
				<td style='text-align:center;width:25px'>
					<a href='fileupload/karyawan/".rawurlencode($r->namafile)."' download><img src=images/uploader/dwnld8.png class=resicon  title='PNG'></a>
				</td>
				<td style='text-align:center;width:25px'>
					<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$karyawanidNum."','".sdmJs($r->tipedokumen)."','".sdmJs($r->namafile)."');\" ></td>
				</tr>";
        }
        echo $tab;
    break;

    case'deletefile':
        $nm = dok_namaaman($namafile);
        try {
            $cek = $owlPDO->prepare("select count(*) from `".$dbname."`.`sdm_karyawandokumen` where karyawanid=? and tipedokumen=? and namafile=?");
            $cek->execute(array($karyawanidNum,$tipefile,$nm));
            if($cek->fetchColumn()==0){
                print " Gagal  !: Data tidak ditemukan.";
                break;
            }
            $st = $owlPDO->prepare("delete from `".$dbname."`.`sdm_karyawandokumen` where karyawanid=? and tipedokumen=? and namafile=?");
            $st->execute(array($karyawanidNum,$tipefile,$nm));
        } catch (PDOException $e) {
            error_log('data karyawan upload: '.$e->getMessage());
            print " Gagal, DB Error: data tidak dapat dihapus.<br/>";
            die();
        }
        foreach(array("fileupload/karyawan/".$nm, "fileupload/karyawan/".$karyawanidNum."/".$nm) as $path){
            if(is_file($path)){
                unlink($path);
            }
        }
    break;
	case 'insert':
		if($karyawanidNum == 0 || !preg_match('/^[A-Za-z0-9_-]{1,20}$/', $tipedoc)){
			$result['wrong'] = "true";
			$result['message'] = "Gagal  !: Data tidak lengkap atau tidak valid.";
			$result['karyawanid'] = '';
			$result['namafile'] = '';
			break;
		}
		$path = "./fileupload/karyawan/".$karyawanidNum."/";
		$rename = "document_".$tipedoc;
		$namefile = upload_file($path,$fileupload,$rename);
		if($namefile === false){
			$result['wrong'] = "true";
			$result['message'] = "Gagal  !: Berkas harus berupa JPG, PNG, atau PDF yang valid.";
			$result['karyawanid'] = '';
			$result['namafile'] = '';
			break;
		}
		$find = $owlPDO->prepare("select karyawanid from ".$dbname.".sdm_karyawandokumen where karyawanid=? and tipedokumen=?");
		$find->execute(array($karyawanidNum,$tipedoc));
		if(count($find->fetchAll()) == 0){
			$str[] = array("insert into ".$dbname.".sdm_karyawandokumen (`karyawanid`,`tipedokumen`,`namafile`,`updateby`) values(?,?,?,?)",
				array($karyawanidNum,$tipedoc,$namefile,$_SESSION['standard']['userid']));
		}else{
			$str[] = array("update ".$dbname.".sdm_karyawandokumen set `namafile`=?,`updateby`=? where karyawanid=? and tipedokumen=?",
				array($namefile,$_SESSION['standard']['userid'],$karyawanidNum,$tipedoc));
		}

		for($i=0; $i<count($str); $i++){
			try{
				$st = $owlPDO->prepare($str[$i][0]);
				$st->execute($str[$i][1]);
				$result['wrong'] = "false";
				$result['message'] = "";
				$result['karyawanid'] = $karyawanid;
				$result['namafile'] = $namefile;
			}catch(PDOException $e){
				error_log('data karyawan upload: '.$e->getMessage());
				$result['wrong'] = "true";
				$result['message'] = "Gagal  !: Data tidak dapat disimpan.";
				$result['karyawanid'] = '';
				$result['namafile'] = '';
			}
		}
	break;
	case 'loaddata';
		$find = $owlPDO->prepare("select * from ".$dbname.".sdm_karyawandokumen where karyawanid=?");
		$find->execute(array($karyawanidNum));
		$data['listdoc'] = $find->fetchAll(PDO::FETCH_ASSOC);
		$find = $owlPDO->query("select * from ".$dbname.".sdm_5tipedokumen where untuk = 'KARYAWAN' and isactive = '1'");
		$data['listtipe'] = $find->fetchAll(PDO::FETCH_ASSOC);
		$result = $data;
	break;
}

if(!empty($result)){
	echo json_encode($result);
}
