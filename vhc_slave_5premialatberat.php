<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method=checkPostGet('method','');
$pt=checkPostGet('pt','');
$jenis=checkPostGet('jenis','');
$posisi=checkPostGet('posisi','');
$basis = checkPostGet('basis', '');
$premibasis = checkPostGet('premibasis', '');
$premilebihbasis = checkPostGet('premilebihbasis', '');


$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$nmjenisvhc=makeOption($dbname,'vhc_5jenisvhc','jenisvhc,namajenisvhc');
	
$arrPos=array("0"=>"Operator","1"=>"Helper","2"=>"Sopir");	
	
	
switch($method){   
    
    case 'insert':
		$str="INSERT INTO ".$dbname.".`vhc_5premialatberat` 
				(kodept,jenisvhc,posisi,basis,premibasis,premilebihbasis,createby,createtime)
        values ('".$pt."','".$jenis."','".$posisi."','".$basis."','".$premibasis."','".$premilebihbasis."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;

    case 'update':
        $str="update ".$dbname.".vhc_5premialatberat set basis='".$basis."',premibasis='".$premibasis."',
				premilebihbasis='".$premilebihbasis."',updateby='".$_SESSION['standard']['userid']."'			
				where kodept='".$pt."' and jenisvhc='".$jenis."' and posisi='".$posisi."' ";
        try{
            $owlPDO->exec($str);
        }
        catch (PDOException $e){
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;
    
    case 'delete':
        $str="delete from ".$dbname.".vhc_5premialatberat where kodept='".$pt."' and jenisvhc='".$jenis."' and posisi='".$posisi."' ";
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
							<td align=center>".$_SESSION['lang']['pt']."</td>
							<td align=center>".$_SESSION['lang']['jenisvch']."</td>
							<td align=center>".$_SESSION['lang']['vhc_posisi']."</td>
							<td align=center style='width:75px;'>".$_SESSION['lang']['premlebihbasis']." 1 (HM)</td>
                            <td align=center style='width:75px;'>".$_SESSION['lang']['premlebihbasis']." 1 (Rp)</td>
                            <td align=center style='width:75px;'>".$_SESSION['lang']['premlebihbasis']." 2 (Rp)</td>
                            <td align=center>" . $_SESSION['lang']['updateby'] . "</td>
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

            $ql2="select count(*) as jmlhrow from ".$dbname.".vhc_5premialatberat";
            $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
            $query2->setFetchMode(PDO::FETCH_OBJ);
            while($jsl=$query2->fetch()){  
                $jlhbrs= $jsl->jmlhrow;
            }
            $no=$maxdisplay;
            $str="select * from ".$dbname.".vhc_5premialatberat limit ".$offset.",".$limit."";
            $hsl=fetchdata($str);
            if(count($hsl) > 0){
                $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_ASSOC);
                while($bar=$res->fetch()){
                    $no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                    echo "<td align=left>".$bar['kodept']." - ".$nmOrg[$bar['kodept']]."</td>";
                    echo "<td align=left>".$bar['jenisvhc']." - ".$nmjenisvhc[$bar['jenisvhc']]."</td>
                        <td align=left>".$arrPos[$bar['posisi']]."</td>
                        <td align=right>".number_format($bar['basis'],2)."</td>
                        <td align=right >".number_format($bar['premibasis'],2)."</td>
                        <td align=right>".number_format($bar['premilebihbasis'],2)."</td>
                        <td align=center>".getNamaKaryawan($bar['updateby'])."</td>";
                    echo "<td align=center>
                            <img src=images/application/application_edit.png class=resicon  caption='Edit' 
                            onclick=\"fillField('".$bar['kodept']."','".$bar['jenisvhc']."','".$bar['posisi']."','".$bar['basis']."','".$bar['premibasis']."','".$bar['premilebihbasis']."');\">
                            <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$bar['kodept']."','".$bar['jenisvhc']."','".$bar['posisi']."');\">
                            </td>";
                    echo "</tr>";//
                }
            }else{
                echo "<tr class=rowcontent><td align=center colspan=9>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
            }
            echo"
            <tr class=rowheader><td colspan=9 align=center>
            ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
            <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
    break;
default:
}
?>