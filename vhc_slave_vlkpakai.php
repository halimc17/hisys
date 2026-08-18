<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		

$method=checkPostGet('method','');
$notran=checkPostGet('notran','');
$kdvhc=checkPostGet('kdvhc','');
$kdbrg=checkPostGet('kdbrg','');
$tgl=tanggalsystemn(checkPostGet('tgl',''));
$tipe=checkPostGet('tipe','');

$kmhm=checkPostGet('kmhm','');
$tekangin=checkPostGet('tekangin','');
$posroda=checkPostGet('posroda','');
$ket=checkPostGet('ket','');
$arrtipe=array('1'=>'Pemasangan','2'=>'Pelepasan');

$brgSch=checkPostGet('brgSch','');
$tglSch=tanggalsystemn(checkPostGet('tglSch',''));

//exit("Error:$method");	
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmTangki=makeOption($dbname,'pabrik_5tangki','kodetangki,keterangan');


$notransch=checkPostGet('notransch','');
$kdvhcsch=checkPostGet('kdvhcsch','');
$kdbrgsch=checkPostGet('kdbrgsch','');
$tglsch=tanggalsystemn(checkPostGet('tglsch',''));
$tipesch=checkPostGet('tipesch','');

if($tglsch=='--'){
    $tglsch='';
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
    
   
    
    case 'insert':
		
		$date=date('Ym');
			$str="select notransaksi from ".$dbname.".vhc_tyre where kodeorg='".$_SESSION['empl']['lokasitugas']."' and notransaksi like '".$date."%' order by notransaksi desc limit 1 ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            $bar=$res->fetch();
		@$awal=substr($bar['notransaksi'],7,3);
		@$awal=intval($awal);
		@$cekbln=substr($bar['notransaksi'],0,6);
		
			if($date!=$cekbln){
				$awal=1;
			}else{
				$awal++;
			}
		$counter=addZero($awal,3);
		$notran=$date.$counter;
		
		
        $str="INSERT INTO ".$dbname.".`vhc_tyre` 
				(`notransaksi`, `tipetransaksi`, `kodevhc`, `posisiroda`, 
                `tanggal`, `kodebarang`, `kmhm`, `tekanan`,
				`kodeorg`, `keterangan`,`updateby`)
        values ('".$notran."','".$tipe."','".$kdvhc."','".$posroda."',
				'".$tgl."','".$kdbrg."','".$kmhm."','".$tekangin."',
				'".$_SESSION['empl']['lokasitugas']."','".$ket."','".$_SESSION['standard']['userid']."')";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;

    case 'update':
        $str="update ".$dbname.".vhc_tyre set 
				kodevhc='".$kdvhc."',posisiroda='".$posroda."',
				tanggal='".$tgl."',kodebarang='".$kdbrg."',kmhm='".$kmhm."',tekanan='".$tekangin."',
				kodeorg='".$_SESSION['empl']['lokasitugas']."',keterangan='".$ket."',updateby='".$_SESSION['standard']['userid']."',tipetransaksi='".$tipe."'				
				where notransaksi='".$notran."' ";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e){
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;
    
    case 'delete':
        $str="delete from ".$dbname.".vhc_tyre where notransaksi='".$notran."' ";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;
        

    case'loadData':
            echo"<div id=container>
                    <table class=sortable cellspacing=1 border=0>
                     <thead>
                                 <tr class=rowheader>
                                    <td align=center>No</td>
                                    <td align=center>".$_SESSION['lang']['notransaksi']."</td>
									<td align=center>".$_SESSION['lang']['tipetransaksi']."</td>
									<td align=center>".$_SESSION['lang']['kodevhc']."</td> 
                                    <td align=center>".$_SESSION['lang']['namabarang']."</td>    
                                    <td align=center>".$_SESSION['lang']['tanggal']."</td>
                                    <td align=center>".$_SESSION['lang']['keterangan']."</td>
                                    <td align=center>".$_SESSION['lang']['action']."</td></tr>
                                 </tr>
                        </thead>
                        <tbody>";

			$where="";
			
            if($notransch!=''){
                $where.=" and notransaksi like '%".$notransch."%' ";
            }
			if($kdvhcsch!=''){
                $where.=" and kodevhc='".$kdvhcsch."' ";
            }
			if($tglsch!=''){
                $where.=" and tanggal='".$tglsch."' ";
            }
			if($tipesch!=''){
                $where.=" and tipetransaksi='".$tipesch."' ";
            }
			if($kdbrgsch!=''){
                $where.=" and kodebarang='".$kdbrgsch."' ";
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

            $ql2="select count(*) as jmlhrow from ".$dbname.".vhc_tyre where kodeorg='".$_SESSION['empl']['lokasitugas']."' ".$where."  ";// echo $ql2;notran
            $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
            $query2->setFetchMode(PDO::FETCH_OBJ);
            while($jsl=$query2->fetch()){  
                $jlhbrs= $jsl->jmlhrow;
            }
            $no=$maxdisplay;
            $str="select * from ".$dbname.".vhc_tyre where kodeorg='".$_SESSION['empl']['lokasitugas']."' ".$where." limit ".$offset.",".$limit."";
            //echo $iList;
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $no+=1;
                echo "<tr class=rowcontent>";
                echo "<td align=center>".$no."</td>";
				echo "<td align=left>".$bar['notransaksi']."</td>";
				echo "<td align=left>".$arrtipe[$bar['tipetransaksi']]."</td>";
				echo "<td align=left>".$bar['kodevhc']."</td>";
                echo "<td align=left>".$nmBrg[$bar['kodebarang']]."</td>";
                echo "<td align=left>".tanggalnormal($bar['tanggal'])."</td>";
                echo "<td align=left>".$bar['keterangan']."</td>";
                echo "<td align=center>
                        <img src=images/application/application_edit.png class=resicon  caption='Edit' 
                        onclick=\"fillField('".$bar['notransaksi']."','".$bar['kodevhc']."','".$bar['kodebarang']."',
						'".$bar['posisiroda']."','".tanggalnormal($bar['tanggal'])."','".$bar['kmhm']."',
						'".$bar['tekanan']."','".$bar['keterangan']."','".$bar['tipetransaksi']."');\">
                        
						<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$bar['notransaksi']."');\">
                        </td>";
                echo "</tr>";//
            }
            echo"
            <tr class=rowheader><td colspan=11 align=center>
            ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
            <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
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