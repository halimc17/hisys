<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		

$method=checkPostGet('method','');


$pabrik=checkPostGet('pabrik','');
$tangki=checkPostGet('tangki','');
$barang=checkPostGet('barang','');
$tipe=checkPostGet('tipe','');
$tgl=tanggalsystemn(checkPostGet('tgl',''));
$jm=checkPostGet('jm','');
$mn=checkPostGet('mn','');
$jumlah=checkPostGet('jumlah','');
$ket=checkPostGet('ket','');
$noba=checkPostGet('noba','');
$sawal=checkPostGet('sawal','');
$jmlRey=checkPostGet('jmlRey','');

$brgSch=checkPostGet('brgSch','');
$tglSch=tanggalsystemn(checkPostGet('tglSch',''));


$tanggal=$tgl.' '.$jm.':'.$mn;


//exit("Error:$sInsert");	
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang','kelompokbarang=400');
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmTangki=makeOption($dbname,'pabrik_5tangki','kodetangki,keterangan');

$per['persetujuan1']=$_POST['persetujuan1'];
$per['persetujuan2']=$_POST['persetujuan2'];
$per['persetujuan3']=$_POST['persetujuan3'];
$jenispersetujuan='PKSCUCITANGKI';

if($tglSch=='--')
{
    $tglSch='';
}

?>

<?php

