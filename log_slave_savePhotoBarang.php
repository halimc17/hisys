<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/zLib.php');

$kodebarang = $_POST['kodebarangx'];
$spec = $_POST['spec'];
$path = 'photobarang';

if (is_dir($path)) {
    writeFile($path, $owlPDO, $dbname);
} else {
    if (mkdir($path, 0777)) {
        writeFile($path, $owlPDO, $dbname);
    } else {
        echo " Gagal, Can't create folder for uploaded file";
        exit(0);
    }
}

function writeFile($path, $owlPDO, $dbname) {
    $lokasi = Array();
    $dir = $path;
    $countError = 0;
    for ($x = 0; $x < count($_FILES['file']['name']); $x++) {
		$tmp = explode('.', $_FILES['file']['name'][$x]);
        $extension = "." . end($tmp);
        $path = $dir . "/".$_POST['kodebarangx']."_".$x."_".basename($_FILES['file']['name'][$x]);

        if ($path != "photobarang/".$_POST['kodebarangx']."_".$x."_") {
            $lokasi[$x] = $path;
        } else {
            $lokasi[$x] = '';
        }

        $size = $_FILES['file']['size'][$x];
        $max = 500000;

        // echo $extension."<br>";
        // echo $_FILES['file']['error'][$x]."<p>";

        if ($_FILES['file']['name'][$x] != '') {
            if (strtolower($extension) == '.jpg' || strtolower($extension) == '.jpeg' || strtolower($extension) == '.png') {
                if ($_FILES['file']['error'][$x] == 2) {
                    $countError += 1;
                } else {
                    move_uploaded_file($_FILES['file']['tmp_name'][$x], $path);
                }
            } else {
                $countError += 1;
            }
        }
    }

    if ($_POST['spec'] == '') {
        $str = "delete from " . $dbname . ".log_5photobarang where kodebarang='" . $_POST['kodebarangx'] . "'";
		try{
			$owlPDO->exec($str);
			echo "Detail item has been deleted";
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die();
		}
    } else {
		$str="select * from ".$dbname.".log_5photobarang where kodebarang='".$_POST['kodebarangx']."'";
		$res=fetchdata($str);
		$hasil = count($res);
		
		if($countError > 0){
            echo '<font color=red>Error : <br>1. file size beyond limit (500kb) , or<br>2. file extension must .jpg(.jpeg) or .png</font>';
        }else{
			if($hasil > 0){
				if($lokasi[0]!=''){
					$str="update ".$dbname.".log_5photobarang set spesifikasi='".$_POST['spec']."', depan='".$lokasi[0]."' where kodebarang='".$_POST['kodebarangx']."'";
					$owlPDO->exec($str); 
				}
				if($lokasi[1]!=''){
					$str="update ".$dbname.".log_5photobarang set spesifikasi='".$_POST['spec']."', samping='".$lokasi[1]."' where kodebarang='".$_POST['kodebarangx']."'";
					$owlPDO->exec($str); 
				}
				if($lokasi[2]!=''){
					$str="update ".$dbname.".log_5photobarang set spesifikasi='".$_POST['spec']."', atas='".$lokasi[2]."' where kodebarang='".$_POST['kodebarangx']."'";
					$owlPDO->exec($str); 
				}
				
				$str="update ".$dbname.".log_5photobarang set spesifikasi='".$_POST['spec']."' where kodebarang='".$_POST['kodebarangx']."'";
				$owlPDO->exec($str);
				
				echo 'Detail item has been saved';
			}else{
				$str = "insert into " . $dbname . ".log_5photobarang(kodebarang,depan,samping,atas,spesifikasi)
					values('" . $_POST['kodebarangx'] . "','" . $lokasi[0] . "','" . $lokasi[1] . "','" . $lokasi[2] . "','" . $_POST['spec'] . "')";
				try{
					$owlPDO->exec($str); 
					echo 'Detail item has been saved';
				}catch(PDOException $e){
					
				}
			}
		}
		
        // if ($countError > 0) {
            // echo '<font color=red>Error : <br>1. file size beyond limit (500kb) , or<br>2. file extension must .jpg(.jpeg) or .png</font>';
        // } else {
            // $str = "delete from " . $dbname . ".log_5photobarang where kodebarang='" . $_POST['kodebarangx'] . "'";
            // try{
				// $owlPDO->exec($str); 
			// }catch(PDOException $e){
				
			// }
			
			// $str = "insert into " . $dbname . ".log_5photobarang(kodebarang,depan,samping,atas,spesifikasi)
				 // values('" . $_POST['kodebarangx'] . "','" . $lokasi[0] . "','" . $lokasi[1] . "','" . $lokasi[2] . "','" . $_POST['spec'] . "')";
            // try{
				// $owlPDO->exec($str); 
				// echo 'Detail item has been saved';
			// }catch(PDOException $e){
				
			// }
		// }
    }
}
?>