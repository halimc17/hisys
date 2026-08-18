<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$param=$_POST;

switch($param['proses']) {
    case'loadNewData':
    	 
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
		#ambil kode barang
		$itemMat='';
		$sBrgWtp="select kodebarang from ".$dbname.".pabrik_5mr_material_usage where kd_transaksi='WTP'  order by kodebarang asc";
		$rBrgWtp=fetchData($sBrgWtp);
		foreach($rBrgWtp as $row=>$lstBrg){
			if($row==0){
				$itemMat="'".$lstBrg['kodebarang']."'";
			}else{
				$itemMat.=",'".$lstBrg['kodebarang']."'";
			}
			$whrBarang="kodebarang='".$lstBrg['kodebarang']."'";
			$namabarang=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whrBarang);
			@$dtBrg[$lstBrg['kodebarang']]=$lstBrg['kodebarang'];
			@$dtNmBrg[$lstBrg['kodebarang']]=$namabarang[$lstBrg['kodebarang']];
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
        $str="select * from ".$dbname.".pabrik_mr_wtp where unit = '".$_SESSION['empl']['lokasitugas']."' ".$where." 
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
	        


	        $str="select * from ".$dbname.".pabrik_mr_wtp where unit = '".$_SESSION['empl']['lokasitugas']."' ".$where." 
                  order by tanggal desc limit ".$offset.",".$limit."";
            $dres=fetchData($str);
            foreach($dres as $row=>$lstData){
            	$tglArr[$lstData['tanggal']]=$lstData['tanggal'];
            	$nilArr[$lstData['tanggal']]=$lstData['nilai'];
            	$upArr[$lstData['tanggal']]=$lstData['updateby'];
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
	            $tab.="<td align=center>".tanggalnormal($tgl)."</td>";
	            foreach($dtBrg as $kdBrg){
	            	#ambil data gudang
					$sTrans="select sum(jumlah) as jumlah from ".$dbname.".log_transaksi_vw 
		         		     where left(kodegudang,4)='".$_SESSION['empl']['lokasitugas']."' 
		         		     and tipetransaksi=5 and kodebarang='".$kdBrg."' and tanggal='".$tgl."'
		         		     and statusjurnal=1";
		         	$rTrans=fetchData($sTrans);
		         	$tab.="<td align=right>".number_format($rTrans[0]['jumlah'],2)."</td>";	
	            }
	           	$tab.="<td align=right>".number_format($nilArr[$tgl],2)."</td>";	
				$tab.="<td align=left>".$optNmKary[$upArr[$tgl]]."</td>";
	            $tab.="
	            <td align=center>";
	            if($periodeAkun[substr($tgl,0,7)]==0){//klo dah tutup buku kagak bisa dihapus
	            	$tab.="<img src=images/application/application_delete.png class=zImgBtn title=\"Delete \"  onclick=\"deletehead('".$tgl."');\">";	
	            }
	            $tab.="&nbsp;<img src=images/skyblue/zoom.png class=zImgBtn title='Detail' 
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
	            <tr><td colspan=6 align=center>
	            <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
	            <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	            </td>
	            </tr>";
		}
	 
        
        echo $tab."####".$footd;
        break;
		
	case'saveDt':
			if($param['tanggal']==''){
				exit('warning:'.$_SESSION['lang']['tanggal'].' '.$_SESSION['lang']['kosong']);
			}	
			if($param['volAir']==''){
				exit('warning:'.$_SESSION['lang']['volume'].' '.$_SESSION['lang']['kosong']);
			}	
			if($_SESSION['empl']['tipelokasitugas']!='PABRIK'){
				exit("warning: ".$_SESSION['lang']['cek'].' '.$_SESSION['lang']['tipe'].' '.$_SESSION['lang']['lokasitugas']);
			}
			#cek apakah sudah ada atau belum
			$rowAda=0;
			$sCek="select * from ".$dbname.".pabrik_mr_wtp where unit='".$_SESSION['empl']['lokasitugas']."' and tanggal='".tanggalsystemn($param['tanggal'])."'";
			$rCek=fetchData($sCek);
			$rowAda=count($rCek);
			if($rowAda!=0){
				exit('warning:'.$param['tanggal'].' '.$_SESSION['lang']['exist']);
			}
			$sins="insert into ".$dbname.".`pabrik_mr_wtp` (`unit`,`tanggal`,`nilai`,`updateby`) values 
			       ('".$_SESSION['empl']['lokasitugas']."','".tanggalsystemn($param['tanggal'])."','".$param['volAir']."','".$_SESSION['standard']['userid']."')";
			try {
				$owlPDO->exec($sins);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>".$sins; 
				die(); 
			}
	break;
    
    case'deletehead':
    	$sDel="delete from ".$dbname.".pabrik_mr_wtp where tanggal='".$param['tanggal']."' and unit='".$_SESSION['empl']['lokasitugas']."'";
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
		$sCek="select * from ".$dbname.".pabrik_mr_wtp where unit='".$_SESSION['empl']['lokasitugas']."' and tanggal='".$param['tgl']."'";
		//echo $sCek;
		$rCek=fetchData($sCek);
		$tanggal=tanggalnormal($rCek[0]['tanggal']);
		$tab.="<table cellspacing='1' cellpadding=1 border='0' class='sortable'>";
		$stForm="";
		if($periodeAkun[substr($tanggal, 0,7)]!=0){
			$stForm="disabled=disabled";
		}
		$tab.="<tr class=rowcontent><td>".$_SESSION['lang']['tanggal']."</td>
                <td>:</td>
                <td align=left><input type=text class='myinputtext' id='tgl2' onmousemove='setCalendar(this.id)' onkeypress='return false;'  size='12' maxlength='10' style=\"width:150px;\" value='".$tanggal."' readonly /></td></tr>";
		$tab.="<tr class=rowcontent><td>".$_SESSION['lang']['volume']."</td><td>:</td>
               <td><input type=text class='myinputtextnumber' id='volAir2' onkeypress='return angka_doang(event)' style=\"width:150px;\" value='".$rCek[0]['nilai']."'  ".$stForm." /></td></tr>";
		$tab.="<tr class=rowcontent><td colspan=3><button class=mybutton id=dtlAbn onclick=upDt()  ".$stForm.">".$_SESSION['lang']['save']."</button></td></tr>";
		$tab.="</table>";
		echo $tab;
	break;
	case'update':
		$sDel="delete from ".$dbname.".`pabrik_mr_wtp` where unit='".$_SESSION['empl']['lokasitugas']."' and tanggal='".tanggalsystemn($param['tanggal'])."'";
		try{
		  $owlPDO->exec($sDel);
		  	$sins="insert into ".$dbname.".`pabrik_mr_wtp` (`unit`,`tanggal`,`nilai`,`updateby`) values 
			       ('".$_SESSION['empl']['lokasitugas']."','".tanggalsystemn($param['tanggal'])."','".$param['volAir']."','".$_SESSION['standard']['userid']."')";
			try {
				$owlPDO->exec($sins);
			} catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "<br/>".$sins; 
				die(); 
			}
		}
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>".$sDel; 
			die(); 
	   }
	break;
}