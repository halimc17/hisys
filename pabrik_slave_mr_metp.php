<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param=$_POST;

switch($param['proses']) {
    case'loadNewData':
    	$sParam="select * from ".$dbname.".pabrik_5mr_metp ";
        $rParam=fetchData($sParam);
        foreach($rParam as $row=>$lstParam){
            $lstKode[$lstParam['kode']]=$lstParam['kode'];
        }
		#ambil periode akuntansi
		$sAkuntansi="select periode,tutupbuku from ".$dbname.".setup_periodeakuntansi where kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		$rAkuntansi=fetchData($sAkuntansi);
		foreach ($rAkuntansi as $key => $value) {
			$periodeAkun[$value['periode']]=$value['tutupbuku'];
		}
 
		if(($param['tgl']!='')&&($param['tgl2']!='')){
			if(tanggalsystem($param['tgl'])>tanggalsystem($param['tgl2'])){
				exit('warning:'.$_SESSION['lang']['cek'].' '.$_SESSION['lang']['tanggal']);
			}
			$where.=" and tanggal between '".tanggalsystemn($param['tgl'])."' and '".tanggalsystemn($param['tgl2'])."'";
		}elseif($param['tgl']!=''){
			$where.=" and tanggal='".tanggalsystemn($param['tgl'])."'";
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
        $str="select distinct tanggal  from ".$dbname.".pabrik_mr_metp where unit = '".$_SESSION['empl']['lokasitugas']."' ".$where." 
              order by tanggal desc ";
		$res=fetchdata($str);
		//$jlhbrs=owlBaris($res);	
		$jlhbrs=count($res);
		if($jlhbrs==0){
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=15>".$_SESSION['lang']['dataempty']."</td>";
			$tab.="</tr>";
		}else{
			$no=0;
			$no=$maxdisplay;
			$str="select distinct tanggal from ".$dbname.".pabrik_mr_metp where unit = '".$_SESSION['empl']['lokasitugas']."' ".$where." 
                  order by tanggal desc limit ".$offset.",".$limit."";
            $dres=fetchData($str);
            foreach($dres as $row=>$lstData){
            	$tglArr[$lstData['tanggal']]=$lstData['tanggal'];
            	
            }

	        $str="select * from ".$dbname.".pabrik_mr_metp where unit = '".$_SESSION['empl']['lokasitugas']."' ".$where." 
                  order by tanggal";
            $dres=fetchData($str);
            foreach($dres as $row=>$lstData){
            	$tglArr[$lstData['tanggal']]=$lstData['tanggal'];
            	$nilArr[$lstData['tanggal'].$lstData['kode']]+=$lstData['nilai'];
            	$upArr[$lstData['tanggal']]=$lstData['updateby'];
            	if($tmpD!=$lstData['kode']){
            		$tmpD=$lstData['kode'];
            		$totRowDt[$lstData['kode']]+=1;	
            	}
            	
            }
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
	           		$tab.="<td align=right>".$nilArr[$tgl.$dtKode]."</td>";	
	           	}
				//$tab.="<td align=left>".$optNmKary[$upArr[$tgl]]."</td>";
	            if($periodeAkun[substr($tgl,0,7)]==0){//klo dah tutup buku kagak bisa dihapus
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
	            <tr><td colspan=16 align=center>
	            <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
	            <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	            </td>
	            </tr>";
		}
	 
        
        echo $tab."####".$footd;
        break;
		case'getTable':
			$sDet="select * from ".$dbname.".pabrik_5mr_metp ";
			$qDet=fetchData($sDet);
			$totRow=count($qDet);
			$tab.="<table cellpadding=0 cellpadding=0>";
			$max=2;
			foreach($qDet as $row=>$lstDet){
				// $tab.="<tr><td>".$lstDet['nama']."</td><td>:</td>";
			 //    $tab.="<td><input type=text id='nil_".$row."' class=myinputtextnumber onkeypress='return angka_doang(event)' />
			 //    	   <input type=hidden id='param_".$row."' value='".$lstDet['kode']."' />
			 //           </td></tr>";	
				if($max==2){
					$tab.="<tr><td style=width:100px>".$lstDet['nama']."</td><td>:</td>";
					$tab.="<td><input type=text id='nil_".$row."' class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:150px;\"  />
					           <input type=hidden id='param_".$row."' value='".$lstDet['kode']."' /></td>";	
					$max-=1;
				}else{
					$tab.="<td>&nbsp;</td><td>".$lstDet['nama']."</td><td>:</td>";
					$tab.="<td><input type=text id='nil_".$row."' class=myinputtextnumber onkeypress='return angka_doang(event)' style=\"width:150px;\"  />
					           <input type=hidden id='param_".$row."' value='".$lstDet['kode']."' /></td></tr>";	
					$max=2;
				}
			}
			$tab.="</table><input type=hidden id=totRow value='".$totRow."' />";
			echo $tab;
		break;
	case'saveDt':
			if($param['tanggal']==''){
				exit('warning:'.$_SESSION['lang']['tanggal'].' '.$_SESSION['lang']['kosong']);
			}		
			if($_SESSION['empl']['tipelokasitugas']!='PABRIK'){
				exit("warning: ".$_SESSION['lang']['cek'].' '.$_SESSION['lang']['tipe'].' '.$_SESSION['lang']['lokasitugas']);
			}
			#cek apakah sudah ada atau belum
			$rowAda=0;
			for($awe=0;$awe<$param['totRow'];$awe++){
				$sCek="select * from ".$dbname.".pabrik_mr_metp where unit='".$_SESSION['empl']['lokasitugas']."' 
				       and kode='".$param['paramDt'][$awe]."' and tanggal='".tanggalsystemn($param['tanggal'])."'";
				$rCek=fetchData($sCek);
				$rowAda+=count($rCek);
			}
			$optJenis=makeOption($dbname,'pabrik_5mr_metp','kode,nama');

			if($rowAda!=0){
				exit('warning:'.$param['tanggal'].' '.$optJenis[$param['kode'][0]].' '.$_SESSION['lang']['exist']);
			}

			$sins="INSERT INTO ".$dbname.".`pabrik_mr_metp` (`unit`,`tanggal`,`kode`,`nilai`,`updateby`) values";
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
    	$sDel="delete from ".$dbname.".pabrik_mr_metp where tanggal='".$param['tanggal']."' and unit='".$_SESSION['empl']['lokasitugas']."'";
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
		$sCek="select * from ".$dbname.".pabrik_mr_metp where unit='".$_SESSION['empl']['lokasitugas']."' and tanggal='".$param['tgl']."'";
		$rCek=fetchData($sCek);
		foreach($rCek as $row=>$nilDt){
			$isNil[$nilDt['kode']]=$nilDt['nilai'];
			$tanggal=tanggalnormal($nilDt['tanggal']);
			$tgl=$nilDt['tanggal'];
		}
		$tab.="<table cellspacing='1' cellpadding=1 border='0' class='sortable'>";
		$sDet="select * from ".$dbname.".pabrik_5mr_metp ";
		$qDet=fetchData($sDet);
		$totRow=count($qDet);
		$max=2;
		$stForm="";
		if($periodeAkun[substr($tgl, 0,7)]!=0){
			$stForm="disabled=disabled";
		}
		$tab.="<tr class=rowcontent><td>".$_SESSION['lang']['tanggal']."</td>
                <td>:</td>
                <td align=left><input type=text class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' style=\"width:150px;\" value='".$tanggal."' readonly /></td>
                <td colspan=4>&nbsp;</td></tr>";
		foreach($qDet as $row=>$lstDet){
			// $tab.="<tr><td>".$lstDet['nama']."</td><td>:</td>";
		 //    $tab.="<td><input type=text id='nil_".$row."' class=myinputtextnumber onkeypress='return angka_doang(event)' />
		 //    	   <input type=hidden id='param_".$row."' value='".$lstDet['kode']."' />
		 //           </td></tr>";	
			if($max==2){
				$tab.="<tr class=rowcontent><td>".$lstDet['nama']."</td><td>:</td>";
				$tab.="<td><input type=text id='nil_".$row."' class=myinputtextnumber onkeypress='return angka_doang(event)'  style=\"width:150px;\" value='".$isNil[$lstDet['kode']]."' ".$stForm."  />
				           <input type=hidden id='param_".$row."' value='".$lstDet['kode']."' /></td>";	
				$max-=1;
			}else{
				$tab.="<td>&nbsp;</td><td>".$lstDet['nama']."</td><td>:</td>";
				$tab.="<td><input type=text id='nil_".$row."' class=myinputtextnumber onkeypress='return angka_doang(event)'  style=\"width:150px;\" value='".$isNil[$lstDet['kode']]."'  ".$stForm."  />
				           <input type=hidden id='param_".$row."' value='".$lstDet['kode']."' /></td></tr>";	
				$max=2;
			}
		}
		$tab.="<tr class=rowcontent><td colspan=7 ><button class=mybutton id=dtlAbn onclick=upDt()  ".$stForm.">".$_SESSION['lang']['save']."</button></td></tr>";
		$tab.="</table><input type=hidden id=totRow value='".$totRow."' />";
		echo $tab;
		break;
		case'update':
			for($awe=0;$awe<$param['totRow'];$awe++){
				$sDel="delete from ".$dbname.".`pabrik_mr_metp` where unit='".$_SESSION['empl']['lokasitugas']."' and tanggal='".tanggalsystemn($param['tanggal'])."' and kode='".$param['paramDt'][$awe]."'";
				try {
					$owlPDO->exec($sDel);
					$sins="insert into ".$dbname.".`pabrik_mr_metp` (`unit`,`tanggal`,`kode`,`nilai`,`updateby`) values 
					       ('".$_SESSION['empl']['lokasitugas']."','".tanggalsystemn($param['tanggal'])."','".$param['paramDt'][$awe]."','".floatval($param['nilai'][$awe])."','".$_SESSION['standard']['userid']."')";
					try{
						$owlPDO->exec($sins);
					} catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>".$sins; 
						die(); 
					}
				}  catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "<br/>".$sDel; 
						die(); 
				}
			}
		break;
		
}