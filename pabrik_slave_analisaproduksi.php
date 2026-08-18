<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
	


$method=checkPostGet('method','');
$kodeorg	  =checkPostGet('kodeorg','');
$tanggal	  =tanggalsystem(checkPostGet('tanggal',''));

$kadarair     =checkPostGet('kadarair','');
$ffa     	  =checkPostGet('ffa','');
$dirt     	  =checkPostGet('dirt','');
$usbcpo           =checkPostGet('usbcpo','');//digunakan utk field Dobi
$kadarairpk   =checkPostGet('kadarairpk','');
$ffapk     	  =checkPostGet('ffapk','');
$dirtpk       =checkPostGet('dirtpk','');

$tglSch=tanggalsystemn(checkPostGet('tglSch',''));

$namasupp=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"tipe='PABRIK'");

if($tglSch=='--'){
    $tglSch='';
}

switch($method){
	
        
	case 'update':
		$str="update ".$dbname.".pabrik_produksi set			
				ffa='".$ffa."',kadarair='".$kadarair."',kadarkotoran='".$dirt."',dobi='".$usbcpo."',
				ffapk='".$ffapk."',kadarairpk='".$kadarairpk."',kadarkotoranpk='".$dirtpk."'
				where kodeorg='".$kodeorg."' and tanggal='".$tanggal."'";		
		try{
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}
	break;
        

	case'loadData':
		echo"<div id=container>
				<table class=sortable cellspacing=1 cellpadding=5 border=0>
				<thead>
					 <tr class=rowheader>
						<th align=center rowspan=2>No</th>
						<th align=center rowspan=2>".$_SESSION['lang']['pabrik']."</th>
						<th align=center rowspan=2>".$_SESSION['lang']['tanggal']."</th>   
						<th colspan=4 align=center>".$_SESSION['lang']['cpo']."</th>
						<th colspan=2 align=center>".$_SESSION['lang']['kernel']."</th>
						<th align=center rowspan=2>Action</th></tr>
					 </tr>
					 <tr class=rowheader>
						<th align=center>(FFa)(%)</th>
					   <th align=center>".$_SESSION['lang']['kadarair']." (%)</th>
					   <th align=center>".$_SESSION['lang']['kotoran']." (%)</th>
					   <th align=center>Dobi (%)</th>
					   <th align=center>".$_SESSION['lang']['kadarair']." (%)</th>
					   <th align=center>".$_SESSION['lang']['kotoran']." (%)</th>
						<th align=center style='display:none'>(Broken) (%)</th>
					 </tr>
				</thead>
				<tbody>";

				if ($_SESSION['empl']['tipelokasitugas']=='HOLDING') {
					if($tglSch!=''){
						$where=" where tanggal like '".$tglSch."' ";
					}
				}else{
					$where = "where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
					if($tglSch!=''){
						$where="where tanggal like '".$tglSch."' ";
					}
				}
    
		
               
                if($tglSch!=''){
                    $where="where tanggal like '".$tglSch."' ";
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
		
		$ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_produksi ".$where."  ";// echo $ql2;notran
		// echo $ql2;
		
                $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
                $query2->setFetchMode(PDO::FETCH_OBJ);
                while($jsl=$query2->fetch())
                {  
                    $jlhbrs= $jsl->jmlhrow;
                }
                $no=$maxdisplay;
				$str="select * from ".$dbname.".pabrik_produksi ".$where." order by tanggal desc limit ".$offset.",".$limit."";
				// echo $str;
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while($bar=$res->fetch()){
                    @$no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                    echo "<td align=left>".$nmOrg[$bar['kodeorg']]."</td>";
                    echo "<td align=left>".tanggalnormal($bar['tanggal'])."</td>";
					echo "<td align=right>".$bar['ffa']."</td>";
					echo "<td align=right>".$bar['kadarair']."</td>";
					echo "<td align=right>".$bar['kadarkotoran']."</td>";
					echo "<td align=right>".$bar['dobi']."</td>";
					echo "<td align=right>".$bar['kadarairpk']."</td>";
					echo "<td align=right>".$bar['kadarkotoranpk']."</td>";
					echo "<td align=right style='display:none'>".$bar['ffapk']."</td>";
					echo "<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' 
						onclick=\"fillField('".$bar['kodeorg']."','".tanggalnormal($bar['tanggal'])."',
						'".$bar['kadarkotoran']."','".$bar['kadarair']."','".$bar['ffa']."','".$bar['dobi']."',
						'".$bar['kadarkotoranpk']."','".$bar['kadarairpk']."','".$bar['ffapk']."');\"</td>";
                
				}
                echo"
		<tr class=rowheader><td colspan=11 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
        break;        

	
	
	case 'getperiodesort':
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