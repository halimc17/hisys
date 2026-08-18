<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$param 		= $_POST;
$method 	= checkPostGet('method', '');
$kodejenis 	= checkPostGet('kodejenis', '');
$namajenis 	= checkPostGet('namajenis', '');

// echo"<pre>";
// print_r($param);
// echo"</pre>";

$str = "SELECT * from ".$dbname.".keu_5akun where length(noakun)=7";		
$res=fetchdata($str);
foreach($res as $bar){
	$nmakun[$bar['noakun']]=$bar['namaakun'];
}	

$str = "SELECT * from ".$dbname.".log_5masterbarang where kelompokbarang='400'";		
$res=fetchdata($str);
foreach($res as $bar){
	$nmbarang[$bar['kodebarang']]=$bar['namabarang'];
}

$nmjurnal=array("0"=>$_SESSION['lang']['tidak'],"1"=>$_SESSION['lang']['ya'],);


switch ($method) {

	case'formdetaildata':
		$tab = "";

		$tab .= "<table class=sortable cellspacing=1 cellpadding=3 align=center width=60%>";
			$tab .= "<thead>";
				$tab .= "<tr>";
					$tab .= "<th>Keterangan</th>";
					$tab .= "<th>Value</th>";
				$tab .= "</tr>";
			$tab .= "</thead>";

			$tab .= "<tbody>";
				$tab .= "<tr class=rowcontent>";
					$tab .= "<td>".$_SESSION['lang']['unit']."</td>";
					$tab .= "<td><select></select></td>";
				$tab .= "</tr>";
			$tab .= "</tbody>";
		$tab .= "</table>";

		echo $tab;
	break;
	
	case'editht':
		$str = "select * from ".$dbname.".keu_5jenispenagihan  where kodejenis='".$param['kodejenis']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$kodejenis=$bar['kodejenis'];
			$namajenis=$bar['namajenis'];
			$initial=$bar['initial'];
			$printout=$bar['printout'];
			$jurnal=$bar['jurnal'];
			$jurnalppn=$bar['jurnalppn'];
			$status=$bar['status'];
		}
		
		 echo $kodejenis."###".$namajenis."###".$initial."###".$printout."###".$jurnal."###".$jurnalppn."###".$status;
	
	break;
	
	case'loaddatadt':
		$str = "select * from ".$dbname.".keu_5jenispenagihandt  where kodejenis='".$param['kodejenis']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			@$no+=1;
			$tab.="<tr class=rowcontent>";
				$tab.="<td align=center>".$no."</td>";
				$tab.="<td align=left>".$bar['kodebarang']." ".$nmbarang[$bar['kodebarang']]."</td>";
				$tab.="<td align=left>".$bar['noakunuangmuka']." ".$nmakun[$bar['noakunuangmuka']]."</td>";
				$tab.="<td align=left>".$bar['noakunpiutang']." ".$nmakun[$bar['noakunpiutang']]."</td>";
				$tab.="<td align=left>".$bar['noakunsales']." ".$nmakun[$bar['noakunsales']]."</td>";
				
			
				$tab.="<td align=left>".$bar['noakunklaimmutu']." ".$nmakun[$bar['noakunklaimmutu']]."</td>";
				$tab.="<td align=left>".$bar['noakunklaimsusut']." ".$nmakun[$bar['noakunklaimsusut']]."</td>";
					$tab.="<td align=left>".$bar['noakunppn']." ".$nmakun[$bar['noakunppn']]."</td>";
				$tab.="<td align=center>
						<img src=images/application/application_delete.png class=zImgBtn   title='Hapus Data' caption='Delete' 
						onclick=\"deletedt('".$bar['kodejenis']."','".$bar['kodebarang']."');\">";
				if($bar['kodejenis']=='OTPI'){
					$tab.="
							<img src=images/skyblue/zoom.png class=zImgBtn title='Tambah Detail Data' caption='Detail Data' 
							onclick=\"detaildata('".$bar['kodejenis']."','".$bar['kodebarang']."');\">";
				} else {}

				$tab.="</td>";

			$tab.="</tr>";
		}
		echo $tab;
	break;
	
    case'loaddataht':
	
		#= bentuk default data
        $tab        ="";
        $footer     ="";
		
		#= bentuk paging
		$limit      = 10;
        $page       = 0;
        $colspan    = 9;
		
		if (isset($param['pagedata'])) {
			$page   = $param['pagedata'];
			if ($page < 0)
				$page = 0;
        }
		$offset     = floatval($page) * $limit;
		$maxdisplay = (floatval($page) * $limit);
        $no         = ((floatval($page) * $limit));
        
		#=where pencarian
		$where	='';
        if($kodejenis!='' || $namajenis!=''){ 
            $where.="and kodejenis LIKE '%".$kodejenis."%' and namajenis LIKE '%".$namajenis."%'";
		}
		
		#= bentuk jumlah data
		$str = "SELECT COUNT(*) AS jlhbrs FROM ".$dbname.".keu_5jenispenagihan where 1=1 ".$where." ";
		$res=fetchdata($str);
		$jlhbrs=0;
		foreach($res as $bar){
			$jlhbrs=$bar['jlhbrs'];
		} 
		
        $totrows    = ceil($jlhbrs / $limit);
		if($totrows == 0){
			$totrows = 1;
		}
		$isiRow = '';
		for ($er = 1; $er <= $totrows; $er++){
			$sel    = ($page==$er-1)? 'selected': '';
			$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
		}
        $frompage = ((floatval($page)*$limit)+1);
        if(((floatval($page)+1)*$limit) > $jlhbrs){
            $topage = $jlhbrs;
        }else{
            $topage = ((floatval($page)+1)*$limit);
        }
		
		#= jika data kosong
        if($jlhbrs < 1){
			$tab.="<tr class=rowcontent>";
			$tab.="<td style='text-align:center' colspan=".$colspan.">" . $_SESSION['lang']['errdatanotexist'] . "</td>";
			$tab.=" </tr>";
		#= jika data isi			
        } else {
           $str = "SELECT * FROM ".$dbname.".keu_5jenispenagihan WHERE 1=1 ".$where." 
			ORDER BY namajenis asc   LIMIT ".$offset.",".$limit." ";
		   // echo $str;
		   $res=fetchdata($str);
           foreach($res as $bar){
           		if($bar['status'] == 1){
           			$stat = 'Aktif';
           		} else {
           			$stat = 'Tidak Aktif';
           		}
                $no+=1;
                $tab.=   "<tr class=rowcontent>";
                    $tab.=  "<td align=center>".$no."</td>";
                    $tab.=  "<td align=left>".$bar['kodejenis']."</td>";
                    $tab.=  "<td align=left>".$bar['namajenis']."</td>";
                    $tab.=  "<td align=left>".$bar['initial']."</td>";
                    $tab.=  "<td align=left>".$bar['printout']."</td>";
                    $tab.=  "<td align=left>".$nmjurnal[$bar['jurnal']]."</td>";
                    $tab.=  "<td align=left>".$nmjurnal[$bar['jurnalppn']]."</td>"; 
                    $tab.=  "<td align=left>".$stat."</td>";
                    $tab.=  "<td align=center>
                                <img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"editht('".$bar['kodejenis']."');\">
                                <img src=images/application/application_delete.png class=zImgBtn  caption='Delete' onclick=\"deleteht('".$bar['kodejenis']."');\">";
				$tab.="</td>";
                $tab.="</tr>";
				$tab.="</tbody>";
			}
            $footer.="<tr>";
			$footer.="<td colspan=".$colspan." align=center> ".$frompage." to ".$topage." Of ".  $jlhbrs."</td>";
			$footer.="</tr>";
            $footer.="<tr>";
			$footer.="<td colspan=".$colspan." align=center>";
			if($page!=0){
				$footer .= "<button class=mybutton onclick=loaddataht(" . (floatval($page) - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
			}
			$footer  .= "<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
			if((floatval($page)+1) != $totrows){
			$footer .="<button class=mybutton onclick=loaddataht(" . (floatval($page) + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
			}
            $footer.="</td>";
		    $footer.="</tr>";
        }
        echo $tab."####".$footer;
	 break;

	 case 'savedt':

		if($param['kodejenis']!='OTPI'){
			if($param['kodejenis'] == '' || $param['kodebarang'] == '' || $param['noakunpiutang'] == '' || $param['noakunsales'] == '' || $param['noakunuangmuka'] == '' || $param['noakunppn'] == '' || $param['pph22'] == '') {
				exit("Warning : Masih terdapat inputan yang masih kosong!");
			}
		} else {
			if($param['kodejenis'] == '' || $param['kodebarang'] == '') {
				exit("Warning : Masih terdapat inputan yang masih kosong!");
			}
		}

        $str     = "INSERT INTO " . $dbname . ".keu_5jenispenagihandt 
			(`kodejenis`,`kodebarang`,`noakunpiutang`,`noakunsales`,
				`noakunuangmuka`,`noakunppn`,`pph22`,`createby`,`createtime`) VALUES 
			('".$param['kodejenis']."','".$param['kodebarang']."','".$param['noakunpiutang']."',
			'".$param['noakunsales']."','".$param['noakunuangmuka']."','".$param['noakunppn']."',
			'".$param['pph22']."','" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."')";
		try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

	
    case 'saveht':
        $str     = "INSERT INTO " . $dbname . ".keu_5jenispenagihan
			(`kodejenis`,`namajenis`,`jurnal`,`jurnalppn`,`printout`,
				`initial`,`nourut`,`createby`,`createtime`,`status`) VALUES 
			('".$param['kodejenis']."','".$param['namajenis']."','".$param['jurnal']."','".$param['jurnalppn']."',
			'".$param['printout']."','".$param['initial']."','".$param['nourut']."',
			'" . $_SESSION['standard']['userid'] . "','".date('Y-m-d H:i:s')."','".$param['status']."')";
		try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
	
    case 'updateht':
        $str  = "UPDATE " . $dbname . ".keu_5jenispenagihan SET 
					namajenis='".$param['namajenis']."',
					jurnal='".$param['jurnal']."',
					printout='".$param['printout']."',
					initial='".$param['initial']."',
					status='".$param['status']."',
					updateby='".$_SESSION['standard']['userid'] . "' WHERE 
					kodejenis='".$param['kodejenis']."'";
        try{
            $owlPDO->exec($str);
        }catch (PDOException $e){
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;

    case 'deleteht':
        $str = "DELETE FROM ".$dbname.".keu_5jenispenagihan WHERE kodejenis='".$param['kodejenis']."'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
	
    case 'deletedt':
        $str = "DELETE FROM ".$dbname.".keu_5jenispenagihandt
			WHERE kodejenis='".$param['kodejenis']."' and kodebarang='".$param['kodebarang']."'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
    
	
    default:
	break;
}
?>