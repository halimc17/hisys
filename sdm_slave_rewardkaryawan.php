<?php
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');

@$proses=($_GET['proses'] == '' ? $_POST['proses'] : $_GET['proses']);
$param=$_POST;
$proses=checkPostGet('proses','');

$tanggal=tanggalsystemn(checkPostGet('tanggal',''));
$kodeorg=checkPostGet('kodeorg','');
$namafile=checkPostGet('namafile','');
$notransaksi=checkPostGet('notransaksi','');

if(isset($_GET['method'])){
	$method = $_GET['method'];
}
if(isset($_POST['id'])){
	$id = $_POST['id'];
}
if(isset($_POST['karyawanid'])){
	$karyawanid = $_POST['karyawanid'];
}
if(isset($_POST['fileupload'])){
	$fileupload = $_POST['fileupload'];
}
if(isset($_POST['namareward'])){
	$namareward = $_POST['namareward'];
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
            <button class=mybutton onclick=savefp('".$noinvoice."')>Simpan</button></td>
            </tr>
            </table>";
        echo $tab;
    break;

    
    
	
	
	case 'submitfile':

		$tgl = date("YmdHis");
		$data = $_POST;
	

/*echo "<pre>";
print_r($data);
exit('error');*/
echo "</pre>";
		
		if($data['fileupload']!='')
		{
			if($_FILES['file']['error']==0)
			{
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $newfilename."_".$tgl."".$filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];		
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')||($filetype=='.rar'))
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

				$str = "
				insert into ".$dbname.".sdm_karyawandokumen
			 (`karyawanid`,
			  `tipedokumen`,
			  `namafile`,
			  `updateby`
			  )
			  values(".$karyawanid.",
			  '13',
			  '".$filename."',
			  '".$_SESSION['standard']['userid']."'
			  );

			  insert into ".$dbname.".sdm_karyawanreward
			 (`karyawanid`,
			  `namareward`,
			  `tanggal`,
			  `updateby`
			  )
			  values(".$karyawanid.",
			  '".$data['namareward']."',
			  '".tanggalsystemn($data['tanggalreward'])."',
			  '".$_SESSION['standard']['userid']."'
			  )";
				try{
					$owlPDO->exec($str);
					// copy($val['location'], "filegis/".$val['namafile']);
				}catch (PDOException $e){print "DB Error  1!: ".$e->getMessage()."<br/>";die();}
			
			}
		}
	break;
	
	case 'loadfiles':

					/*$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
					$filename = $newfilename."_".$tgl."".$filetype;
					$file_tmpname = $_FILES['file']['tmp_name'];	*/


		$tab = "";
		$q="select * from ".$dbname.".sdm_karyawanreward a left join ".$dbname.".sdm_karyawandokumen b on a.karyawanid=b.karyawanid where a.karyawanid = '".$karyawanid."' group by b.namafile";
		$redy=$owlPDO->query($q) or die(print " Gagal: ".PDOException::getMessage());
		$redy->setFetchMode(PDO::FETCH_OBJ);
		$html = "";
		while($r=$redy->fetch())
		{
			$no+=1;
		  $tab.="<tr class=rowcontent >
					<td style='text-align:center'>".$no."</td>
					<td style='text-align:center'>".$r->namareward."</td>
					<td style='text-align:center'>".$r->tanggal."</td>
					<td style='text-align:center'>".$r->namafile."</td>
					<td style='text-align:center'>
						<a href='fileupload/karyawan/$r->namafile' download><img src=images/uploader/dwnld8.png class=resicon  title='PNG'></a>
					
					<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$karyawanid."','".$r->namafile."','".$r->namareward."');\" ></td>
					</tr>";
		}  
		echo $tab;
		

	break;
	
	case'deletefile':
		
			

		$query="delete from `".$dbname."`.`sdm_karyawanreward` where karyawanid='".$karyawanid."' and namareward='".$namareward."';
				delete from `".$dbname."`.`sdm_karyawandokumen` where karyawanid='".$karyawanid."' and namafile='".$namafile."' and tipedokumen='13'";


        try {
            $owlPDO->exec($query);
        } catch (PDOException $e) {
            print " Gagal, DB Error  3!: ".$e->getMessage()."<br/>";
            die();
        }

			$path = "fileupload/karyawan/".$namafile;
				unlink($path);
			
		
	break;
	
	case'clearreward':
		$_SESSION['efiltgh'] = array();
	break;
}

?>