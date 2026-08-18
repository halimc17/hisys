<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

#regional
$sregional="select distinct regional from ".$dbname.".bgt_regional_assignment where kodeunit='".$_SESSION['empl']['lokasitugas']."'";
$qregional=$owlPDO->query($sregional) or die(print " Gagal: ".PDOException::getMessage());
$qregional->setFetchMode(PDO::FETCH_ASSOC);
$regional=$qregional->fetch();

$tab=$_POST['tab'];
if((isset($_POST['txtfind']))!=''){
    $awalan=$_POST['awalan'];
    $txtfind=$_POST['txtfind'];
    $thnBudget=$_POST['thnbgt'];
	$thnBudget=checkPostGet('thnbgt','');
    if($tab=='1'){
        // $str="select b.kodebarang,a.namabarang,a.satuan from ".$dbname.".log_5masterbarang a "
        //         . "left join ".$dbname.".bgt_masterbarang b on a.kodebarang=b.kodebarang"
        //         . " where tahunbudget='".$_POST['thnbgt']."' and regional='".$regional['regional']."'  and "
        //         . " (a.kodebarang like '".$txtfind."%' "
        //         . "or namabarang like '%".$txtfind."%') ";
                $str="select * from ".$dbname.".log_5masterbarang where kodebarang like '".$awalan."%' and (namabarang like '%".$txtfind."%' or kodebarang like '%".$txtfind."%') ";
    }else{
        $str="select * from ".$dbname.".log_5masterbarang where kodebarang like '".$awalan."%' and (namabarang like '%".$txtfind."%' or kodebarang like '%".$txtfind."%') ";
    }
echo"
        <fieldset>
        <legend>".$_SESSION['lang']['result']."</legend>
        <div style=\"overflow:auto; height:332px;\" >
        <table class=sortable cellspacing=1 cellpadding=2  border=0>
        <thead>
        <tr class=rowheader>
			<td align=center>No.</td>
			<td align=center>".$_SESSION['lang']['kodebarang']."</td>
			<td align=center>".$_SESSION['lang']['namabarang']."</td>
			<td align=center>".$_SESSION['lang']['satuan']."</td>
			<td align=center>".$_SESSION['lang']['harga']."</td>
        </tr>
        </thead>
        <tbody>";

$no=0;
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);        
while($bar=$res->fetch()){
    $no+=1;
    if($bar->inactive==1){
        echo"<tr class=rowcontent style='cursor:pointer;'  title='Inactive' >";
        $bar->namabarang=$bar->namabarang. " [Inactive]";
    }else{
        if($tab=='1')
            echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setBrg(1,'".$bar->kodebarang."','".$bar->namabarang."','".$bar->satuan."')\" title='Click' >";
        if($tab=='2')
            echo"<tr class=rowcontent style='cursor:pointer;' onclick=\"setBrg(2,'".$bar->kodebarang."','".$bar->namabarang."','".$bar->satuan."')\" title='Click' >";
    }   
    echo" <td  align=center>".$no."</td>
        <td align=center>".$bar->kodebarang."</td>
        <td>".$bar->namabarang."</td>
        <td>".$bar->satuan."</td>";
		
		
		$sHrg="select distinct hargasatuan from ".$dbname.".bgt_masterbarang where regional='".$regional['regional']."' and kodebarang='".$bar->kodebarang."' and tahunbudget='".$thnBudget."' and closed=1";
		$qHrg=$owlPDO->query($sHrg) or die(print " Gagal: ".PDOException::getMessage());
		$qHrg->setFetchMode(PDO::FETCH_ASSOC);
		$rHrg=$qHrg->fetch();
		echo"<td align=right>".@number_format($rHrg['hargasatuan'])."</td>";
			
    echo" </tr>";
    }	 
    echo "</tbody>
        <tfoot>
        </tfoot>
        </table></div></fieldset>";	
}
?>