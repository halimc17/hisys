<?

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');

/*$path = 'filepad';

$notransaksi = checkPostGet('notransaksi', '');
//$aksi = checkPostGet('aksi', '');
$spec = checkPostGet('spec', '');
$filename = checkPostGet('filename', '');
*/

$notransaksi=$_POST['notransaksi'];
$spec=isset($_POST['spec'])?$_POST['spec']:'';
$path='filepad';

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
    
    //$aksi=isset($aksi)?$aksi:'';
    $_POST['aksi']=isset($_POST['aksi'])?$_POST['aksi']:'';
    if ($_POST['aksi']=='del') {//exit("error:MASUK");
        //$str = "delete from " . $dbname . ".pad_photo where idlahan='" . $notransaksi . "' and filename='" . $filename . "'";
       $str="delete from ".$dbname.".pad_photo where idlahan='".$_POST['notransaksi']."' and filename='".$_POST['filename']."'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        if (is_file($path . "/" . $_POST['filename'])) {
            @unlink($path . "/" . $_POST['filename']);
        }
    } else {//exit("error:MASUKx");
        $lokasi = Array();
        $dir = $path;
       
        for ($x = 0; $x < count($_FILES['file']['name']); $x++) {
            $path = $dir . "/" . basename($_FILES['file']['name'][$x]);
            if ($path != 'photoqc/')
                $lokasi[$x] = $path;
            else
                $lokasi[$x] = '';

            $size = $_FILES['file']['size'][$x];
            $max = 75000;
            if ($size > $max) { 
                echo"Error : file size beyond limit (75kb)";
                $lokasi[$x] = '';
                exit(0);
            }
            $ext = explode('[.]', basename($_FILES['file']['name'][$x]));
            $ext = $ext[count($ext) - 1];
            $ext = strtolower($ext);
            if ($ext != 'exe' and $ext != 'js' and $ext != 'php' and $ext != 'perl' and $ext != 'vbs' and $ext != 'bat' and $ext != 'com' and $ext != 'jar') {
                try {
                    if (basename($_FILES['file']['name'][$x]) == '') {
                        
                    } else {
                        if (move_uploaded_file($_FILES['file']['tmp_name'][$x], $path)) {
                            $str = "delete from " . $dbname . ".pad_photo where idlahan='" . $_POST['notransaksi'] . "' and filename='" . basename($_FILES['file']['name'][$x]) . "'";


                            try {
                                $owlPDO->exec($str);
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }

                            $str = "insert into " . $dbname . ".pad_photo(idlahan,filename,filetype,filesize)
                                      values('" . $_POST['notransaksi'] . "','" . basename($_FILES['file']['name'][$x]) . "','" . basename($_FILES['file']['type'][$x]) . "'," . basename($_FILES['file']['size'][$x]) . ")";
                            
                            $err = '';

                            try {
                                $owlPDO->exec($str);
                                $err = 'Uploaded';
                            } catch (PDOException $e) {
                                print " Gagal  !: " . $e->getMessage() . "\n";
                                die();
                            }

                        }
                    }
                } catch (Exception $e) {
                    echo "Error:" . $e;
                    exit();
                }
            } else {
                echo "<script>alert('Filetype not support:" . $ext . " or too large');history.go(-1)</script>";
                exit();
            }
        }
        echo "<script>alert('Done');window.location='pad_uploadPhoto.php?notransaksi=" . $_POST['notransaksi'] . "';</script>";
    }
}

?>