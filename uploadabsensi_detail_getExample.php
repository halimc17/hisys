<?php
require_once('master_validation.php');

        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=expampluploadabsensi.csv");
        echo "tanggal(YYYY-mm-dd),nik,jam datang,jam pulang\n";
        echo '=CONCATENATE("2018-03-10"),=CONCATENATE("9999999"),=CONCATENATE("08:30"),=CONCATENATE("16:25")'."\n";
        exit();
