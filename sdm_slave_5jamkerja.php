<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>	

<?php		
$kodeunit = checkPostGet('kodeunit', '');
$hari     = checkPostGet('hari', '');
$jamkerja = checkPostGet('jamkerja', '');

$jmMulai  = checkPostGet('jmMulai', '');
$mnMulai  = checkPostGet('mnMulai', '');
$jmSelesai= checkPostGet('jmSelesai', '');
$mnSelesai= checkPostGet('mnSelesai', '');
$method   = checkPostGet('method', '');

$pt       =makeOption($dbname,'organisasi','kodeorganisasi,induk');
$namaorg  =makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');



$jammasuk=$jmMulai.":".$mnMulai;
$jamkeluar=$jmSelesai.":".$mnSelesai;


if($hari=='Mon'){
	$urut=1;
}
if($hari=='Tue'){
	$urut=2;
}
if($hari=='Wed'){
	$urut=3;
}if($hari=='Thu'){
	$urut=4;
}if($hari=='Fri'){
	$urut=5;
}
if($hari=='Sat'){
	$urut=6;
}
if($hari=='Sun'){
	$urut=0;
}
	
	

switch($method){
	case 'insert':
	
		$str="insert into ".$dbname.".sdm_5jamkerja (`kodept`,`kodeunit`,`hari`,`jammasuk`,`jamkeluar`,`jamkerja`,`urut`)
		values ('".$pt[$kodeunit]."','".$kodeunit."','".$hari."','".$jammasuk."','".$jamkeluar."','".$jamkerja."','".$urut."')";
                try{$owlPDO->exec($str); }
                catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n"; 
                    die(); 
                }
	break;
        
        case 'update':
		$str="update ".$dbname.".sdm_5jamkerja set jammasuk='".$jammasuk."',jamkeluar='".$jamkeluar."' ,jamkerja='".$jamkerja."',urut='".$urut."' 
				where kodeunit='".$kodeunit."' and hari='".$hari."'";
                try{$owlPDO->exec($str); }
                    catch (PDOException $e) {
                        print " Gagal  !: " . $e->getMessage() . "\n"; 
                        die(); 
                    }
	break;
        

	case'loadData':

		echo"
			<table class=sortable cellspacing=1 border=0 cellpadding=5>
			 <thead>
				 <tr class=rowheader>
					<th align=center>No</th>
					<th align=center>".$_SESSION['lang']['unit']."</th>  
					<th align=center>".$_SESSION['lang']['hari']."</th>  
					<th align=center>".$_SESSION['lang']['jammasuk']."</th>  
					<th align=center>".$_SESSION['lang']['jamkeluar']."</th>  
					<th align=center>Jam Kerja</th>  
					
					<th align=center>Action</th></tr>
				 </tr>
				</thead>
				<tbody>";
		
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
		
		$ql2="select count(*) as jmlhrow from ".$dbname.".sdm_5jamkerja";// echo $ql2;notran		
        $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);
        while($jsl=$query2->fetch()){
            $jlhbrs= $jsl->jmlhrow;
		}
		$str="select * from ".$dbname.".sdm_5jamkerja order by kodeunit asc,urut asc limit ".$offset.",".$limit." ";
		$no=$maxdisplay;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
                    $no+=1;
                    echo "<tr class=rowcontent>";
                    echo "<td align=center>".$no."</td>";
                    echo "<td align=left>".$bar['kodeunit']."</td>";
                    echo "<td align=left>".$bar['hari']."</td>";
                    echo "<td align=right>".$bar['jammasuk']."</td>";
                    echo "<td align=right>".$bar['jamkeluar']."</td>";
                    echo "<td align=right>".$bar['jamkerja']."</td>";
                    
                    /*
					  <img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"fillField('".$bar['kodeorg']."','".$bar['kodesupplier']."',"
                            . "'".tanggalnormal($bar['tanggal'])."','".$bar['vhc']."','".$bar['harga']."','".$bar['hargaperkg']."');\">
                            
					*/
                    echo "<td align=center>
                          
                            <img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$bar['kodeunit']."','".$bar['hari']."');\">

                            </td>";
                    echo "</tr>";//<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$bar['kode']."');\">
		}
                echo"
		<tr class=rowheader><td colspan=7 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
	break;		
                

	case 'delete':
		$tab="delete from ".$dbname.".sdm_5jamkerja where kodeunit='".$kodeunit."' and hari='".$hari."'";		
        try{$owlPDO->exec($tab); }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n"; 
            die(); 
        }
	break;
	
	
	default:
}
?>