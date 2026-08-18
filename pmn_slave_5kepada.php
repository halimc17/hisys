<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		

$id=checkPostGet('id','');
$alamat=checkPostGet('alamat','');
$kepada=checkPostGet('kepada','');
$method=checkPostGet('method','');

switch($method)
{
	

    case 'insert':
            $i="insert into ".$dbname.".pmn_5kepada (kepada,alamat,updateby)
            values ('".$kepada."','".$alamat."','".$_SESSION['standard']['userid']."')";
        //exit("Error:$i");
            /*if(mysql_query($i))
            echo"";
            else
            echo " Gagal,".addslashes(mysql_error($conn));*/
         try{$owlPDO->exec($i); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}
    break;

	case 'update':
            $i="update ".$dbname.".pmn_5kepada set kepada='".$kepada."', alamat='".$alamat."',updateby='".$_SESSION['standard']['userid']."' where id='".$id."'";
        //exit("Error:$i");
            /*if(mysql_query($i))
            echo"";
            else
            echo " Gagal,".addslashes(mysql_error($conn));*/
             try{$owlPDO->exec($i); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}
    break; 
		
    case'loadData':
	echo"
            <div id=container>
		<table class=sortable cellspacing=1 border=0>
                    <thead>
			 <tr class=rowheader>
			 	 <td align=center>".$_SESSION['lang']['nourut']."</td>
                                 <td align=center>".$_SESSION['lang']['kepada']."</td>
                                 <td align=center>".$_SESSION['lang']['alamat']."</td>
                                 <td align=center>".$_SESSION['lang']['updateby']."</td>
                                     <td align=center>".$_SESSION['lang']['action']."</td>
			 </tr>
		</thead>
		<tbody>";
		
		$no=0;
		$i="select * from ".$dbname.".pmn_5kepada order by kepada asc"; 
		//$n=mysql_query($i) or die(mysql_error($conn));
		//while($d=mysql_fetch_assoc($n))
                $n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
$n->setFetchMode(PDO::FETCH_ASSOC);
while($d=$n->fetch())
		{
                    $no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                    echo "<td align=left>".$d['kepada']."</td>";
                    echo "<td align=left>".$d['alamat']."</td>";
                    echo "<td align=left>".getNamaKaryawan($d['updateby'])."</td>";
                    echo "<td align=center>
                          <img src=images/application/application_edit.png class=resicon  caption='Delete' onclick=\"fillfield('".$d['kepada']."','".str_replace('\n','',$d['alamat'])."','".$d['id']."');\">
                          <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['id']."');\">
                            </td>";
                    echo "</tr>";//
		}
		echo"</tbody></table>";
    break;

	case 'delete':
	//exit("Error:hahaha");
		$i="delete from ".$dbname.".pmn_5kepada where id='".$id."'";
		//exit("Error.$str");
		/*if(mysql_query($i))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));*/
             try{$owlPDO->exec($i); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}
	break;

default:
}
?>
