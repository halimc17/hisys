<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param=$_POST;

switch($param['proses']) {
    case'loadNewData':
		#cek jabatan
		$sCkJbtn="select count(jabatan) as itung from ".$dbname.".setup_posting where jabatan='".$_SESSION['empl']['kodejabatan']."' and kodeaplikasi='keuangan'";
		$qCkJbtn=$owlPDO->query($sCkJbtn) or die(print " Gagal: ".PDOException::getMessage());
		$qCkJbtn->setFetchMode(PDO::FETCH_ASSOC);
		$rCkJbtn=$qCkJbtn->fetch();

		if($param['jenis']!=''){
			$where.=" and jenis='".$param['jenis']."'";
		}
		if($param['tgl']!=''){
			$where.=" and tanggal='".tanggalsystemn($param['tgl'])."'";
		}
		if($param['nokontrak']!=''){
			$where.=" and nokontrak like '%".$param['nokontrak']."%'";
		}
		if($param['notransaksi']!=''){
			$where.=" and nokirim like '%".$param['notransaksi']."%'";
		}
		if($param['noba']!=''){
			$where.=" and noba like '%".$param['noba']."%'";
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
        $str="select * from ".$dbname.".pabrik_blk_kirimht where millcode = '".$_SESSION['empl']['lokasitugas']."' ".$where." 
              order by tanggal desc ";
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
	        $str="SELECT * from ".$dbname.".pabrik_blk_kirimht where millcode = '".$_SESSION['empl']['lokasitugas']."' ".$where." 
	              order by tanggal desc   limit ".$offset.",".$limit."";
	        $tab="";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
	            $no+=1;
	            $optNmKary=array();
	            $optNmKary2=array();
	            if(intval($bar['updateby'])!=0){
	            	$whr="karyawanid='".$bar['updateby']."'";
	            	$optNmKary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
	            }
	            if(intval($bar['postingby'])!=0){
	            	$whr="karyawanid='".$bar['postingby']."'";
	            	$optNmKary2=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whr);
	            }
	            $tab.="<tr class=rowcontent>";
	            $tab.="<td align=right>".$no."</td>";
	            $tab.="<td>".$bar['nokirim']."</td>";
	            //$tab.="<td align=left>".$bar['kodeunit']."</td>";
	            $tab.="<td>".$bar['jenis']."</td>";
				$tab.="<td>".$bar['tanggal']."</td>";
				$tab.="<td>".$bar['nokontrak']."</td>";
				$tab.="<td>".$bar['noba']."</td>";
				$tab.="<td>".$bar['lokasi']."</td>";
				$tab.="<td align=left>".$optNmKary[$bar['updateby']]."</td>";
				//$tab.="<td align=left>".$optNmKary2[$bar['postingby']]."</td>";
	            $tab.="
	            <td align=center>";
				if($bar['posting']==1){
					$postdt=" src=images/skyblue/posted.png class=zImgOffBtn title='Posted' ";          
				}
				else{
					if($rCkJbtn['itung']==1){
						$postdt=" src=images/skyblue/posting.png style='cursor:pointer;' title='Posting' onclick=\"posting('".$bar['nokirim']."','".tanggalnormal($bar['tanggal'])."','".$_SESSION['lang']['notifandayakin']."');\"";
					}
				}
				$tab.="
					<img src=images/application/application_edit.png class=zImgBtn title='Edit' 
	                onclick=\"edit('".$bar['nokirim']."','".$bar['millcode']."','".$bar['jenis']."','".tanggalnormal($bar['tanggal'])."','".$bar['nokontrak']."','".$bar['noba']."','".$bar['lokasi']."');\">
	                <img src=images/application/application_delete.png class=zImgBtn title='Delete' 
	                     onclick=\"deletehead('".$bar['nokirim']."');\">
	                <img src=images/skyblue/zoom.png class=zImgBtn title='Detail' 
	                     onclick=\"detaildt('".$_SESSION['lang']['detail']."','".$bar['nokirim']."');\">         
	                <img  ".$postdt."  class=zImgBtn >";
	            $tab.="</td>";
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
	            <tr><td colspan=9 align=center>
	            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
	            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	            </td>
	            </tr>";
		}
	 
        
        echo $tab."####".$footd;
        break;
	
    
    case'deletehead':
    	$sDel="delete from ".$dbname.".pabrik_blk_kirimht where nokirim='".$param['notransaksi']."'";
    	try{
			$owlPDO->exec($sDel); 
		}catch (PDOException $e){
			exit("error: db error".$e->getMessage()."___".$sUpd);
			die();
		}
    break;
	
     
	
     
    
    case'delDetail':
		$sDel="delete from ".$dbname.".pabrik_blk_kirimdt 
		       where nokirim='".$param['notransaksi']."' and notransaksi='".$param['noTiket']."'";
		try{
			$owlPDO->exec($sDel); 
		}catch (PDOException $e){
			echo "DB Error : ".$e->getMessage();
			die();
		}			
		break;
	
    case'createTable':
    	if($param['notransaksi']==''){
    		//tanggal/millcode/jenis/nourut
    		#2016/10/BP1M/BA/00001
    		$sTran="select nokirim from ".$dbname.".pabrik_blk_kirimht where millcode='".$_SESSION['empl']['lokasitugas']."'
    		        and jenis='".$param['jenis']."'
    		        order by nokirim desc limit 1";
    		$rTran=fetchdata($sTran);
    		$nod=explode("/",$rTran[0]['nokirim']);
    		$nourut=intval($nod[4]);
    		$thnBerJln=date('Y');
    		if($thnBerJln!=$nod[0]){
    			$nourut=1;
    		}else{
    			$nourut=$nourut+1;
    		}
    		$nourut=addZero($nourut,"5");
    		$notrans=$thnBerJln."/".date('m')."/".$_SESSION['empl']['lokasitugas']."/".$param['jenis']."/".$nourut;
    		//exit('warning'.$notrans);
    		if($param['jenis']==''){
		    	exit('warning: '.$_SESSION['lang']['jenis']." ".$_SESSION['lang']['kosong']);
		    }
		    if($param['tgl']==''){
		    	exit('warning: '.$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['kosong']);
		    }
		    if($param['tgl']==''){
		    	exit('warning: '.$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['kosong']);
		    }
		    if($param['nokontrak']==''){
		    	exit('warning: '.$_SESSION['lang']['NoKontrak']." ".$_SESSION['lang']['kosong']);
		    }
		    if($param['jenis']=='BA'){
		    	if($param['noba']==''){
		    		exit('warning: '.$_SESSION['lang']['noberitaacara']." ".$_SESSION['lang']['kosong']);
		    	}	
		    }
    		$sIns="insert into ".$dbname.".pabrik_blk_kirimht (`nokirim`,`tanggal`,`nokontrak`,`noba`,`jenis`,`lokasi`,`millcode`,`updateby`)
    		       values ('".$notrans."','".tanggalsystemn($param['tgl'])."','".$param['nokontrak']."','".$param['noba']."','".$param['jenis']."','".$param['lokasi']."','".$param['kdOrg']."','".$_SESSION['standard']['userid']."') ";
    		try{
				$owlPDO->exec($sIns); 
			}catch (PDOException $e){
				exit("error: DB Error ".$e->getMessage()."___".$sIns);
				die();
			}
    	}else{
    		$notrans=$param['notransaksi'];
    	}
		
		  
	   	$optData="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	   	$arrOpt=array("40000001"=>"CPO","40000002"=>"KER");
	   	foreach($arrOpt as $row=>$isiDt){
	   		$optData.="<option value='".$row."'>".$isiDt."</option>";
	   	}
        $whrDt="millcode='".$_SESSION['empl']['lokasitugas']."' and left(tanggal,10)='".tanggalsystemn($param['tgl'])."' 
				and kodebarang in ('40000001','40000002') and (norefrensi is null or norefrensi='')";
        $sData="select notransaksi from ".$dbname.".pabrik_timbangan where ".$whrDt."";
		//exit("Error:$sData");
        $rData=fetchdata($sData);
		$optTkt.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        foreach($rData as $row){
        	$sCek="select * from ".$dbname.".pabrik_blk_kirimdt where notransaksi='".$row['notransaksi']."'";
        	$rCek=fetchdata($sCek);
        	if(count($rCek)==0){
        		$optTkt.="<option value='".$row['notransaksi']."'>".$row['notransaksi']."</option>";	
        	}
        }
		$optKry=makeOption($dbname,'pabrik_timbangan','notransaksi,notransaksi',$whrDt);
		$table="<table id='ppDetailTable' cellspacing='1' border='0' class='sortable' width=100%>
		<thead>
		<tr class=rowheader>
		<td align=center>".$_SESSION['lang']['noTiket']."</td>
		<td align=center>".$_SESSION['lang']['komoditi']."</td>
		<td align=center>".$_SESSION['lang']['beratBersih']."</td>
		<td width=50px>Action</td>
		</tr></thead>
		<tbody id='detailBody'>";
		$table.="<tr class=rowcontent>
		<td><select id=noTiket name=noTiket  style='width:150px' onchange=getData()>".$optTkt."</select>
		<img id='noTiket' onclick=z.elSearch('noTiket',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
		</td>
		<td align=center><select id=komoditiId style='width:120px'  disabled=disabled>".$optData."</select></td>
		<td align=center><input type=text class=myinputtextnumber id=netto  style='width:120px'  onkeypress='return tanpa_kutip(event)'  readonly=readonly /></td>
		<td align=center><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"addDetail()\" src='images/save.png'/></td>
		</tr>
		";
		$table.="</tbody></table>";
		echo $notrans."####".$table;
		break;
	
    case'loadDetail':
		$sTgl="select tanggal from ".$dbname.".pabrik_blk_kirimht where nokirim='".$param['notransaksi']."' ";
		$rTgl=fetchData($sTgl);

		$sDet="select * from ".$dbname.".pabrik_blk_kirimdt where nokirim='".$param['notransaksi']."' ";
        $qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
		$qDet->setFetchMode(PDO::FETCH_ASSOC);
		$tot=0;

		while($rDet=$qDet->fetch()){
			$no+=1;
			$whr="kodebarang='".$rDet['kodebarang']."'";
			$optNm=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whr);
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=right>".$no."</td>";
			$tab.="<td>".$rDet['notransaksi']."</td>";
			$tab.="<td>".$optNm[$rDet['kodebarang']]."</td>";
			$tab.="<td align=right>".number_format($rDet['beratbersih'],0)."</td>";
			$tab.="<td align=center> 
				<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delDetail('".$rDet['nokirim']."','".$rDet['notransaksi']."','".tanggalnormal($rTgl[0]['tanggal'])."');\" >	</td>";
			$tab.="</tr>";
			$tot+=$rDet['beratbersih'];
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td colspan=3 align=center><b>".$_SESSION['lang']['total']."</b></td>";
		$tab.="<td align=right><b>".number_format($tot,0)."</b></td><td>&nbsp;</td></tr>";
		echo $tab;
		break;
	case'ambilDt':
		$sGet="select kodebarang,beratbersih from ".$dbname.".pabrik_timbangan 
		       where millcode='".$_SESSION['empl']['lokasitugas']."' and notransaksi='".$param['notransaksi']."'";
		$rGet=fetchdata($sGet);
		echo $rGet[0]['kodebarang']."####".number_format($rGet[0]['beratbersih']);		       
	break;
	case'saveData':
		if($param['notransaksi']==''){
			exit('warning:'.$_SESSION['lang']['notransaksi']." ".$_SESSION['lang']['notifobligatory']);
		}
		if($param['noTiket']==''){
			exit('warning:'.$_SESSION['lang']['noTiket']." ".$_SESSION['lang']['notifobligatory']);
		}
		if($param['komoditiId']==''){
			exit('warning:'.$_SESSION['lang']['komoditi']." ".$_SESSION['lang']['notifobligatory']);
		}
		if(($param['netto']=='')||($param['netto']=='0')){
			exit('warning:'.$_SESSION['lang']['netto']." ".$_SESSION['lang']['notifemptyzero']);
		}
		$param['netto']=str_replace(",", "", $param['netto']);
		$sIns="insert into ".$dbname.".pabrik_blk_kirimdt (`nokirim`,`notransaksi`,`kodebarang`,`beratbersih`)";
		$sIns.=" values ('".$param['notransaksi']."','".$param['noTiket']."','".$param['komoditiId']."','".$param['netto']."')";
		try{
			$owlPDO->exec($sIns); 
		}catch (PDOException $e){
			exit("error: DB Error ".$e->getMessage()."___".$sIns);
			die();
		}
	break;
	case'htmlDetail':
		$sHead="select * from ".$dbname.".pabrik_blk_kirimht where nokirim='".$param['notransaksi']."'";
		$rHead=fetchdata($sHead);
		$tab.="<table cellpadding=1 cellspacing=1 border=0>";
		$tab.="<tr><td>".$_SESSION['lang']['notransaksi']."</td>";
		$tab.="<td>:</td><td>".$rHead[0]['nokirim']."</td></tr>";
		$tab.="<tr><td>".$_SESSION['lang']['tanggal']."</td>";
		$tab.="<td>:</td><td>".tanggalnormal($rHead[0]['tanggal'])."</td></tr>";
		$tab.="<tr><td>".$_SESSION['lang']['NoKontrak']."</td>";
		$tab.="<td>:</td><td>".$rHead[0]['nokontrak']."</td></tr>";
		$tab.="<tr><td>".$_SESSION['lang']['noberitaacara']."</td>";
		$tab.="<td>:</td><td>".$rHead[0]['noba']."</td></tr>";
		$tab.="<tr><td>".$_SESSION['lang']['lokasi']."</td>";
		$tab.="<td>:</td><td>".$rHead[0]['lokasi']."</td></tr>";
		$tab.="</table>";
		

		$tab.="<table cellspacing='1' border='0' class='sortable' style='width:400px'>
                <thead>
                    <tr class=\"rowheader\">
                        <td align='center'>No.</td>
                        <td align='center'>".$_SESSION['lang']['noTiket']."</td>
                        <td align='center'>".$_SESSION['lang']['komoditi']."</td>
                        <td align='center'>".$_SESSION['lang']['beratBersih']."</td>
                    </tr>
                </thead>";
		$sDet="select * from ".$dbname.".pabrik_blk_kirimdt where nokirim='".$param['notransaksi']."' ";
        $qDet=$owlPDO->query($sDet) or die(print " Gagal: ".PDOException::getMessage());
		$qDet->setFetchMode(PDO::FETCH_ASSOC);
		$tot=0;

		while($rDet=$qDet->fetch()){
			$no+=1;
			$whr="kodebarang='".$rDet['kodebarang']."'";
			$optNm=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whr);
			$tab.="<tr class=rowcontent>";
			$tab.="<td align=right>".$no."</td>";
			$tab.="<td>".$rDet['notransaksi']."</td>";
			$tab.="<td>".$optNm[$rDet['kodebarang']]."</td>";
			$tab.="<td align=right>".number_format($rDet['beratbersih'],0)."</td>";
			$tab.="</tr>";
			$tot+=$rDet['beratbersih'];
		}
		$tab.="<tr class=rowcontent>";
		$tab.="<td colspan=3 align=center><b>".$_SESSION['lang']['total']."</b></td>";
		$tab.="<td align=right><b>".number_format($tot,0)."</b></td></tr>";
		echo $tab;
		break;
		case'postData':
			$sCek="select * from ".$dbname.".pabrik_blk_kirimdt where nokirim='".$param['notransaksi']."'";
			$rCek=fetchdata($sCek);
			if(count($rCek)==0){
				exit('warning:'.$_SESSION['lang']['detail']." ".$_SESSION['lang']['dataempty']);
			} 
			$sUpd="update ".$dbname.".pabrik_blk_kirimht set posting=1,postingby='".$_SESSION['standard']['userid']."'  where nokirim='".$param['notransaksi']."'";
			try{
				$owlPDO->exec($sUpd); 
			}catch (PDOException $e){
				exit("error: DB Error ".$e->getMessage()."___".$sUpd);
				die();
			}
		break;
    default:
        break;
}