switch($method)
{//'".$_SESSION['standard']['userid']."'
    
   case'getTangki':
        $optTangki.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $iTangki="select kodetangki,keterangan from ".$dbname.".pabrik_5tangki where kodeorg='".$pabrik."' and komoditi in ('CPO','KER') ";
        $nTangki=$owlPDO->query($iTangki) or die(print " Gagal: ".PDOException::getMessage());
        $nTangki->setFetchMode(PDO::FETCH_ASSOC);
        while($dTangki=$nTangki->fetch())
        {
            $optTangki.="<option value=".$dTangki['kodetangki'].">".$dTangki['keterangan']."</option>";
        }
        echo $optTangki;
    break;
    
    case'getBarang':
        $arrKdBrg=array('CPO'=>'40000001','KER'=>'40000002');
        $arrNmBrg=array('CPO'=>'CRUDE PALM OIL (CPO)','KER'=>'PALM KERNEL (PK)');
        $iBarang="select komoditi from ".$dbname.".pabrik_5tangki where kodeorg='".$pabrik."' and kodetangki='".$tangki."'";
        $nBarang=$owlPDO->query($iBarang) or die(print " Gagal: ".PDOException::getMessage());
        $nBarang->setFetchMode(PDO::FETCH_ASSOC);
        while($dBarang=$nBarang->fetch())
        {
            $optBrg.="<option value=".$arrKdBrg[$dBarang['komoditi']].">".$arrNmBrg[$dBarang['komoditi']]."</option>";
        }
        echo $optBrg;
    break;
    
    case 'insert':
        
        $iSave="INSERT INTO ".$dbname.".`pabrik_pembersihantangki` (`kodeorg`, `kodetangki`, `kodebarang`, `tipe`, `tanggal`, 
                `jumlah`, `keterangan`, `updateby`, `sawal`, `recycle_jmlh`, `noba`)
        values ('".$pabrik."','".$tangki."','".$barang."','".$tipe."','".$tanggal."','".$jumlah."','".$ket."','".$_SESSION['standard']['userid']."','".$sawal."','".$jmlRey."','".$noba."')";
        try{
            $owlPDO->exec($iSave);

            for($i=1; $i<4; $i++){
                    if($per['persetujuan'.$i]!=''){
                        $str="insert into ".$dbname.".approval (`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`) values 
                          ('".$noba."','".$jenispersetujuan."','".$i."','".$per['persetujuan'.$i]."')";

                          // exit("error ".$str);

                        try{
                            $owlPDO->exec($str); 
                        }catch(PDOException $e){
                            echo " Gagal," . addslashes($e->getMessage());
                        }
                    }
                }

        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;

    case 'update':
        $iUpdate="update ".$dbname.".pabrik_pembersihantangki set jumlah='".$jumlah."'"
            . ",updateby='".$_SESSION['standard']['userid']."',keterangan='".$ket."' "
            . ",sawal='".$sawal."',tipe='".$tipe."',recycle_jmlh='".$jmlRey."',noba='".$noba."' "
            . " where kodeorg='".$pabrik."' and kodetangki='".$tangki."' and kodebarang='".$barang."' "
            . " and tanggal='".$tanggal."'";
        //exit("Error:$iUpdate");
        try{
            $owlPDO->exec($iUpdate);
        }
        catch (PDOException $e){
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;
    
    case 'delete':
        $iDelete="delete from ".$dbname.".pabrik_pembersihantangki where kodeorg='".$pabrik."' and kodetangki='".$tangki."' "
            . " and kodebarang='".$barang."' and tanggal='".$tanggal."'  ";
        try{
            $owlPDO->exec($iDelete);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;
        

    case'loadData':
            echo"<table class=sortable cellspacing=1 border=0>
                     <thead>
                                 <tr class=rowheader>
                                    <td align=center>No</td>
                                    <td align=center>".$_SESSION['lang']['pabrik']."</td>
                                    <td align=center>".$_SESSION['lang']['tangki']."</td> 
                                    <td align=center>".$_SESSION['lang']['namabarang']."</td>    
                                    <td align=center>".$_SESSION['lang']['tipe']."</td>    
                                    <td align=center>".$_SESSION['lang']['tanggal']."</td>
                                    <td align=center>".$_SESSION['lang']['noberitaacara']."</td>
                                    <td align=center>".$_SESSION['lang']['stockawal']."</td>
                                    <td align=center>".$_SESSION['lang']['recyclestock']."</td>
                                    <td align=center>".$_SESSION['lang']['wastestock']."</td>
                                    <td align=center>".$_SESSION['lang']['keterangan']."</td>
                                    <td align=center>".$_SESSION['lang']['action']."</td></tr>
                                 </tr>
                        </thead>
                        <tbody>";

                        $tmbh="";
            if($brgSch!='')
            {
                $tmbh.=" and kodebarang='".$brgSch."' ";
            }
            if($tglSch!='')
            {
                $tmbh.=" and tanggal like '%".$tglSch."%' ";
            }

            $limit=10;
            $page=0;
            if(isset($_POST['page']))
            {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
            }
            $offset=$page*$limit;
            $maxdisplay=($page*$limit);

            $ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_pembersihantangki where kodeorg='".$_SESSION['empl']['lokasitugas']."' ".$tmbh."  ";// echo $ql2;notran
            $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
            $query2->setFetchMode(PDO::FETCH_OBJ);
            while($jsl=$query2->fetch()){  
                $jlhbrs= $jsl->jmlhrow;
            }
            $no=$maxdisplay;
            $iList="select * from ".$dbname.".pabrik_pembersihantangki where kodeorg='".$_SESSION['empl']['lokasitugas']."' ".$tmbh." limit ".$offset.",".$limit."";
            //echo $iList;
            $nList=$owlPDO->query($iList) or die(print " Gagal: ".PDOException::getMessage());
            $nList->setFetchMode(PDO::FETCH_ASSOC);
            while($dList=$nList->fetch()){
                $no+=1;
                echo "<tr class=rowcontent>";
                echo "<td align=center>".$no."</td>";
                echo "<td align=left>".$nmOrg[$dList['kodeorg']]."</td>";
                echo "<td align=left>".$nmTangki[$dList['kodetangki']]."</td>";
                
                echo "<td align=left>".$nmBrg[$dList['kodebarang']]."</td>";
                echo "<td align=left>".$dList['tipe']."</td>";
                echo "<td align=left>".tanggalnormal($dList['tanggal'])." ".substr($dList['tanggal'],11,8)."</td>";
                echo "<td align=left>".$dList['noba']."</td>";
                echo "<td align=right>".number_format($dList['sawal'])."</td>";
                echo "<td align=right>".number_format($dList['recycle_jmlh'])."</td>";
                echo "<td align=right>".number_format($dList['jumlah'])."</td>";
                echo "<td align=left>".$dList['keterangan']."</td>";
                echo "<td align=center>
                        <img src=images/application/application_edit.png class=resicon  caption='Edit' 
                        onclick=\"fillField('".$dList['kodeorg']."','".$dList['kodetangki']."',
                        '".$nmTangki[$dList['kodetangki']]."','".$dList['kodebarang']."','".$nmBrg[$dList['kodebarang']]."','".$dList['tipe']."',
                        '".tanggalnormal(substr($dList['tanggal'],0,10))."','".substr($dList['tanggal'],11,2)."',
                        '".substr($dList['tanggal'],14,2)."','".$dList['jumlah']."','".$dList['keterangan']."','".$dList['noba']."','".$dList['sawal']."','".$dList['recycle_jmlh']."');\">
                        <img src=images/application/application_delete.png class=resicon  caption='Delete' 
                        onclick=\"del('".$dList['kodeorg']."','".$dList['kodetangki']."','".$dList['kodebarang']."',
                        '".tanggalnormal(substr($dList['tanggal'],0,10))."','".substr($dList['tanggal'],11,2)."',
                        '".substr($dList['tanggal'],14,2)."','".$dList['kodebarang']."');\">
                        </td>";
                echo "</tr>";//
            }
            echo"
            <tr class=rowheader><td colspan=11 align=center>
            ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
            <button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
    break;        
    case'getSounding':
        $sSound="select kuantitas,kernelquantity from ".$dbname.".pabrik_masukkeluartangki 
                 where tanggal='".$tgl."' and kodetangki='".$tangki."' and kodeorg='".$pabrik."'";
        $res=fetchData($sSound);

        if($res[0]['kuantitas']!=0){
            echo $res[0]['kuantitas'];
        }else{
            echo $res[0]['kernelquantity'];
        }
    break;
    

	
	
default:
}
?>