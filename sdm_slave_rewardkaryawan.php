<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/sdm_karyawan_helper.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

$param=$_POST;
$proses=checkPostGet('proses','');

$namafile=checkPostGet('namafile','');
$tab='';

$karyawanid=0;
$namareward='';
if(isset($_POST['karyawanid'])){
	$karyawanid = sdmId($_POST['karyawanid']);
}
if(isset($_POST['namareward'])){
	$namareward = $_POST['namareward'];
}
# nama berkas aman: tanpa folder dan karakter khusus
if(!function_exists('rwd_namaaman')){
	function rwd_namaaman($v){
		$v = basename(str_replace('\\', '/', (string)$v));
		$v = preg_replace('/[^A-Za-z0-9._ -]/', '_', $v);
		return ltrim($v, '.');
	}
}
switch ($proses) {

    case 'showformfp':
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable style='width:100%'>
            <tr  class=rowcontent>
            <td>".$_SESSION['lang']['historynofp']."</td>
            <td>:</td>
            <td  colspan=2><input type=text id=historynofp onkeypress=\"return tanpa_kutip(event);\" class=myinputtext  style=\"width:150px;\" value='' /></td>
            </tr>
            <tr  class=rowcontent>
            <td>".$_SESSION['lang']['tanggal']."</td>
            <td>:</td>
            <td><input type=text class=myinputtext readonly  id=historytanggalfp onmousemove=setCalendar(this.id) onkeypress=return false;  size=10 maxlength=10 style=\"width:150px;\" value=''    />
            <button class=mybutton onclick=savefp('')>Simpan</button></td>
            </tr>
            </table>";
        echo $tab;
    break;

	case 'submitfile':

		$tgl = date("YmdHis");
		$data = $_POST;

		if(isset($data['fileupload']) and $data['fileupload']!='')
		{
			if(isset($_FILES['file']) and $_FILES['file']['error']==0)
			{
				$namaasli = rwd_namaaman($_FILES['file']['name']);
				$filetype = strtolower('.'.substr($namaasli,strripos($namaasli,'.')+1));
				$newfilename = substr($namaasli,0,strripos($namaasli,'.'));
				if($newfilename==='' or $newfilename===false)
				{
					$newfilename = 'reward';
				}
				$filename = $newfilename."_".$tgl."".$filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];

				if(!in_array($filetype, array('.jpeg','.jpg','.png','.pdf','.xls','.xlsx','.doc','.docx','.rar')))
				{
					exit("Warning : Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
				}
				if($_FILES['file']['size'] > 512000)
				{
					exit("warning : Ukuran file upload maksimal 250kb");
				}
				$tglreward = sdmTglSistem(isset($data['tanggalreward']) ? $data['tanggalreward'] : '');
				if($karyawanid==0 or $tglreward===null or !isset($data['namareward']) or $data['namareward']=='')
				{
					exit("Warning : Data reward tidak lengkap atau tanggal tidak valid");
				}
				move_uploaded_file($file_tmpname,"fileupload/karyawan/$filename");

				try{
					$st=$owlPDO->prepare("insert into ".$dbname.".sdm_karyawandokumen (`karyawanid`,`tipedokumen`,`namafile`,`updateby`) values(?,'13',?,?)");
					$st->execute(array($karyawanid,$filename,$_SESSION['standard']['userid']));
					$st=$owlPDO->prepare("insert into ".$dbname.".sdm_karyawanreward (`karyawanid`,`namareward`,`tanggal`,`updateby`) values(?,?,?,?)");
					$st->execute(array($karyawanid,$data['namareward'],$tglreward,$_SESSION['standard']['userid']));
				}catch (PDOException $e){
					error_log('data karyawan reward: '.$e->getMessage());
					print "Gagal, DB Error: data tidak dapat disimpan.<br/>";
					die();
				}
			}
		}
	break;

	case 'loadfiles':
		$tab = "";
		$no = 0;
		$redy=$owlPDO->prepare("select * from ".$dbname.".sdm_karyawanreward a left join ".$dbname.".sdm_karyawandokumen b on a.karyawanid=b.karyawanid where a.karyawanid = ? group by b.namafile");
		$redy->execute(array($karyawanid));
		$redy->setFetchMode(PDO::FETCH_OBJ);
		while($r=$redy->fetch())
		{
			$no+=1;
		  $tab.="<tr class=rowcontent >
					<td style='text-align:center'>".$no."</td>
					<td style='text-align:center'>".sdmH($r->namareward)."</td>
					<td style='text-align:center'>".sdmH($r->tanggal)."</td>
					<td style='text-align:center'>".sdmH($r->namafile)."</td>
					<td style='text-align:center'>
						<a href='fileupload/karyawan/".rawurlencode($r->namafile)."' download><img src=images/uploader/dwnld8.png class=resicon  title='PNG'></a>

					<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$karyawanid."','".sdmJs($r->namafile)."','".sdmJs($r->namareward)."');\" ></td>
					</tr>";
		}
		echo $tab;
	break;

	case'deletefile':
		$nm = rwd_namaaman($namafile);
		try {
			$st=$owlPDO->prepare("delete from `".$dbname."`.`sdm_karyawanreward` where karyawanid=? and namareward=?");
			$st->execute(array($karyawanid,$namareward));
			$st=$owlPDO->prepare("delete from `".$dbname."`.`sdm_karyawandokumen` where karyawanid=? and namafile=? and tipedokumen='13'");
			$st->execute(array($karyawanid,$nm));
			$hapus = $st->rowCount();
		} catch (PDOException $e) {
			error_log('data karyawan reward: '.$e->getMessage());
			print " Gagal, DB Error: data tidak dapat dihapus.<br/>";
			die();
		}
		if($hapus > 0 and is_file("fileupload/karyawan/".$nm)){
			unlink("fileupload/karyawan/".$nm);
		}
	break;

	case'clearreward':
		$_SESSION['efiltgh'] = array();
	break;
}

?>
