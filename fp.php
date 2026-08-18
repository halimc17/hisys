<?php

require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('phpqrcode/qrlib.php');

// $str="select * from ".$dbname.".log_5masterbarang where kelompokbarang > '334'";
$str="select * from ".$dbname.".log_5masterbarang";
$res=fetchdata($str);
foreach($res as $val){
        $kodebarang = $val['kodebarang'];
        $filename = "/images/qrcode/".$kodebarang.".png";
        if(file_exists($filename))
        {}
        else
        {
                $folder="images/qrcode/";
                $file_name=$kodebarang.".png";
                $file_name=$folder.$file_name;
                QRcode::png($kodebarang,$file_name);

                header("Content-type: image/png");
                $imgPath = $file_name;
                $image = imagecreatefrompng($imgPath);
                $color = imagecolorallocate($image, 0, 0, 0);
                $string = $kodebarang;
                $fontSize = 2;
                $x = 20;
                $y = 74;
                imagestring($image, $fontSize, $x, $y, $string, $color);

                imagepng($image,$file_name);
                imagedestroy($image);
        }
}


?>