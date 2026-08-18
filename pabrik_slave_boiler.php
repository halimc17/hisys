<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param=$_POST;

switch($param['proses']) {
    case'loadNewData':
    	$sParam="select * from ".$dbname.".pabrik_5bfwtjenis ";
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

        $limit=40;
        $page=0;
        if(isset($_POST['page']))
        {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select tanggal,sum(nilai) as nilai,left(parameter,1) as jenis from ".$dbname.".pabrik_bfwt ".$where." 
              group by tanggal,left(parameter,1) order by tanggal desc ";
		$res=fetchdata($str);
		//$jlhbrs=owlBaris($res);	
		$jlhbrs=count($res);
		if($jlhbrs==0){
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=12>".$_SESSION['lang']['dataempty']."</td>";
			$tab.="</tr>";
		}else{
			$no=0;
			$no=$maxdisplay;
	        $str="select tanggal,sum(nilai) as nilai,left(parameter,1) as jenis,updateby from ".$dbname.".pabrik_bfwt ".$where." 
                  group by tanggal,left(parameter,1) order by tanggal desc limit ".$offset.",".$limit."";
            $dres=fetchData($str);
            foreach($dres as $row=>$lstData){
            	$tglArr[$lstData['tanggal']]=$lstData['tanggal'];
            	@$nilArr[$lstData['tanggal'].$lstData['jenis']]+=$lstData['nilai'];
            	$upArr[$lstData['tanggal']]=$lstData['updateby'];
            	if(@$tmpD!=@$lstData['jenis']){
            		$tmpD=$lstData['jenis'];
            		@$totRowDt[$lstData['jenis']]+=1;	
            	}
            	
            }

            // echo "<pre>";
            // print_r($nilArr);
            // echo "</pre>";
	        $tab="";
			foreach($tglArr as $tgl){
	            $no+=1;
	            $optNmKary=array();
	            if(intval($upArr[$tgl])!=0){
	            	$whr="karyawanid='".$upArr[$tgl]."'";
	            	$optNmKary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
	            }
	            $tab.="<tr class=rowcontent>";
	            $tab.="<td align=center>".$no."</td>";
	            $tab.="<td align=center>".tanggalnormal($tgl)."</td>";
	           	foreach($lstKode as $dtKode){
	           		$tab.="<td align=right>".@$nilArr[$tgl.$dtKode]."</td>";	
	           	}
				$tab.="<td align=left>".$optNmKary[$upArr[$tgl]]."</td>";
	            if(@$periodeAkun[substr($tgl,0,7)]==0){//klo dah tutup buku kagak bisa dihapus
					$tab.="<td align=center width=25px>";
	            	$tab.="<img src=images/application/application_delete.png class=zImgBtn title=\"Delete ".$_SESSION['lang']['all']."\"  onclick=\"deletehead('".$tgl."');\"></td>";	
	          	}else{
					$tab.="<td align=center width=25px></td>";
				}
				$tab.="<td align=center width=25px>";
	            $tab.="<img src=images/skyblue/zoom.png class=zImgBtn title='Detail' 
	                     onclick=\"detaildt('".$_SESSION['lang']['detail']."','".$tgl."');\"></td>";
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
	            <tr><td colspan=8 align=center>
	            <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
	            <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	            </td>
	            </tr>";
		}
	 
        
        echo @$tab."####".@$footd;
        break;
		case'getTable':
			$sDet="select * from ".$dbname.".pabrik_5bfwtparameter where left(parameter,1)='".$param['jenis']."'";
			// exit("Error:$sDet");
			$qDet=fetchData($sDet);
			$totRow=count($qDet);
			$tab.="<table cellpadding=0 cellpadding=0>";
			//$max=2;
			foreach($qDet as $row=>$lstDet){
				$tab.="<tr><td width=50px>".$lstDet['nama']."</td><td>:</td>";
			    $tab.="<td><input type=text id='nil_".$row."' class=myinputtext onkeypress='return_tanpa_kutip(event)' />
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
				$sCek="select * from ".$dbname.".pabrik_bfwt where unit='".$_SESSION['empl']['lokasitugas']."' 
				       and parameter='".$param['paramDt'][$awe]."' and tanggal='".tanggalsystemn($param['tanggal'])."'";
				$rCek=fetchData($sCek);
				$rowAda+=count($rCek);
			}
			$optJenis=makeOption($dbname,'pabrik_5bfwtjenis','jenis,nama');

			if($rowAda!=0){
				exit('warning:'.$param['tanggal'].' '.$optJenis[substr($param['paramDt'][0],0,1)].' '.$_SESSION['lang']['exist']);
			}

			$sins="insert into ".$dbname.".`pabrik_bfwt` (`unit`,`tanggal`,`parameter`,`nilai`,`updateby`) values";
			for($awe=0;$awe<$param['totRow'];$awe++){
				if($awe==0){
					$sins.=" ('".$_SESSION['empl']['lokasitugas']."','".tanggalsystemn($param['tanggal'])."','".$param['paramDt'][$awe]."','".floatval($param['nilai'][$awe])."','".$_SESSION['standard']['userid']."')";
				}else{
					$sins.=" ,('".$_SESSION['empl']['lokasitugas']."','".tanggalsystemn($param['tanggal'])."','".$param['paramDt'][$awe]."','".floatval($param['nilai'][$awe])."','".$_SESSION['standard']['userid']."')";
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
    	$sDel="delete from ".$dbname.".pabrik_bfwt where tanggal='".$param['tanggal']."' and unit='".$_SESSION['empl']['lokasitugas']."'";
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
		$shead="select * from ".$dbname.".pabrik_5bfwtjenis";
		$rhead=fetchData($shead);
		foreach ($rhead as $key => $val) {
			$nmJudul[$val['jenis']]=$val['nama'];
		}
		$sDet="select * from ".$dbname.".pabrik_5bfwtparameter ";
		$qDet=fetchData($sDet);

		#ambil nilai dari transaksi
		$sTrans="select * from ".$dbname.".pabrik_bfwt where tanggal='".$param['tgl']."' and unit='".$_SESSION['empl']['lokasitugas']."'";
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
			$tab.="<table cellpadding=5 border=0>";
			$tab.="<tr><td>".$_SESSION['lang']['tanggal']."</td><td>:</td>";
		    $tab.="<td colspan=100><input type=text id=tgl2 class=myinputtext disabled=disabled value='".$tanggal."' />
		           </td></tr><tr>";	
			//$max=2;
			foreach($qDet as $row=>$lstDet){
				$d=substr($lstDet['parameter'],0,1);
				$dt[$d]=$d;
				$dtno[$d]+=1;
			}
			
			foreach($dt as $kddt){				
				$tab.="<td colspan=10 style=vertical-align:top;>";
				$tab.="<table cellpadding=5 cellspacing=1 border=0 class=sortable>";
				$tab.="<tr class=rowcontent>";
				$tab.="<td colspan=3><b>".$nmJudul[$kddt]."</b></td></tr>";
				foreach($qDet as $row=>$lstDet){
					$d=substr($lstDet['parameter'],0,1);
					if($d==$kddt){
						$tab.="<tr class=rowcontent><td>".$lstDet['nama']."</td><td>:</td>";
						$tab.="<td><input type=text id='nil_".$row."' class=myinputtextnumber onkeypress='return_tanpa_kutip(event)' value='".@$dtNilai[$lstDet['parameter']]."' ".$stForm." /><input type=hidden id='param_".$row."' value='".$lstDet['parameter']."' /></td></tr>";	
					}else{
					}
				}
				$tab.="</td>";
				$tab.="</table>"; //exit("error");
			}
			$tab.="</tr>";
			$tab.="<tr class=rowcontent>
				<td colspan=20 align=center><button class=mybutton id=dtlAbn  ".$stForm." onclick=upDt()>".$_SESSION['lang']['save']."</button>
				<button class=mybutton id=cancelAbn onclick=displayList()>".$_SESSION['lang']['done']."</button></td></tr>";
			$tab.="</table><input type=hidden id=totRow2 value='".$totRow."' />";
		echo $tab;
		break;
		case'update':
			$sDel="delete from ".$dbname.".pabrik_bfwt where unit='".$_SESSION['empl']['lokasitugas']."' and tanggal='".tanggalsystemn($param['tanggal'])."'";
			try {
				$owlPDO->exec($sDel);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>".$sDel; 
				die(); 
			}
			$sins="insert into ".$dbname.".`pabrik_bfwt` (`unit`,`tanggal`,`parameter`,`nilai`,`updateby`) values";
			for($awe=0;$awe<$param['totRow'];$awe++){
				if($awe==0){
					$sins.=" ('".$_SESSION['empl']['lokasitugas']."','".tanggalsystemn($param['tanggal'])."','".$param['paramDt'][$awe]."','".floatval($param['nilai'][$awe])."','".$_SESSION['standard']['userid']."')";
				}else{
					$sins.=" ,('".$_SESSION['empl']['lokasitugas']."','".tanggalsystemn($param['tanggal'])."','".$param['paramDt'][$awe]."','".floatval($param['nilai'][$awe])."','".$_SESSION['standard']['userid']."')";
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