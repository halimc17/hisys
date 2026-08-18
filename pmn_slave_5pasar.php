<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		

$pasar = checkPostGet('pasar', '');
$komoditi = checkPostGet('komoditi','');
$satuan = checkPostGet('satuan','');
$sumber = checkPostGet('sumber','');
$method = checkPostGet('method', '');

$arkomoditi = makeOption($dbname,"log_5masterbarang","kodebarang,namabarang","kelompokbarang='400'");


switch($method)
{
	

    case 'insert':
            $i="insert into ".$dbname.".pmn_5pasar (pasar,komoditi,satuan,sumber,updateby)
            values ('".$pasar."','".$komoditi."','".$satuan."','".$sumber."','".$_SESSION['standard']['userid']."')";
        try{$owlPDO->exec($i); }
catch (PDOException $e) {
    print " Gagal  !: " . $e->getMessage() . "\n"; 
    die(); 
}
            /*if(mysql_query($i))
            echo"";
            else
            echo " Gagal,".addslashes(mysql_error($conn));*/
    break; 
		
    case'loadData':
	echo"
		<div id=container>
		<table class=sortable cellspacing=1 border=0>
		<thead>
			<tr class=rowheader>
			<td align=center>".$_SESSION['lang']['nourut']."</td>
			<td align=center >".$_SESSION['lang']['pasar']."</td>
			<td align=center >".$_SESSION['lang']['nama']." ".$_SESSION['lang']['komoditi']."</td>
			<td align=center>".$_SESSION['lang']['satuan']."</td>
			<td align=center >".$_SESSION['lang']['sumber']."</td>
			<td align=center >".$_SESSION['lang']['updateby']."</td>
			<td align=center>".$_SESSION['lang']['action']."</td>
		</tr>
		</thead>
		<tbody>";
		
		
		$i="select * from ".$dbname.".pmn_5pasar order by pasar asc"; 
		//$n=mysql_query($i) or die(mysql_error($conn));
		//while($d=mysql_fetch_assoc($n))
		$n=$owlPDO->query($i) or die(print " Gagal: ".PDOException::getMessage());
$n->setFetchMode(PDO::FETCH_ASSOC);
while($d=$n->fetch())
                {
                    $no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                     echo "<td align=left>".$d['pasar']."</td>";
                     echo "<td align=left>".$arkomoditi[$d['komoditi']]."</td>";
                     echo "<td align=left>".$d['satuan']."</td>";
                     echo "<td align=left><a href=".$d['sumber']." target='_blank'>".$d['sumber']."</a></td>";
                     echo "<td align=left>".getNamaKaryawan($d['updateby'])."</td>";
                    echo "<td align=center>
                          <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['pasar']."');\">
                            </td>";
                    echo "</tr>";//
		}
		echo"</tbody></table>";
    break;

	case 'delete':
	//exit("Error:hahaha");
		$i="delete from ".$dbname.".pmn_5pasar where pasar='".$pasar."'";
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

	case 'getsatuan':

		$query = "select satuan from ".$dbname.".log_5masterbarang where kodebarang='".$komoditi."'";
		$resTab = fetchData($query);
		echo trim($resTab[0]['satuan']);

	break;

default:
}
?>
