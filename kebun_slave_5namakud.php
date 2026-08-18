<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kode = checkPostGet('kodegudang','');
$nama = checkPostGet('afdeling','');
$status = checkPostGet('status','');
$method = checkPostGet('method','');
$kodeunit = checkPostGet('kodeunit','');
$noakuninvest = checkPostGet('noakuninvestasi','');


$skebun="select namaorganisasi,kodeorganisasi,induk from ".$dbname.".organisasi where inti=1 and tipe in ('KEBUN') ";
$qkebun=$owlPDO->query($skebun) or die(print " Gagal: ".PDOException::getMessage());
$qkebun->setFetchMode(PDO::FETCH_ASSOC);
while($rkebun=$qkebun->fetch()){
	$kodept[$rkebun['kodeorganisasi']]=$rkebun['induk'];
}




switch ($method) {
    case 'update':
		$str="update ".$dbname.".kebun_5namakud set kodeunit='".$kodeunit."',kodept='".$kodept[$kodeunit]."',status='".$status."',noakuninvestasi='".$noakuninvest."' 
			where afdeling='".$nama."'  and kodesupplier='".$kode."' ";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
			die();
		}
	break;
    case 'insert':
	
		$str="select * from ".$dbname.".kebun_5namakud where afdeling='".$nama."' and kodesupplier='".$kode."' ";
		$qry=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numRows=owlBaris($qry);
		if($numRows>=1){
			echo "Error: Data Sudah Ada.";
		}
	
	
		$str="insert into ".$dbname.".kebun_5namakud (afdeling,kodesupplier,noakuninvestasi,kodeunit,kodept,status) 
			values('".$nama."','".$kode."','".$noakuninvest."','".$kodeunit."','".$kodept[$kodeunit]."','".$status."')";
		try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
			die();
		}
	break;
    case 'delete':
        $str = "delete from ".$dbname.".kebun_5namakud where id='".$kode."'";
        try{
			$owlPDO->exec($str); 
		}
		catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
			die();
		}
        break;
    case 'loadData':
    echo"<div id=container>
    <table class=sortable cellspacing=1 border=0 >
	     <thead>
		 	<tr class=rowheader>
		 		<td align=center>No</td>
		 		<td align=center>Nama KUD Organisasi</td>
		 		<td align=center>Nama KUD Supplier</td>
		 		<td align=center>".$_SESSION['lang']['noakuninvestasi']."</td>
		 		<td align=center>Unit Iduk</td>
		 		<td align=center>PT Induk</td>
		 		<td align=center>" . $_SESSION['lang']['status'] . "</td>
		 		<td align=center style='width:30px;'>Action</td>
		 	</tr>
		 </thead>
		 <tbody>";

			$where="";
			$whrunit=getOrgdetail(2);
				
			
            $limit=20;
            $page=0;
            if(isset($_POST['page']))
            {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
            }
            $offset=$page*$limit;
            $maxdisplay=($page*$limit);

            $ql2="select count(*) as jmlhrow from ".$dbname.".kebun_5namakud where 1=1 and kodeunit in (".$whrunit.")";
            $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
            $query2->setFetchMode(PDO::FETCH_OBJ);
            while($jsl=$query2->fetch()){  
                $jlhbrs= $jsl->jmlhrow;
            }
            $no=$maxdisplay;
    		$nmakun=makeOption($dbname,'keu_5akun','noakun,namaakun');
    		$namagudang=makeOption($dbname,'log_5supplier','supplierid,namasupplier');
    		$namaorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
			$str = "select * from " . $dbname . ".kebun_5namakud where 1=1 and kodeunit in (".$whrunit.") limit ".$offset.",".$limit."";
			$res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			while ($bar1= $res1->fetch(PDO::FETCH_OBJ)) {
				$no+=1;
				if ($bar1->status==1){
					$statusaktif='Aktif';
				}
				if ($bar1->status==0){
					$statusaktif=' Tidak Aktif';
				}
			echo"<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td >".$bar1->afdeling." - " . $namaorg[$bar1->afdeling] . "</td>
					<td >".$bar1->kodesupplier." - " . $namagudang[$bar1->kodesupplier]."</td>
					<td >".$bar1->noakuninvestasi." ".$nmakun[$bar1->noakuninvestasi]."</td>
					<td >".$bar1->kodeunit."</td>
					<td >".$bar1->kodept."</td>
					<td align=center>" .$statusaktif. "</td>
    				<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('" . $bar1->afdeling . "','" . $bar1->kodesupplier. "','" . $bar1->status. "','" . $bar1->kodeunit. "','".$bar1->noakuninvestasi."');\"></td>
    	 		</tr>";       
			}
			echo"<tr class=rowheader>
					<td colspan=5 align=center>
            		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
            		<button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            		<button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            		</td>
            	</tr>";
break;
default:
}

?>