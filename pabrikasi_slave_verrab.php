<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method=checkPostGet('method','');

$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$nmcus=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
$nmpb=makeOption($dbname,'pabrikasi_5masterht','kodepabrikasi,namapabrikasi');
$kdpabsch=checkPostGet('kdpabsch','');
$statussch=checkPostGet('statussch','');
$kdsosch=checkPostGet('kdsosch','');

$stat=checkPostGet('stat','');
$kdpab=checkPostGet('kdpab','');
$kdso=checkPostGet('kdso','');


$where="";	
if($kdpabsch!='') {
	$where.=" and kodepabrikasi like '%".$kdpabsch."%' ";
}
if($kdsosch!='') {
	$where.=" and kodeso like '%".$kdsosch."%' ";
}
if($statussch!='') {
	$where.=" and status='".$statussch."' ";
}



switch($method){
	
	case 'update':
		$str="update ".$dbname.".pabrikasi_rabht set status='".$stat."' where kodeso='".$kdso."' and kodepabrikasi='".$kdpab."'";
		try{$owlPDO->exec($str); }
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
	break;
	
		
	case'loaddata':

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
		
        $str="select count(*) as jmlhrow from ".$dbname.".pabrikasi_rabht where 1=1 ".$where." ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($res);	

        $no=0;
		$no=$maxdisplay;
        $str="SELECT *  from ".$dbname.".pabrikasi_rabht where 1=1 ".$where."   limit ".$offset.",".$limit."";
		
        $tab="";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			
			if($bar['status']==1){
				$optstat.="<option value='0' selected>Waiting</option>";
			}else{
				$optstat="<option value='0' >Waiting</option>";
			}
			
			if($bar['status']==1){
				$optstat.="<option value='1' selected>Open</option>";
			}else{
				$optstat.="<option value='1'>Open</option>";
			}
			
			if($bar['status']==2){
				$optstat.="<option value='2' selected>Cancel</option>";
			}else{
				$optstat.="<option value='2'>Cancel</option>";
			}
			
			if($bar['status']==3){
				$optstat.="<option value='3' selected>Close</option>";
			}else{
				$optstat.="<option value='3'>Close</option>";
			}
			
            $no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left id=kdpab".$no.">".$bar['kodepabrikasi']."</td>";
			$tab.="<td align=left>".$nmpb[$bar['kodepabrikasi']]."</td>";
			$tab.="<td align=left id=kdso".$no.">".$bar['kodeso']."</td>";
			$tab.="<td align=center><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrikasi_rabht','".$bar['kodepabrikasi']."','','pabrikasi_slave_rab_pdf',event)\"></td>";
			$tab.="<td align=center><select id=stat".$no." style=\"width:150px;\" onchange=update(".$no.")>'".$optstat."'</select></td>";
            $tab.="</tr>";
        }
        $totrows=ceil($jlhbrs/$limit);
        if($totrows==0){
                $totrows=1;
        }
        $isiRow='';
        for($er=1;$er<=$totrows;$er++){
                $sel = ($page==$er-1)? 'selected': '';
                $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        $footd="
            <tr><td colspan=6 align=center>
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
        echo $tab."####".$footd;
    break;

	
	
default:
}
?>