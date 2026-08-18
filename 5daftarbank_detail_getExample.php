<?php
require_once('master_validation.php');

        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=expamplertgs.csv");
        echo "clearing code,rtgs code,bank code,bank name,branch name,city\n";
        echo "1539923,SYTBIDJ1,10008,PT BANK RAKYAT INDONESIA,KANTOR PUSAT,DKI JAKARTA\n";
        exit();
