<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kode = checkPostGet('kodegudang','');
$nama = checkPostGet('afdeling','');
$status = checkPostGet('status','');
$method = checkPostGet('method','');


switch ($method) {
    case 'update':
    if ($status==0){
    	$str = "select status from " . $dbname . ".kebun_5gudangtransaksi WHERE afdeling='".$nama."'";
			$res1=$owlPDO->query($str);
			$res1->setFetchMode(PDO::FETCH_OBJ);
			while ($bar1= $res1->fetch()) {
    			if ($bar1->status==0 )
    				exit("warning: Gudang Aktif Kosong");
			}
		}
		if ($status==1){
    		$str="update ".$dbname.".kebun_5gudangtransaksi set status='0' where afdeling='".$nama."' ";
    		try{
			$owlPDO->exec($str); 
			}
			catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
			die();
			}
			$str="update ".$dbname.".kebun_5gudangtransaksi set status='".$status."' where afdeling='".$nama."' and kodegudang='".$kode."' ";
			try{
			$owlPDO->exec($str); 
			}
			catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
			die();
			}
    	}
        break;
    case 'insert':
    	if ($status==1){
    		$str="update ".$dbname.".kebun_5gudangtransaksi set status='0' where afdeling='".$nama."' ";
    		try{
			$owlPDO->exec($str); 
			}
			catch (PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
			die();
			}
			$str="insert into ".$dbname.".kebun_5gudangtransaksi (afdeling,kodegudang,status) values('".$nama."','".$kode."','".$status."')";
			try{
			$owlPDO->exec($str); 
			}
			catch (PDOException $e){
			exit("warning: Data Sudah Ada");
			die();
			}
    	}
    	
    	if ($status==0){
			$str="insert into ".$dbname.".kebun_5gudangtransaksi (afdeling,kodegudang,status) values('".$nama."','".$kode."','".$status."')";
			try{
			$owlPDO->exec($str); 
			}
			catch (PDOException $e){
			exit("warning: Data Sudah Ada");
			die();
			}
    	}
        break;
    case 'delete':
        $str = "delete from ".$dbname.".kebun_5gudangtransaksi where id='".$kode."'";
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
		 		<td align=center>" . $_SESSION['lang']['afdeling'] . "</td>
		 		<td align=center>" . $_SESSION['lang']['gudang'] . "</td>
		 		<td align=center>" . $_SESSION['lang']['status'] . "</td>
		 		<td align=center style='width:30px;'>*</td>
		 	</tr>
		 </thead>
		 <tbody>";

			$where="";
				
			
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

            $ql2="select count(*) as jmlhrow from ".$dbname.".kebun_5gudangtransaksi";
            $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
            $query2->setFetchMode(PDO::FETCH_OBJ);
            while($jsl=$query2->fetch()){  
                $jlhbrs= $jsl->jmlhrow;
            }
            $no=$maxdisplay;
    		$namagudang=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
			$str = "select * from " . $dbname . ".kebun_5gudangtransaksi WHERE left(afdeling,4)='".$_SESSION['empl']['lokasitugas']."' order by kodegudang limit ".$offset.",".$limit."";
			$res1=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res1->setFetchMode(PDO::FETCH_OBJ);
			while ($bar1= $res1->fetch()) {
				$no+=1;
				if ($bar1->status==1){
					$statusaktif='Aktif';
				}
				if ($bar1->status==0){
					$statusaktif=' Tidak Aktif';
				}
			echo"<tr class=rowcontent>
					<td align=center>".$no."</td>
					<td >".$bar1->afdeling." - " . $namagudang[$bar1->afdeling] . "</td>
					<td >".$bar1->kodegudang." - " . $namagudang[$bar1->kodegudang]."</td>
					<td align=center>" .$statusaktif. "</td>
    				<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('" . $bar1->afdeling . "','" . $bar1->kodegudang. "','" . $bar1->status. "');\"></td>
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