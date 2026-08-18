<?php
require_once('master_validation.php');
$param=$_GET['form'];
if($param=='SDM'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=examplesdm.csv");
        echo "tahunbudget,kodeblok,tipebudget,kodebudget,kodekegiatan,volumepekerjaansetahun,rupiahsetahun,rotasi,jumlahhk,satuan\n";
        echo "2014,SNBE01A000,ESTATE,SDM-BHL,621010201,55.74,2321882.64,3,27.23616,HK\n";
        echo "2014,SNBE01A000,ESTATE,SDM-KHT,611020201,161.1225,1958580,1,14.625,HK\n";
        echo "2014,SNBE01A000,ESTATE,SDM-PRE,611010101,11935.2,24952049.89,48,197.604,HK\n";
        echo "2014,SNBE02B000,ESTATE,SDM-BHL,621010201,50.4,2100750.96,3,24.64224,HK\n";
        echo "2014,SNBE03C000,ESTATE,SDM-KHT,611010101,14910.24,30873648.96,48,230.538,HK\n";
        exit();
}
if($param=='MATANDTOOL'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=examplematandtool.csv");
        echo "tahunbudget,kodeblok,tipebudget,kodebudget,kodekegiatan,volumepekerjaansetahun,rupiahsetahun,rotasi,kodebarang,jumlahbrg,satuanbrg\n";
        echo "2014,SNBE01A000,ESTATE,M-311,621030205,37.16,27348288.66,2,31100001,5370.75,KG\n";
        echo "2014,SNBE01A000,ESTATE,M-312,621010201,55.74,898159.636,3,31200028,20.6611458,LTR\n";
        echo "2014,SNBE01A000,ESTATE,M-377,621060101,2387,8649.6,1,37700867,8,PCS\n";
        echo "2014,SNBE02B000,ESTATE,M-351,621010402,6.72,1382617.354,1,35100001,127.68,LTR\n";
        echo "2014,SNBE03C000,ESTATE,M-351,621040301,24.24,24069.0054,2,35100002,2.42,LTR\n";
        exit();        
}
if($param=='VHC'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=examplevhc.csv");
            echo "tahunbudget,kodeblok,tipebudget,kodebudget,kodekegiatan,volumepekerjaansetahun,rupiahsetahun,kodevhc,jumlahhmkmpertahun,satuan\n";
            echo "2014,SNBE01A000,ESTATE,VHC,621090201,1680,91961780.44,BG8796F,17815,HM/KM\n";
            echo "2014,SNBE01A000,ESTATE,VHC,621090201,1680,1838094.593,BL-02,13.743,HM/KM\n";
            echo "2014,SNBE01A000,ESTATE,VHC,621090201,1680,2165015.905,RG-03,8.653,HM/KM\n";
            echo "2014,SNBE02B000,ESTATE,VHC,621090201,1420,43006196.94,BG8795F,8907.5,HM/KM\n";
            echo "2014,SNBE03C000,ESTATE,VHC,621090601,2,316519.1169,VR-03,1.8935,HM/KM\n";
            exit();        
}       
if($param=='KONTRAK'){ 
        header("Cache-Control: must-revalidate");
        header("Pragma: must-revalidate");
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: attachment; filename=examplekontrak.csv");
            echo "tahunbudget,kodeblok,tipebudget,kodebudget,kodekegiatan,volumepekerjaansetahun,rupiahsetahun\n";
            echo "2014,SNBE01A000,ESTATE,KONTRAK,611020201,161.1225,4750060.125\n";
            echo "2014,SNBE01A000,ESTATE,KONTRAK,621080303,500,386250\n";
            echo "2014,SNBE01A000,ESTATE,KONTRAK,621090101,1680,67200\n";
            echo "2014,SNBE02B000,ESTATE,KONTRAK,611020101,345.465,24873480\n";
            echo "2014,SNBE03C000,ESTATE,KONTRAK,621090101,2864,114560\n";
            exit();        
}  
?>