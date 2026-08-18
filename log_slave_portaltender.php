<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/paging.php');

$method = checkPostGet('method','');
$supplierid = checkPostGet('supplierid','');

switch ($method) {
	case 'portaltender':
		
		$str="select * from ".$dbname.".log_5supuser where id_supplier='".$supplierid."'";
		$res=fetchdata($str);
		$email = $res[0]['email'];
		if($email==''){
			exit("Warning, Email supplier belum terdaftar");
		}
		
		$str="update ".$dbname.".list_notification set readnotif='1' where kodetransaksi='".$supplierid."' and karyawanid='".$_SESSION['standard']['userid']."'";
		$owlPDO->exec($str);
	
	    $tab="
	        <legend>".$_SESSION['lang']['list']."</legend>
	        <table class='sortable' cellspacing='1' cellpadding=5 border='0' width=100%>
	            <thead>
	            <tr class=rowheader>
	                <td align='center'>No.</td>
	                <td align='center'>".$_SESSION['lang']['notransaksi']."</td>
	                <td align='center'>".$_SESSION['lang']['tanggal']."</td>
	                <td align='center'>".$_SESSION['lang']['dari']."</td>
	                <td align='center'>".$_SESSION['lang']['subject']."</td>
	                <td align='center'>".$_SESSION['lang']['deskripsi']."</td>
	                <td align='center'>Filename</td>
	            </tr>
	            </thead>
	            <tbody id='listfiles'>
	            </tbody>
	        </table><br><hr><br>";

	    $tab.="<table cellspacing='1' border='0' id='uploadpopup'>
	        <tr>
	            <td>".$_SESSION['lang']['subject']."</td>
		        <td>:</td>
		        <td><input type=text id=subject class=myinputtext style=width:200px; ></td>
	        </tr>
	        <tr>
		        <td>".$_SESSION['lang']['deskripsi']."</td>
		        <td>:</td>
		        <td><textarea class=myinputtext id=deskripsi style='width:650px;min-height:150px;' onkeypress=\"return tanpa_kutip(event);\"/></textarea></td>
	        </tr>
	        <tr>
	            <td>Filename</td>
	            <td>:</td>
	            <td>
	                <input type='file' name='upload' id='upload' class=mybutton> Ukuran file upload maksimal 250 kb
	            </td>
	        </tr>
	        <tr>
	            <td colspan=2></td>
	            <td>
	                <input type='hidden' id='notransaksi' value=''>
	                <input type='hidden' id='supplierupload' value='".$supplierid."'>
	                <button class=mybutton onclick=\"submitfiletender()\">Submit</button>
	            </td>
	        </tr>
	    </table>
	    <p />";
	    
	    echo $tab;
	break;

	case 'submitfiletender':
		try{
			$owlPDO->beginTransaction();
			$tgl = date("YmdHis");
			$data = $_POST;

			$notransaksi="I".$tgl;
			
			$str1 = "insert into ".$dbname.".portal_tender values ('".$notransaksi."','I','".date('Y-m-d H:i:s')."','".$data['supplierid']."','".$data['subject']."','".$data['deskripsi']."','0')";
			$owlPDO->exec($str1);
			
			if($data['fileupload']!=''){
				if($_FILES['file']['error']==0){
					$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
					$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
					$filename = $newfilename."_".$tgl."".$filetype;
					$file_tmpname = $_FILES['file']['tmp_name'];        
					
					if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')||($filetype=='.rar')){
						if($_FILES['file']['size'] <= 250000){
							$str = "insert into ".$dbname.".portal_tender_file values ('".$notransaksi."','".$data['supplierid']."','".$filename."','".$filetype."')";
							$owlPDO->exec($str);
							move_uploaded_file($file_tmpname,"fileupload/portaltender/$filename");
						}else{
							throw new PDOException("Ukuran file upload maksimal 250 kb");
						}
					}else{
						throw new PDOException("Format file upload harus jpg, jpeg, png, pdf, xls, xlsx, doc, docx, rar");
					}
				}
			}
			
			$str="update ".$dbname.".log_5supuser set sessionid='portalsupplier',date_reg='".date('Y-m-d')."' where id_supplier='".$data['supplierid']."'";
			$owlPDO->exec($str);
			
			kirimemailportal($data['supplierid']);
			$owlPDO->commit();
		}catch (PDOException $e){
			$owlPDO->rollback();
			echo $e."\n";
			exit("Warning, Ada kesalahan silahkan hubungi pihak STH GROUP");
		}
	break;

	case 'loadfilestender':
	    $no = 0;
	    $tab = "";
	    $str="select * from ".$dbname.".portal_tender where supplierid = '".$supplierid."'";
	    $resv=fetchData($str);
	    foreach($resv as $bar => $barv){
	        $close = $barv['close'];    
	    }
	    
	    $str="select a.*,b.*,a.notransaksi as notransaksitender from ".$dbname.".portal_tender a left join ".$dbname.".portal_tender_file b on a.notransaksi=b.notransaksi and a.supplierid=b.supplierid where a.supplierid = '".$supplierid."' order by tanggal desc";
	    $res=fetchData($str);
	    if(empty($res))
	    {
	        $tab.="<tr class=rowcontent><td colspan=8 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	    }
	    else
	    {
	        foreach($res as $key=>$val)
	        {
				$optsup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$supplierid."'");
	            $no++;
	            $tab.="<tr id='ppDetailTable' class=rowcontent>
	                <td style='text-align:center;vertical-align:top'>".$no."</td>
	                <td style='text-align:center;vertical-align:top'>".$val['notransaksitender']."</td>
	                <td style='text-align:center;min-width:80px;vertical-align:top'>".tanggalnormal($val['tanggal'])."</td>
	                <td style='text-align:left;vertical-align:top'>".($val['tipe']=='E'?$optsup[$supplierid]:'STH GROUP')."</td>
	                <td style='text-align:center;vertical-align:top'>".$val['subject']."</td>
	                <td style='text-align:justify;vertical-align:top'>".$val['deskripsi']."</td>
					<td style='vertical-align:top'>";
					
					$strx="select * from ".$dbname.".portal_tender_file where notransaksi='".$val['notransaksitender']."' and supplierid='".$val['supplierid']."'";
					$resx=fetchdata($strx);
					if(count($resx) > 0){
						$nox=0;
						foreach($resx as $valx){
							$nox++;
							if($nox=='1'){
								$tab.=$nox.". <a href='../fileupload/portalsupplier/".$valx['namafile']."' download>".$valx['namafile']."</a>";
							}else{
								$tab.="<br>".$nox.". <a href='../fileupload/portalsupplier/".$valx['namafile']."' download>".$valx['namafile']."</a>";
							}
						}
					}
	                $tab."</td>
	            </tr>";
	        }   
	    }
	    echo $tab;
	break;
	
	default:
		# code...
	break;
}

