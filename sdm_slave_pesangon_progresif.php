<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');


$tot_sblm_pajak =$_POST['totsblmpajak'];
$karyawanid     =$_POST['karyawanid'];
//exit("error: ".$tot_sblm_pajak);
$skodept="select * from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
$rkodept=$owlPDO->query($skodept) or die(print " Gagal: ".PDOException::getMessage());
$rkodept->setFetchMode(PDO::FETCH_OBJ);
$bkodept=$rkodept->fetch();

$penghasilan=Array();
$persentase=Array();
$urut=0;
$spajak="select * from ".$dbname.".sdm_5pajakpesangon where kodept='".$bkodept->kodeorganisasi."' 
         order by penghasilan asc";
$rpajak=$owlPDO->query($spajak) or die(print " Gagal: ".PDOException::getMessage());
$rpajak->setFetchMode(PDO::FETCH_OBJ);
while($row=$rpajak->fetch()){
    $penghasilan[$urut]=$row->penghasilan;
    $persentase[$urut]=$row->persentase;
    

    $urut+=1;
}

$pajak=$totpajak1=$totpajak2=$totpajak3=0;
$sisa=$tot_sblm_pajak;
 
if($tot_sblm_pajak>0){
//Progresif I    
    if($tot_sblm_pajak<$penghasilan[0]){
        $pajak1=$penghasilan[0]*$persentase[0];
        $totpajak1+=$pajak1;
        $sisa=0;
    }
    else if($tot_sblm_pajak>=$penghasilan[0]){  
        $pajak1=$penghasilan[0]*$persentase[0];
        $totpajak1+=$pajak1;
        $sisa-=$penghasilan[0];
        //Progresif II
        if($sisa<$penghasilan[1]){
            $pajak2=$sisa*$persentase[1];    
            $totpajak2+=$pajak2;
            $sisa=0;            
        }
        else if($sisa>=$penghasilan[1]){    
            $pajak2=$penghasilan[1]*$persentase[1];
            $totpajak2+=$pajak2;
            $sisa-=$penghasilan[1];
            //Progresif III
            if($sisa<$penghasilan[2]){
                $pajak3=$sisa*$persentase[2];
                $totpajak3+=$pajak3;
                $sisa=0;
//                exit("error: ".$totpajak);
            }
            else if($sisa>=$penghasilan[2]){
                $pajak3=$penghasilan[2]*$persentase[2];
                $totpajak3+=$pajak3;
                $sisa-=$penghasilan[2];
                //Progresif IV
//                if($sisa<$penghasilan[3]){
//                    $pajak4=$sisa*$persentase[3];
//                    $totpajak4+=$pajak4;
//                    $sisa=0;
//                }
//                else if($sisa>=$penghasilan[3]){
//                    $pajak4=$penghasilan[3]*$persentase[3];
//                    $totpajak4+=$pajak4;
//                    $sisa-=$penghasilan[3];
//
//                } 
            }   
        }   
    }
}
$totpajak=$totpajak1+$totpajak2+$totpajak3;
//exit("error: ".$totpajak);
$totpesangon=$tot_sblm_pajak-$totpajak;
echo number_format(@$pajak1,2)."###".number_format(@$pajak2,2)."###".number_format(@$pajak3,2)."###".number_format(@$totpajak,2)."###".number_format(@$totpesangon,2);
?>
