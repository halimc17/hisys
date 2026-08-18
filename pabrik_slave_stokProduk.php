<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		


$method=checkPostGet('method','');
$kdOrg=checkPostGet('kdOrg','');
$tgl=tanggalsystemn(checkPostGet('tgl',''));

$sawal=checkPostGet('sawal','');
$prod=checkPostGet('prod','');
$pakai=checkPostGet('pakai','');
$jual=checkPostGet('jual','');
$sisa=checkPostGet('sisa','');
$cangkang=checkPostGet('cangkang','');
$ket=checkPostGet('ket','');


$kdBrg=checkPostGet('kdBrg','');

$tglSch=tanggalsystemn(checkPostGet('tglSch',''));
$kdBrgSch=checkPostGet('kdBrgSch','');




//exit("Error:$sInsert");	
$namasupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PABRIK'");
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang','kelompokbarang=400');

if($tglSch=='--')
{
    $tglSch='';
}

?>

<?php

switch($method)
{//'".$_SESSION['standard']['userid']."'
	case 'insert':
            
            $iSave="INSERT INTO ".$dbname.".`pabrik_stokbarang` (`kodeorg`, `tanggal`, `kodebarang`, `saldoawal`, 
                    `produksi`, `pemakaian`, `penjualan`, `sisa`,  `cangkang`, `updateby`,`keterangan`)
            values ('".$kdOrg."','".$tgl."','".$kdBrg."','".$sawal."','".$prod."','".$pakai."','".$jual."',
                    '".$sisa."','".$cangkang."','".$_SESSION['standard']['userid']."','".$ket."')";
           // exit("Error:$iSave");
            try
            {
                $owlPDO->exec($iSave);
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                die(); 
            }
	break;
        
        case 'update':
            $iUpdate="update ".$dbname.".pabrik_stokbarang set saldoawal='".$sawal."',produksi='".$prod."',pemakaian='".$pakai."',"
            . "penjualan='".$jual."',sisa='".$sisa."',cangkang='".$cangkang."',updateby='".$_SESSION['standard']['userid']."',keterangan='".$ket."' "
            . " where kodeorg='".$kdOrg."' and kodebarang='".$kdBrg."' "
            . " and tanggal='".$tgl."'";
            try
            {
                $owlPDO->exec($iUpdate);
            }
            catch (PDOException $e) 
            {
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
                                        <td align=center>".$_SESSION['lang']['pabrik']."</td>
                                        <td align=center>".$_SESSION['lang']['tanggal']."</td>    
                                        <td align=center>".$_SESSION['lang']['kodebarang']."</td>
                                        <td align=center>".$_SESSION['lang']['saldoawal']."</td>
                                        <td align=center>".$_SESSION['lang']['produksi']."</td>
                                        <td align=center>".$_SESSION['lang']['jmlhPakai']."</td>
                                        <td align=center>".$_SESSION['lang']['penjualan']."</td>
                                        <td align=center>".$_SESSION['lang']['estimasi']." ".$_SESSION['lang']['saldoakhir']."</td>    
                                        <td align=center>".$_SESSION['lang']['cangkang']."</td>    
                                        <td align=center>".$_SESSION['lang']['keterangan']."</td>
                                        <td align=center>".$_SESSION['lang']['updateby']."</td>
                                        <td align=center>*</td></tr>
                                     </tr>
                            </thead>
                            <tbody>";
    
		$tmbh2=$tmbh3="";
                if($kdBrgSch!='')
                {
                    $tmbh2=" and kodebarang='".$kdBrgSch."' ";
                }
		
                if($tglSch!='')
                {
                    $tmbh3=" and tanggal like '".$tglSch."' ";
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
		
		$ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_stokbarang where kodeorg='".$_SESSION['empl']['lokasitugas']."'  ".$tmbh2." ".$tmbh3." ";// echo $ql2;notran
		
                $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
                $query2->setFetchMode(PDO::FETCH_OBJ);
                while($jsl=$query2->fetch())
                {  
                    $jlhbrs= $jsl->jmlhrow;
                }
                $no=$maxdisplay;
		$iList="select * from ".$dbname.".pabrik_stokbarang where kodeorg='".$_SESSION['empl']['lokasitugas']."'  ".$tmbh2." ".$tmbh3."  limit ".$offset.",".$limit."";
		//echo $iList;
                $nList=$owlPDO->query($iList) or die(print " Gagal: ".PDOException::getMessage());
                $nList->setFetchMode(PDO::FETCH_ASSOC);
                while($dList=$nList->fetch())
                {
                    $no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                    echo "<td align=left>".$nmOrg[$dList['kodeorg']]."</td>";
                    echo "<td align=left>".tanggalnormal($dList['tanggal'])."</td>";
                    echo "<td align=left>".$nmBrg[$dList['kodebarang']]."</td>";
                    echo "<td align=right>".number_format($dList['saldoawal'])."</td>";
                    echo "<td align=right>".number_format($dList['produksi'])."</td>";
                    echo "<td align=right>".number_format($dList['pemakaian'])."</td>";
                    echo "<td align=right>".number_format($dList['penjualan'])."</td>";
                    echo "<td align=right>".number_format($dList['sisa'])."</td>";
                    echo "<td align=right>".number_format($dList['cangkang'])."</td>";
                    echo "<td align=left>".$dList['keterangan']."</td>";
                    echo "<td align=left>".getNamaKaryawan($dList['updateby'])."</td>";
                    echo "<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$dList['kodeorg']."',
                            '".tanggalnormal($dList['tanggal'])."','".$dList['kodebarang']."','".$dList['saldoawal']."',
                            '".$dList['produksi']."','".$dList['pemakaian']."','".$dList['penjualan']."',
                            '".$dList['sisa']."','".$dList['cangkang']."','".$dList['keterangan']."');\">
                            <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$dList['kodeorg']."',
                            '".tanggalnormal($dList['tanggal'])."','".$dList['kodebarang']."');\">
                            </td>";
                    echo "</tr>";//<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$dList['kode']."');\">
		}
                echo"
		<tr class=rowheader><td colspan=12 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
        break;        

	case 'delete':
            $iDelete="delete from ".$dbname.".pabrik_stokbarang where kodeorg='".$kdOrg."' and tanggal='".$tgl."' "
            . " and kodebarang='".$kdBrg."' ";
            try
            {
                $owlPDO->exec($iDelete);
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                die(); 
            }
	break;
	
	case 'getperiodesort':
	//exit("Error:MASUK");
		$optpersort="<option value=''>".$_SESSION['lang']['all']."</option>";
		$aper = "SELECT distinct substr(tanggal,1,7) as tanggal FROM ".$dbname.".pabrik_stokbarang where substr(tanggal,1,7) order by tanggal desc";
		//exit ("Error:$asup");
		
                $bper=$owlPDO->query($aper) or die(print " Gagal: ".PDOException::getMessage());
                $bper->setFetchMode(PDO::FETCH_ASSOC);
                while($cper=$bper->fetch())
                {
			$optpersort.="<option value='".$cper['tanggal']."'>".$cper['tanggal']."</option>";
		}
		echo $optpersort;
	break;
	
	case 'getsuppsort':
			//exit("Error:xx");
		$optsupsort="<option value=''>".$_SESSION['lang']['all']."</option>";
		$asup = "SELECT distinct kodesupplier FROM ".$dbname.".pabrik_stokbarang ";
		
                $bsup=$owlPDO->query($asup) or die(print " Gagal: ".PDOException::getMessage());
                $bsup->setFetchMode(PDO::FETCH_ASSOC);
                while($csup=$bsup->fetch())
                {
			$optsupsort.="<option value='".$csup['kodesupplier']."'>".$namasupp[$csup['kodesupplier']]."</option>";
		}
		echo $optsupsort;//exit();
		//exit ("Error:$optsupsort");
	break;
	
	case 'getorgsort':
			//exit("Error:xx");
		$optorgsort="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$aorg = "SELECT distinct kodeorg FROM ".$dbname.".pabrik_stokbarang ";
		
                $borg=$owlPDO->query($aorg) or die(print " Gagal: ".PDOException::getMessage());
                $borg->setFetchMode(PDO::FETCH_ASSOC);
                while($corg=$borg->fetch())
                {
			$optorgsort.="<option value='".$corg['kodeorg']."'>".$namaorg[$corg['kodeorg']]."</option>";
		}
		echo $optorgsort;//exit();
		//exit ("Error:$optsupsort");
	break;
	
	
default:
}
?>