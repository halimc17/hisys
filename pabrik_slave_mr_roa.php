<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param=$_POST;

switch($param['proses']) {
    case'loadNewData':
    	$sParam="select * from ".$dbname.".pabrik_5mr_roa_jenis ";
        $rParam=fetchData($sParam);
        foreach($rParam as $row=>$lstParam){
            $lstKode[$lstParam['jenis']]=$lstParam['jenis'];
        }
		#ambil periode akuntansi
		$sAkuntansi="select periode,tutupbuku from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		$rAkuntansi=fetchData($sAkuntansi);
		foreach ($rAkuntansi as $key => $value) {
			$periodeAkun[$value['periode']]=$value['tutupbuku'];
		}


		if ($_SESSION['empl']['tipelokasitugas']=="HOLDING") {
			if(($param['tgl']!='')&&($param['tgl2']!='')){
				if(tanggalsystem($param['tgl'])>tanggalsystem($param['tgl2'])){
					exit('warning:'.$_SESSION['lang']['cek'].' '.$_SESSION['lang']['tanggal']);
				}
				$where.=" where tanggal between '".tanggalsystemn($param['tgl'])."' and '".tanggalsystemn($param['tgl2'])."'";
			}elseif($param['tgl']!=''){
				$where.=" where tanggal='".tanggalsystemn($param['tgl'])."'";
			}
		}else{
			$where= " where unit = '".$_SESSION['empl']['lokasitugas']."' ";
			if(($param['tgl']!='')&&($param['tgl2']!='')){
				if(tanggalsystem($param['tgl'])>tanggalsystem($param['tgl2'])){
					exit('warning:'.$_SESSION['lang']['cek'].' '.$_SESSION['lang']['tanggal']);
				}
				$where.=" and tanggal between '".tanggalsystemn($param['tgl'])."' and '".tanggalsystemn($param['tgl2'])."'";
			}elseif($param['tgl']!=''){
				$where.=" and tanggal='".tanggalsystemn($param['tgl'])."'";
			}
		}
		
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
        
		$no=0;
		$str="select tanggal,nilai,left(parameter,1) as jenis,updateby,station from ".$dbname.".pabrik_mr_roa  ".$where." order by tanggal desc,station";
		$dres=fetchData($str);
		foreach($dres as $row=>$lstData){
			$arrdata[$lstData['tanggal']][$lstData['station']]=$lstData['station'];
			@$nilArr[$lstData['tanggal'].$lstData['jenis'].$lstData['station']]+=$lstData['nilai'];
			$upArr[$lstData['tanggal']]=$lstData['updateby'];
			if(@$tmpD!=@$lstData['jenis']){
				$tmpD=$lstData['jenis'];
				@$totRowDt[$lstData['jenis']]+=1;	
			}
			
		}
		
		$jlhbrs=count($dres);
		$tab="";
		if($jlhbrs==0){
			$tab.="<tr class=rowcontent><td colspan='7' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$batasatas = (($page+1)*$limit);
			$batasbawah = ($batasatas-$limit);
			// echo $batasatas."__".$batasbawah;
			
			foreach($arrdata as $tgl => $arrstion){
				foreach ($arrstion as $sttn) {
					$no+=1;
					// echo "<br>".$no;
					if($no <= $batasatas && $no > $batasbawah){
						$optNmKary=array();
						if(intval($upArr[$tgl])!=0){
							$whr="karyawanid='".$upArr[$tgl]."'";
							$optNmKary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
						}
						$tab.="<tr class=rowcontent>";
						$tab.="<td align=center>".$no."</td>";
						$tab.="<td align=center>".tanggalnormal($tgl)."</td>";
						$tab.="<td align=center>".getNamaOrg($sttn)."</td>";
						foreach($lstKode as $dtKode){
							$tab.="<td align=right>".@$nilArr[$tgl.$dtKode.$sttn]."</td>";	
						}
						$tab.="<td align=left>".$optNmKary[$upArr[$tgl]]."</td>";
						if(@$periodeAkun[substr($tgl,0,7)]==0){//klo dah tutup buku kagak bisa dihapus
							$tab.="<td align=center width=25px>";
							$tab.="<img src=images/application/application_delete.png class=zImgBtn title=\"Delete ".$_SESSION['lang']['all']."\"  onclick=\"deletehead('".$tgl."','".$sttn."');\"></td>";	
						}else{
							$tab.="<td align=center width=25px></td>";
						}
						$tab.="<td align=center width=25px>";
						$tab.="<img src=images/application/application_edit.png class=zImgBtn title='Detail' 
								onclick=\"detaildt('".$_SESSION['lang']['detail']."','".$tgl."','".$sttn."');\"></td>";
						$tab.="</tr>";
					}
				}
			}
		}
		
		## PAGING
		$tab.=createpaging($jlhbrs,$limit,$page,'6','loadData','getPage');
		$tab.="</table>";
        echo $tab;
        break;
		
		case'getTable':
			$sDet="select * from ".$dbname.".pabrik_5mr_roa_parameter where left(parameter,1)='".$param['jenis']."'";
			$qDet=fetchData($sDet);
			$totRow=count($qDet);
			$tab.="<table cellpadding=0 cellpadding=0>";
			//$max=2;
			foreach($qDet as $row=>$lstDet){
				$tab.="<tr><td>".$lstDet['nama']."</td><td>:</td>";
			    $tab.="<td><input type=text id='nil_".$row."' class=myinputtextnumber onkeypress='return angka_doang(event)' />
			    	   <input type=hidden id='param_".$row."' value='".$lstDet['parameter']."' />
			           </td></tr>";	
				// if($max==2){
				// 	$tab.="<tr><td>".$lstDet['nama']."</td><td>:</td>";
				// 	$tab.="<td><input type=text id='".$lstDet['nama']."' class=myinputtextnumber onkeypress='return angka_doang(event)' /></td></tr>";	
				// 	//$max-=1;
				// }else{
				// 	$tab.="<tr><td>".$lstDet['nama']."</td><td>:</td>";
				// 	$tab.="<td><input type=text id='".$lstDet['nama']."' class=myinputtextnumber onkeypress='return angka_doang(event)' /></td></tr>";	
				// 	//$max=2;
				// }
			}
			$tab.="</table><input type=hidden id=totRow value='".$totRow."' />";
			echo $tab;
		break;
	case'saveDt':
			if($param['tanggal']==''){
				exit('warning:'.$_SESSION['lang']['tanggal'].' '.$_SESSION['lang']['kosong']);
			}	
			if($param['jenis']==''){
				exit('warning:'.$_SESSION['lang']['jenis'].' '.$_SESSION['lang']['kosong']);
			}	
			if($_SESSION['empl']['tipelokasitugas']!='PABRIK'){
				exit("warning: ".$_SESSION['lang']['cek'].' '.$_SESSION['lang']['tipe'].' '.$_SESSION['lang']['lokasitugas']);
			}
			#cek apakah sudah ada atau belum
			$rowAda=0;
			for($awe=0;$awe<$param['totRow'];$awe++){
				$sCek="select * from ".$dbname.".pabrik_mr_roa where unit='".$_SESSION['empl']['lokasitugas']."' 
				       and parameter='".$param['paramDt'][$awe]."' and tanggal='".tanggalsystemn($param['tanggal'])."' and station='{$param['station']}'";
				$rCek=fetchData($sCek);
				$rowAda+=count($rCek);
			}
			$optJenis=makeOption($dbname,'pabrik_5mr_roa_jenis','jenis,nama');

			if($rowAda!=0){
				exit('warning: Tanggal '.$param['tanggal'].' di Station '.trim(getNamaOrg($param['station'])).' dengan jenis '.$optJenis[substr($param['paramDt'][0],0,1)].' '.$_SESSION['lang']['exist']);
			}

			$sins="insert into ".$dbname.".`pabrik_mr_roa` (`unit`,`station`,`tanggal`,`parameter`,`nilai`,`updateby`) values";
			for($awe=0;$awe<$param['totRow'];$awe++){
				if($awe==0){
					$sins.=" ('".$_SESSION['empl']['lokasitugas']."','".$param['station']."','".tanggalsystemn($param['tanggal'])."','".$param['paramDt'][$awe]."','".floatval($param['nilai'][$awe])."','".$_SESSION['standard']['userid']."')";
				}else{
					$sins.=" ,('".$_SESSION['empl']['lokasitugas']."','".$param['station']."','".tanggalsystemn($param['tanggal'])."','".$param['paramDt'][$awe]."','".floatval($param['nilai'][$awe])."','".$_SESSION['standard']['userid']."')";
				}
				
			}
			
			try {
				$owlPDO->exec($sins);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>".$sins; 
				die(); 
			}
	break;
    
    case'deletehead':
    	$sDel="delete from ".$dbname.".pabrik_mr_roa where tanggal='".$param['tanggal']."' and station='".$param['station']."' and unit='".$_SESSION['empl']['lokasitugas']."'";
    	try{
			$owlPDO->exec($sDel); 
		}catch (PDOException $e){
			exit("error: db error".$e->getMessage()."___".$sUpd);
			die();
		}
    break;
    
	case'htmlDetail':
		#ambil periode akuntansi
		$sAkuntansi="select periode,tutupbuku from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		$rAkuntansi=fetchData($sAkuntansi);
		foreach ($rAkuntansi as $key => $value) {
			$periodeAkun[$value['periode']]=$value['tutupbuku'];
		}
		$wher="";
		if(@$param['jenis']!=''){
			$wher="where left(parameter,1)='".$param['jenis']."'";
		}
		$shead="select * from ".$dbname.".pabrik_5mr_roa_jenis";
		$rhead=fetchData($shead);
		foreach ($rhead as $key => $val) {
			$nmJudul[$val['jenis']]=$val['nama'];
		}
		$sDet="select * from ".$dbname.".pabrik_5mr_roa_parameter ";
		$qDet=fetchData($sDet);

		#ambil nilai dari transaksi
		$sTrans="select * from ".$dbname.".pabrik_mr_roa where tanggal='".$param['tgl']."' and station='".$param['station']."' and unit='".$_SESSION['empl']['lokasitugas']."'";
		$rTrans=fetchData($sTrans);
		foreach ($rTrans as $key => $val) {
			$dtNilai[$val['parameter']]=$val['nilai'];
		}
		$tanggal=tanggalnormal($param['tgl']);
		$totRow=count($qDet);
		$stForm="";
		if(@$periodeAkun[substr($param['tanggal'],0,7)]!=0){
			$stForm="disabled=disabled";
		}
			$tab.="<table cellpadding=2 cellspacing=1 border=0 class=sortable width=100%>";
			$tab.="<tr class=rowcontent><td>".$_SESSION['lang']['tanggal']."</td><td>:</td>";
		    $tab.="<td><input type=text id=tgl2 class=myinputtext disabled=disabled value='".$tanggal."' />
		           </td></tr>";	
			$tab.="<tr class=rowcontent><td>".$_SESSION['lang']['station']."</td><td>:</td>";
		    $tab.="<td>".getNamaOrg($param['station'])."<input id=station2 hidden value='".$param['station']."'></td></tr>";	
			//$max=2;
			foreach($qDet as $row=>$lstDet){
				if(@$tempData!=substr($lstDet['parameter'],0,1)){
					$tempData=substr($lstDet['parameter'],0,1);
					$tab.="<tr class=rowcontent>";
					$tab.="<td colspan=3><b>".$nmJudul[substr($lstDet['parameter'],0,1)]."</b></td></tr>";
				}
				$tab.="<tr class=rowcontent><td>".$lstDet['nama']."</td><td>:</td>";
			    $tab.="<td><input onclick='this.select()' type=text id='nil_".$row."' class=myinputtextnumber onkeypress='return angka_doang(event)' value='".@$dtNilai[$lstDet['parameter']]."' ".$stForm." />
			    	   <input type=hidden id='param_".$row."' value='".$lstDet['parameter']."' />
			           </td></tr>";	
				// if($max==2){
				// 	$tab.="<tr><td>".$lstDet['nama']."</td><td>:</td>";
				// 	$tab.="<td><input type=text id='".$lstDet['nama']."' class=myinputtextnumber onkeypress='return angka_doang(event)' /></td></tr>";	
				// 	//$max-=1;
				// }else{
				// 	$tab.="<tr><td>".$lstDet['nama']."</td><td>:</td>";
				// 	$tab.="<td><input type=text id='".$lstDet['nama']."' class=myinputtextnumber onkeypress='return angka_doang(event)' /></td></tr>";	
				// 	//$max=2;
				// }
			}
			$tab.="<tr class=rowcontent><td colspan=2>&nbsp;</td><td><button class=mybutton id=dtlAbn  ".$stForm." onclick=upDt()>".$_SESSION['lang']['save']."</button>
                        <button class=mybutton id=cancelAbn onclick=displayList()>".$_SESSION['lang']['done']."</button></td></tr>";
			$tab.="</table><input type=hidden id=totRow2 value='".$totRow."' />";
		echo $tab;
		break;
		case'update':
			$sDel="delete from ".$dbname.".pabrik_mr_roa where unit='".$_SESSION['empl']['lokasitugas']."' and station='".$param['station']."' and tanggal='".tanggalsystemn($param['tanggal'])."'";
			try {
				$owlPDO->exec($sDel);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>".$sDel; 
				die(); 
			}
			$sins="insert into ".$dbname.".`pabrik_mr_roa` (`unit`,`station`,`tanggal`,`parameter`,`nilai`,`updateby`) values";
			for($awe=0;$awe<$param['totRow'];$awe++){
				if($awe==0){
					$sins.=" ('".$_SESSION['empl']['lokasitugas']."','".$param['station']."','".tanggalsystemn($param['tanggal'])."','".$param['paramDt'][$awe]."','".floatval($param['nilai'][$awe])."','".$_SESSION['standard']['userid']."')";
				}else{
					$sins.=" ,('".$_SESSION['empl']['lokasitugas']."','".$param['station']."','".tanggalsystemn($param['tanggal'])."','".$param['paramDt'][$awe]."','".floatval($param['nilai'][$awe])."','".$_SESSION['standard']['userid']."')";
				}
				
			}
			try {
				$owlPDO->exec($sins);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>".$sins; 
				die(); 
			}
		break;
		 
    default:
        break;
}