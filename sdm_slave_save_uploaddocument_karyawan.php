<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
$method	= "";
$result = array();
$tipedoc=""; 
$karyawanid='';
$fileupload='';

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

function upload_file($pathlocation,$data,$rename){
	$result = "";
	$ext ="";
	if($data != ""){
		$fileTrans	=$data;
		if($fileTrans != ""){
			$path = $pathlocation;
			if (!file_exists($path)) {
				mkdir($path, 0777, true);
			}
			$newFile=$fileTrans;
			$file =explode(',',$newFile);
			
			$file =str_replace(' ', '+', $file[1]);
			$stream = base64_decode($file);
			$f = finfo_open();
			$mime_type = finfo_buffer($f, $stream, FILEINFO_MIME_TYPE);

			if ($mime_type=="image/jpeg" ){
				$ext = ".jpg";
			}elseif ($mime_type=="image/png" ){
				$ext = ".png";
			}elseif ($mime_type=="application/pdf" ){
				$ext = ".pdf";
			}
			$filename= $path.$rename.$ext;	
			file_put_contents($filename, $stream);			
		}
		$result = $rename.$ext;
	}
	return $result;
}
$str = array();
switch($method){

	case 'showupload':
		if($param['jenis']!='' and $param['jenis']!='undefined'){
			$where=" and id='".$param['jenis']."'";
		}
		$tab="";
		$cektipe="select * from ".$dbname.".sdm_5tipedokumen where isactive='1' ".$where."";
		$restipe = $owlPDO->query($cektipe) or die(print " Gagal: " . PDOException::getMessage());
		$restipe->setFetchMode(PDO::FETCH_OBJ);
		while($baru=$restipe->fetch()){
			@$optkriteria.="<option value='".$baru->id."'>".$baru->namatipe."</option>";
	    }	
		
		$tab.="<table cellspacing='1' border='0' id='uploadpopup'>
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
                        $newdata = array(
                            'idfiledt'=>$idfiledt,
                            'tipe'=>'1',
                            'location'=>'fileupload/karyawan/'.$filename,
                            'namafile'=>$filename,
                            'formaticon'=>$filetype,
                            'kriteriaefil'=>$kriteriaefil,
                            'size'=>$_FILES['file']['size']
                        );
                        
                        if($_SESSION['efiltgh'] != array())
                        {
                           /* foreach($_SESSION['efiltgh'] as $key=>$row)
                            {
                                if($row['namafile'] == $filename)
                                {
                                    exit("Warning : Item ini sudah pernah diinput sebelumnya.");
                                }
                            }
                            array_push($_SESSION['efiltgh'],$newdata);
                        }else{
                            array_push($_SESSION['efiltgh'],$newdata);
                        }*/
                    }
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
              `tipefile`,
              `updateby`
              )
              values('".$karyawanid."',
              '".$kriteriaefil."',
              '".$filename."',
              '".$filetype."',              
              '".$_SESSION['standard']['userid']."'
              )";
                try{
                    $owlPDO->exec($str);
                    // copy($val['location'], "filegis/".$val['namafile']);
                }catch (PDOException $e){print "DB Error  1!: ".$e->getMessage()."<br/>";die();}
            
            
            }
        }
    break;

    case 'loadfileskar':
      	$nmtipe=makeOption($dbname,'sdm_5tipedokumen','id,namatipe');
        $tab = "";
        $q="select * from ".$dbname.".sdm_karyawandokumen  where karyawanid = '".$karyawanid."'";
        $redy=$owlPDO->query($q) or die(print " Gagal: ".PDOException::getMessage());
        $redy->setFetchMode(PDO::FETCH_OBJ);
        $html = "";
        while($r=$redy->fetch()){
			$no+=1;
			$tab.="<tr class=rowcontent >
				<td style='text-align:center'>".$no."</td>
				<td style='text-align:center'>".$r->tipefile."</td>
				<td style='text-align:center'>".$nmtipe[$r->tipedokumen]."</td>
				<td style='text-align:center'>".$r->namafile."</td>
				<td style='text-align:center;width:25px'>
					<a href='fileupload/karyawan/$r->namafile' download><img src=images/uploader/dwnld8.png class=resicon  title='PNG'></a>
				</td>	
				<td style='text-align:center;width:25px'>
					<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$karyawanid."','".$r->tipedokumen."','".$r->namafile."');\" ></td>
				</tr>";
        }  
        echo $tab;
    break;

    case'deletefile':
        
            

        $query="delete from `".$dbname."`.`sdm_karyawandokumen` where karyawanid='".$karyawanid."' and tipedokumen='".$tipefile."' and namafile='".$namafile."'";


        try {
            $owlPDO->exec($query);
        } catch (PDOException $e) {
            print " Gagal, DB Error  3!: ".$e->getMessage()."<br/>";
            die();
        }

            $path = "fileupload/karyawan/".$namafile;
                unlink($path);
            
        
    break;
	case 'insert':

		
		$path = "./fileupload/karyawan/".$karyawanid."/";
		$rename = "document_".$tipedoc;
		$namefile = upload_file($path,$fileupload,$rename);
		$find = selectQuery($dbname,"sdm_karyawandokumen","karyawanid","karyawanid=".$karyawanid." and tipedokumen ='".$tipedoc."'");
		$sdm_karyawandokumen = fetchData($find);
		if(count($sdm_karyawandokumen) == 0){ 
			$str[] ="insert into ".$dbname.".sdm_karyawandokumen
			 (`karyawanid`,
			  `tipedokumen`,
			  `namafile`,
			  `updateby`
			  )
			  values(".$karyawanid.",
			  '".$tipedoc."',
			  '".$namefile."',
			  '".$_SESSION['standard']['userid']."'
			  )";
		}else{
			$str[] ="update ".$dbname.".sdm_karyawandokumen set
			 `namafile` ='$namefile',
			 `updateby` ='".$_SESSION['standard']['userid']."'
			 where karyawanid=".$karyawanid." and tipedokumen ='".$tipedoc."'";
		}
	
	if(count($str)>0){
		for($i=0; $i<count($str); $i++){
			try{
				$owlPDO->exec($str[$i]);
				$result['wrong'] = "false"; 
				$result['message'] = ""; 
				$result['karyawanid'] = $karyawanid; 
				$result['namafile'] = $namefile; 
			}catch(PDOException $e){
				$result['wrong'] = "true"; 
				$result['message'] = "Gagal  !: " . $e->getMessage();
				$result['karyawanid'] = ''; 
				$result['namafile'] = ''; 
			}
		}
	}
	break;
	case 'loaddata';
		$find = selectQuery($dbname,"sdm_karyawandokumen","*","karyawanid=".$karyawanid."");
		$data['listdoc'] = fetchData($find);
		$find = selectQuery($dbname,"sdm_5tipedokumen","*","untuk = 'KARYAWAN' and isactive = '1'");
		$data['listtipe'] = fetchData($find);
		$result = $data;
	break;
}

if(!empty($result)){	
	echo json_encode($result);
}
?>