<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method = checkPostGet('method','');
$doc = checkPostGet('doc','');
$jenisvhc=$_POST['jenisvhc'];
$namajenisvhc=$_POST['namajenisvhc'];
$noakun=$_POST['noakun'];
$kelompok=$_POST['kelompok'];
$path	= "fileupload/jenis_vhc/";
switch($method)
{
case 'submitfile':
	
	$tgl = date("YmdHis");
	$data = $_POST;
		if($_FILES['file']['error']==0)
		{
			$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
			$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
			$filename 	= $data['kvhc']."-".$data['jvhc'].$filetype;
			//$filename = $newfilename."_".$tgl."".$filetype;
			//$file_tmpname = $_FILES['file']['tmp_name'];	
			$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);	

			if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx'))
			{
				if($_FILES['file']['size'] <= 250000)
				{
					$str = "update ".$dbname.".vhc_5jenisvhc set file='".$filename."' where jenisvhc='".$data['jvhc']."' and kelompokvhc = '".$data['kvhc']."'";
					try
					{
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename,$file_tmpname);
						//move_uploaded_file($file_tmpname,$path.$filename);
					}
					catch(PDOException $e)
					{
						echo " Gagal," . addslashes($e->getMessage());
					}
				}
				else
				{
					exit("warning : Ukuran file upload maksimal 250kb");
				}
			}else{
				exit("Warning : Format file upload harus .jpg atau .jpeg");
			}
		}
	
break;
case'isifile':
	$potong=explode('.',$doc);
	if($potong[1]=='pdf')
	{
		echo"<embed src=".$doc." width=780px height=370px>";
	}
	else
	{
		echo"<img src=".$doc.">";
	}
break;

case 'update':	
	$str="update ".$dbname.".vhc_5jenisvhc set namajenisvhc='".$namajenisvhc."'
	      ,noakun='".$noakun."'
		  ,kelompokvhc='".$kelompok."'
		  ,updateby='" . $_SESSION['standard']['userid'] . "'
		   where jenisvhc='".$jenisvhc."'";
    try{$owlPDO->exec($str); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}
	break;
case 'insert':
	$str="insert into ".$dbname.".vhc_5jenisvhc(jenisvhc,namajenisvhc,noakun,kelompokvhc,createby,createtime)
	      values('".$jenisvhc."','".$namajenisvhc."','".$noakun."','".$kelompok."','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
    try{$owlPDO->exec($str); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}
	break;
case 'delete':
	$str="delete from ".$dbname.".vhc_5jenisvhc 
	where jenisvhc='".$jenisvhc."'";
    try{$owlPDO->exec($str); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}
	break;
					

case 'loaddata':	
	$str1="select * from ".$dbname.".vhc_5jenisvhc order by kelompokvhc asc, jenisvhc asc";
	$res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
	$res1->setFetchMode(PDO::FETCH_OBJ);
	while($bar1=$res1->fetch()){
			echo"<tr class=rowcontent>
		         <td align=center>".$bar1->kelompokvhc."</td>			     
				 <td align=center>".$bar1->jenisvhc."</td>
				 <td>".$bar1->namajenisvhc."</td>
				 <td align=center>".$bar1->noakun."</td>
				 <td align=center>".$bar1->file."</td>
				 <td align=center>".getNamaKaryawan($bar1->updateby)."</td>
				 <td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar1->jenisvhc."','".$bar1->namajenisvhc."','".$bar1->noakun."','".$bar1->kelompokvhc."');\">
				 <img src=images/zoom.png class=resicon onclick=\"isifile('".$path.$bar1->file."','event');\" title='view'>
				 </td></tr>";
	}	
break;
default:
   break;	
}
?>
