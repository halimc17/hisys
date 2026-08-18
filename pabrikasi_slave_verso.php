<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$kode=checkPostGet('kode','');
$nama=checkPostGet('nama','');
$method=checkPostGet('method','');
$kdbrg=checkPostGet('kdbrg','');
$kdso=checkPostGet('kdso','');
$stat=checkPostGet('stat','');
$namaBarangCari=checkPostGet('namaBarangCari','');

$nmbrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
$nmkar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$nmcus=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');


$kdptsch=checkPostGet('kdptsch','');
$kdcussch=checkPostGet('kdcussch','');
$kdsosch=checkPostGet('kdsosch','');
$kdbrgsch=checkPostGet('kdbrgsch','');
$noposch=checkPostGet('noposch','');
$salesidsch=checkPostGet('salesidsch','');
$statussch=checkPostGet('statussch','');
$tglsch=tanggalsystemn(checkPostGet('tglsch',''));
if($tglsch=='--'){
	$tglsch='';
}

$kdsoal=checkPostGet('kdsoal','');
$kdbrgal=checkPostGet('kdbrgal','');
$alasan=checkPostGet('alasan','');


$where="";	
if($kdptsch!='') {
	$where.=" and kodept='".$kdptsch."' ";
}
if($kdcussch!='') {
	$where.=" and kodecustomer='".$kdcussch."' ";
}
if($kdsosch!='') {
	$where.=" and a.kodeso like '%".$kdsosch."%' ";
}
if($tglsch!='') {
	$where.=" and tanggalpesan='".$tglsch."' ";
}
if($kdbrgsch!='') {
	$where.=" and kodebarang='".$kdbrgsch."' ";
}	
if($noposch!='') {
	$where.=" and nopo like '%".$noposch."%' ";
}	
if($salesidsch!='') {
	$where.=" and salesid='".$salesidsch."' ";
}
if($statussch!='') {
	$where.=" and status='".$statussch."' ";
}	


switch($method)
{
	case'savealasan':
		$str="update ".$dbname.".pabrikasi_salesdt set status='2',alasan='".$alasan."' where kodeso='".$kdsoal."' and kodebarang='".$kdbrgal."'";
		try{$owlPDO->exec($str); }
			catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
	break;
	
	
	case 'insert':
	
		$str="insert into ".$dbname.".pabrikasi_5outlet (`kode`,`nama`,`updateby`)
		values ('".$kode."','".$nama."','".$_SESSION['standard']['userid']."')";
		try{$owlPDO->exec($str); }
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	break;
	case 'update':
		$str="update ".$dbname.".pabrikasi_salesdt set status='".$stat."' where kodeso='".$kdso."' and kodebarang='".$kdbrg."'";
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
		
        $str="select count(*) as jmlhrow from ".$dbname.".pabrikasi_salesdt a left join ".$dbname.".pabrikasi_salesht b 
				on a.kodeso=b.kodeso where 1=1 ".$where." ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($res);	

        $no=0;
		$no=$maxdisplay;
        $str="SELECT a.*,b.kodept,b.kodecustomer,b.tanggalpesan,b.nopo,b.salesid,b.tanggalmulai,b.tanggalsampai 
				from ".$dbname.".pabrikasi_salesdt a left join ".$dbname.".pabrikasi_salesht b 
				on a.kodeso=b.kodeso where 1=1 and status!='3' ".$where."   limit ".$offset.",".$limit."";
		
        $tab="";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			

			if($bar['status']==0){
				$optstat.="<option value='0' selected>Waiting</option>";
			}else{
				$optstat.="<option value='0' >Waiting</option>";
			}
			
			if($bar['status']==1){
				$optstat.="<option value='1' selected>Open</option>";
				$disabled="disabled";
			}else{
				$optstat.="<option value='1'>Open</option>";
			}
			
			if($bar['status']==2){
				$optstat.="<option value='2' selected>Cancel</option>";
			}else{
				$optstat.="<option value='2'>Cancel</option>";
			}
			
			// if($bar['status']==3){
				// $optstat.="<option value='3' selected>Close</option>";
			// }else{
				// $optstat.="<option value='3'>Close</option>";
			// }
			
			
            $no+=1;
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=center>".$no."</td>";
			$tab.="<td align=left>".$bar['kodept']."</td>";
			$tab.="<td align=left id=kdso".$no.">".$bar['kodeso']."</td>";
			$tab.="<td align=left id=kdbrg".$no.">".$bar['kodebarang']."</td>";
			$tab.="<td align=left>".$nmbrg[$bar['kodebarang']]."</td>";
			$tab.="<td align=left>".$bar['jumlah']."</td>";
			$tab.="<td align=left>".$bar['keterangan']."</td>";
			$tab.="<td align=left>".$nmcus[$bar['kodecustomer']]."</td>";
			$tab.="<td align=left>".$bar['nopo']."</td>";
			$tab.="<td align=left>".tanggalnormal($bar['tanggalpesan'])."</td>";
			$tab.="<td align=left>".$nmkar[$bar['salesid']]."</td>";
			$tab.="<td align=center><select ".$disabled." id=stat".$no." style=\"width:150px;\" onchange=update(".$no.")>'".$optstat."'</select></td>";
            $tab.="</tr>";
			$optstat="";
			$disabled="";
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
            <tr><td colspan=12 align=center>
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
        echo $tab."####".$footd;
    break;

	case'getListBarang':
        echo"<fieldset  style='float:left;' >
                <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['namabarang']."</legend>
                    <table cellspacing=1 border=0 class=data>
                        <tr>
                            <td colspan=2>".$_SESSION['lang']['namabarang']."</td>

                            <td colspan=5>: 
                                    <input type=text id=namaBarangCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
                                    <button class=mybutton onclick=cariListBarang()>cari</button>
                            <td>
                        <tr>
                    </table>

                    <table id=listCariBarang >
                    <thead>
                    <tr class=rowheader>
                            <td>No</td>
                            <td>".$_SESSION['lang']['kodebarang']."</td>
                            <td>".$_SESSION['lang']['namabarang']."</td>
                            <td>".$_SESSION['lang']['satuan']."</td>
                            <td hidden>".$_SESSION['lang']['harga']."</td>    
                    </tr></thead>";

                    if($namaBarangCari!=''){
                        $str="select kodebarang,namabarang from ".$dbname.".log_5masterbarang where namabarang like '%".$namaBarangCari."%'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);
                        while($bar=$res->fetch())
                        {
                            $no+=1;
                            echo"
                            <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('".$bar['kodebarang']."','".$bar['namabarang']."');\">
                                    <td>".$no."</td>
                                    <td>".$bar['kodebarang']."</td>
                                    <td>".$bar['namabarang']."</td>
                            </tr>";
                        }
                    }
                    echo"</table>
        </fieldset>";
	
    break;
	
default:
}
?>