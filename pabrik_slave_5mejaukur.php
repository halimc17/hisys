<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method=checkPostGet('method','');
$pabrik=checkPostGet('pabrik','');
$tangki=checkPostGet('tangki','');
$nilai=checkPostGet('nilai','');

$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmTangki=makeOption($dbname,'pabrik_5tangki','kodetangki,keterangan');

switch($method){   
    
    case 'insert':
		
		$str="INSERT INTO ".$dbname.".`pabrik_5mejaukur` 
				(`kodeorg`, `tangki`, `nilai`,`updateby`)
        values ('".$pabrik."','".$tangki."','".$nilai."','".$_SESSION['standard']['userid']."')";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;

    case 'update':
        $str="update ".$dbname.".pabrik_5mejaukur set nilai='".$nilai."',updateby='".$_SESSION['standard']['userid']."'			
				where kodeorg='".$pabrik."' and tangki='".$tangki."' ";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e){
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;
    
    case 'delete':
        $str="delete from ".$dbname.".pabrik_5mejaukur where kodeorg='".$pabrik."' and tangki='".$tangki."' ";
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
							<td align=center>".$_SESSION['lang']['pabrik']."</td>
							<td align=center>".$_SESSION['lang']['tangki']."</td>
							<td align=center>".$_SESSION['lang']['nilai']."</td> 
							<td align=center>".$_SESSION['lang']['updateby']."</td> 
							<td align=center>".$_SESSION['lang']['action']."</td></tr>
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

            $ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_5mejaukur  where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
            $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
            $query2->setFetchMode(PDO::FETCH_OBJ);
            while($jsl=$query2->fetch()){  
                $jlhbrs= $jsl->jmlhrow;
            }
            $no=$maxdisplay;
            $str="select * from ".$dbname.".pabrik_5mejaukur where kodeorg='".$_SESSION['empl']['lokasitugas']."' limit ".$offset.",".$limit."";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $no+=1;
                $nmKar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid='".$bar['updateby']."'");
                echo "<tr class=rowcontent>";
                echo "<td align=center>".$no."</td>";
				echo "<td align=left>".$bar['kodeorg']." - ".$nmOrg[$bar['kodeorg']]."</td>";
				echo "<td align=left>".$bar['tangki']." - ".$nmTangki[$bar['tangki']]."</td>";
				echo "<td align=right>".number_format($bar['nilai'],2)."</td>";
				echo "<td align=center>".$nmKar[$bar['updateby']]."</td>";
                echo "<td align=center>
                        <img src=images/application/application_edit.png class=resicon  caption='Edit' 
                        onclick=\"fillField('".$bar['kodeorg']."','".$bar['tangki']."','".$bar['nilai']."');\">
						<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$bar['kodeorg']."','".$bar['tangki']."');\">
                        </td>";
                echo "</tr>";//
            }
            echo"
            <tr class=rowheader><td colspan=6 align=center>
            ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
            <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
    break;
default:
}
?>