function kirimemailportal($idsupplier){
	global $dbname;
	global $owlPDO;
	
	$newpas = rand_passwd(4);
	$exp 	= urldecode(base64_encode(date("Ymd")));
	$url 	=  site_url()."/".segment(1);
	$qstr = "select * from " . $dbname . ".log_5supuser where id_supplier = '".$idsupplier."' limit 1";
	$r = fetchData($qstr);
	if(count($r) >0){
	$log_5supuser = "UPDATE " . $dbname . ".log_5supuser set password = PASSWORD('" . $newpas . "'), sessionid = 'portalsupplier' where id_supplier = '".$idsupplier."' limit 1";
		try{
			$owlPDO->exec($log_5supuser); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}
		$subject	=	"Aplikasi Portal Supplier";
		$from		=	"STH Grup";
		$to			=	trim($r[0]['email']);
		$link 		=   $url."/supplier/?log=".$exp;
		$content	= 	"<table>";
		$content	.= "<tr><td>Data Supplier</td></tr>";
		$content	.= "<tr><td>Url </td><td>: $link</td></tr>";
		$content	.= "<tr><td>Email  </td><td>: $to</td></tr>";
		$content	.= "<tr><td>Password  </td><td>: $newpas</td></tr>";
		$content	.= "<tr><td>Email ini dikirim untuk membuka halaman Portal Supplier.</td></tr>";
		$content	.= "</table>";
		echo $content;
	// kirimEmailkeSupplier($to,$cc = "",$subject,$content,$mailType='text/html');
	}
}

function site_url(){
  return sprintf(
    "%s://%s%s",isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',$_SERVER['SERVER_NAME'],''
  );
}
function segment($num){
	$result = "";
	$list = explode("/",$_SERVER['REQUEST_URI']);
	if(isset($list[$num])){
		$result = $list[$num];
	}
  return $result; 
}
function rand_passwd( $length = 8, $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789' ) {
    return substr( str_shuffle( $chars ), 0, $length );
}
function  kirimEmailkeSupplier($to,$cc = "",$subject,$body,$mailType='text/html')//multiple recipient separated by comma
{
    global $owlPDO;
    global $dbname;
    #default
    $port=25;
    $ssl='YES';
    $str=$owlPDO->query("select * from ".$dbname.".setup_remotetimbangan where lokasi='MAILSYS'");
    $str->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$str->fetch()){
        $host=trim($bar->ip);
        $username=trim($bar->username);
        $password=trim($bar->password);
        $port=trim($bar->port);
        $ssl=strtoupper(trim($bar->dbname));
    }

    if($ssl=='YES' or $ssl=='TRUE' or strtoupper($ssl)=='SSL')
    {
        $host="ssl://".$host;
    }
    #mailType posible value 'text/html' or 'text/text'
    
     require_once "Mail.php";   
     $from = "Owl-Plantation<noreply@owl-plantation.com>";
     $headers = array ('From' => $from,
       'To' => $to,
       'Cc' => $cc,  
       'Subject' => $subject,
       'Content-Type'=> $mailType);
     $mail = Mail::factory('smtp',
       array ('host' => $host,
         'auth' => true,
         'port' => $port,
         'username' => $username,
         'password' => $password));     
		 
     if($mailType=='text/html')
     {
         $body.="";
     }    
	 $toto=explode(",",$to);
	 foreach($toto as $key =>$val){
           $kirim = $mail->send($val, $headers, $body);
       }
     if (PEAR::isError($kirim)) {
       return $kirim->getMessage();
     	//return true;
      } else {
       return true;
      }
     return true;
}

?>