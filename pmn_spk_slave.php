<?php
ini_set('display_errors',0);
error_reporting(0);

require_once('master_validation.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/nangkoelib.php');


$rekeningbank= checkPostGet('rekeningbank', '');
$tipe        = checkPostGet('tipe', '');
$method      = checkPostGet('method', '');
$tanggal     = tanggalsystemn(checkPostGet('tanggal',''));
$nokontrak   = checkPostGet('nokontrak', '');
$kodecustomer= checkPostGet('kodecustomer', '');
$kodebarang  = checkPostGet('kodebarang', '');
$kodept      = checkPostGet('kodept', '');

switch ($method) {
	
	case'detail':
		// echo"<fieldset><legend><b>Form Detail</b></legend>";
		echo"<table class='sortable' cellspacing='1' cellpadding='5' border='0'>";
		echo"<thead><tr class=rowheader>";
		echo"<th align=center>".$_SESSION['lang']['kode']."</th>";
		echo"<th align=center>".$_SESSION['lang']['nama']."</th>";
		echo"<th align=center>".$_SESSION['lang']['keterangan']."</th>";
		echo"<th align=center>".$_SESSION['lang']['nospk']."</th>";
		echo"</tr></thead>";
		$str="select * from ".$dbname.".pmn_5jenisspk where penjualan=1";
		$res=$owlPDO->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()) {
			$kodejenis[$bar['kode']]=$bar['kode'];
			$namajenis[$bar['kode']]=$bar['nama'];
			$ketjenis[$bar['kode']]=$bar['keterangan'];
			$filejenis[$bar['kode']]=$bar['file'];
			$database[$bar['kode']]=$bar['file'];
		}
		foreach($kodejenis as $kdjenis){
				$str="select * from ".$dbname.".".$filejenis[$kdjenis]."  where  nokontrak='".$nokontrak."' ";
				// echo $str;
				$res=$owlPDO->query($str);
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()) {
					$jumtrans[$kdjenis]=$bar['jumlah'];								
					$nospk[$kdjenis]=$bar['nospk'];								
				}
			$attribut = "style='cursor:pointer;text-decoration: underline' title='Click to Detail' onclick=\"loadtransaksi('".$filejenis[$kdjenis]."','".$kdjenis."')\";";
			echo"<tr class=rowcontent>";
				echo"<td ".$attribut.">".$kdjenis."</td>";
				echo"<td>".$namajenis[$kdjenis]."</td>";
				echo"<td>".$ketjenis[$kdjenis]."</td>";
				echo"<td>".$nospk[$kdjenis]."</td>";
			echo"</tr>";
		}
		echo"</table>";
	echo"</fieldset>";
	
	break;
	
	
	 case'carinokontrak':
        echo"<fieldset  style='float:left;' >
                <legend>".$_SESSION['lang']['find']." ".$_SESSION['lang']['NoKontrak']."</legend>
                    <table cellspacing=1 border=0 class=data>
                        <tr>
                            <td colspan=2>".$_SESSION['lang']['NoKontrak']."</td>
                            <td colspan=5>: 
								<input type=text id=daftarnokontrak value='".$nokontrak."' class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
								<button class=mybutton onclick=caridaftarnokontrak()>cari</button>
                            <td>
                        <tr>
                    </table>
                    <table>
                    <thead>
                    <tr class=rowheader>
                            <td>No</td>
                            <td>".$_SESSION['lang']['NoKontrak']."</td>
                            <td>".$_SESSION['lang']['tanggal']."</td>
                            <td>".$_SESSION['lang']['Pembeli']."</td>
                    </tr></thead>";

                    if($nokontrak!=''){
						$str="select * from ".$dbname.".pmn_kontrakjual where nokontrak like '%".$nokontrak."%'";
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);
                        while($bar=$res->fetch()){
                            $no+=1;
                            echo"
                            <tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"movecarinokontrak('".$bar['nokontrak']."','".$bar['kodept']."','".$bar['tanggalkontrak']."','".$bar['koderekanan']."','".$bar['kodebarang']."');\">
                                    <td>".$no."</td>
                                    <td>".$bar['nokontrak']."</td> 
                                    <td>".$bar['tanggalkontrak']."</td> 
                                    <td>".$bar['koderekanan']."</td> 
                            </tr>";
                        }
					}
                    echo"</table>
        </fieldset>";
	
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
		$where=" where 1=1";

        if($nokontrak!='') {
			$where.=" and nokontrak like '".$nokontrak."%' ";
        } 
		if($kodecustomer!='') {
			$where.=" and koderekanan = '".$kodecustomer."' ";
        }
		
		$nmrek=makeOption($dbname,'pmn_4customer','kodecustomer,namacustomer');
		
        $str="select count(*) as jmlhrow from ".$dbname.".pmn_kontrakjual ".$where;
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$jlhbrs=owlBaris($res);	
        $no=0;
		$no=$maxdisplay;
		/*
        $str="SELECT * from ".$dbname.".pmn_kontrakjual  ".$where." limit ".$offset.",".$limit." ";
		*/
		$str="SELECT *,left(nokontrak,3) as filternomor,right(nokontrak,2) as filtertahun from ".$dbname.".pmn_kontrakjual ".$where." order by filtertahun desc,filternomor desc limit ".$offset.",".$limit." ";
        $tab="";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
            $no+=1;
			
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>".$no."</td>";
            $tab.="<td align=left>".$bar['nokontrak']."</td>";
            $tab.="<td align=left>".tanggalnormal($bar['tanggalkontrak'])."</td>";
            $tab.="<td align=left>".$bar['koderekanan']." - ".$nmrek[$bar['koderekanan']]."</td>";
           
			  $tab.="<td align=center><img src=images/application/application_edit.png class=resicon  title='Edit' 
                     onclick=\"edit('".$bar['nokontrak']."','".$bar['kodept']."','".tanggalnormal($bar['tanggalkontrak'])."','".$bar['koderekanan']."','".$bar['kodebarang']."');\"></td>";
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
            <tr><td colspan=5 align=center>
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
        echo $tab."####".$footd;
    break;
}
?>