<?
require_once('master_validation.php');
require_once('config/connection.php');

	//return kelompok supplier list
	$tipe		=$_POST['tipe'];
	$str="select kode,kelompok from ".$dbname.".log_5klsupplier where tipe='".$tipe."'";
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_OBJ);
	$opt="<option value=''></option>";
	while($bar=$res->fetch())
	{
		$opt.="<option value='".$bar->kode."'>".$bar->kelompok."</option>";
	}
	echo $opt;


	if (isset($_POST['kelompok'])) {
    $kdkelompok = trim($_POST['kelompok']);
    if ($kdkelompok == '') {
        echo"";
    } else {
        $str = "select max(supplierid) as id from " . $dbname . ".log_5supplier where kodekelompok='" . $kdkelompok . "'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $newkode = $bar->id;
        }
        //remove group code at the begenning	
        $newkode = substr($newkode, 6, 4);
        //get year code 2 digit
        $mid = date('y');
        //create increment for new number		
        $newkode = intval($newkode) + 1;
        switch ($newkode) {
            //create 4 digit code from new number
            case $newkode < 10:
                $newkode = '000' . $newkode;
                break;
            case $newkode < 100:
                $newkode = '00' . $newkode;
                break;
            case $newkode < 1000:
                $newkode = '0' . $newkode;
                break;
            default:
                $newkode = $newkode;
                break;
        }
        $newkode = $kdkelompok . $mid . $newkode;
        echo $newkode;
    }
} else {
    $tipe = $_POST['tipe'];
    $pt = $_POST['pt'];
    $str1 = "select max(kode) as kode from " . $dbname . ".log_5klsupplier where tipe='" . $tipe . "'";
    $res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
    $res1->setFetchMode(PDO::FETCH_OBJ);
    while ($bar1 = $res1->fetch()) {
        $kode = $bar1->kode;
    }
    $kode = substr($kode, 1, 5);
    $newkode = $kode + 1;
    switch ($newkode) {
        case $newkode < 10:
            $newkode = '00' . $newkode;
            break;
        case $newkode < 100:
            $newkode = '0' . $newkode;
            break;
        default:
            $newkode = $newkode;
            break;
    }
	
	if ($tipe == 'RAMP')
	{
		$str = "select count(kode) as kode from " . $dbname . ".log_5klsupplier where kode like '%".$pt."%'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$kode = $bar->kode;
		}
		$newkode = $kode + 1;
	}
	
	
    if ($tipe == 'SUPPLIER')
        $newkode = 'S' . $newkode;
    else if ($tipe == 'KONTRAKTOR')
        $newkode = 'K' . $newkode;
	else if ($tipe == 'RAMP')
        $newkode = 'R'.$pt.$newkode;
    else
        $newkode = 'T' . $newkode;
	
	
    echo $newkode;
}
?>