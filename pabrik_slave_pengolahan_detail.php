<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
#include_once('lib/zGrid.php');
#include_once('lib/rGrid.php');
include_once('lib/formTable.php');

$proses=checkPostGet('proses','');

$param = $_POST;
$optDwnStat=array(''=>'','EDT'=>'EDT : Breakdown','SDT'=>'SDT : Stagnasi','CDT'=>'CDT : Commercial Downtime');
switch($proses) {
	case'showDetail':
	# Get Data
	$where = "nopengolahan='".$param['nopengolahan']."'";
	$cols = "kodeorg as station,tahuntanam,jammulai,jamselesai,jamstagnasi,downstatus,".
	    "tekananawal,tekananakhir,keterangan,nopengolahan";
	$query = selectQuery($dbname,'pabrik_pengolahanmesin',$cols,$where);
	$data = fetchData($query);	

	$cols2='shift,kodeorg,tanggal,jamstagnasi';
	$query2 = selectQuery($dbname,'pabrik_pengolahan',$cols2,$where);
	$data2 = fetchData($query2);	

	#cek data
	$sData="select * from ".$dbname.".pabrik_rawatmesinht 
		        where shift='".$data2[0]['shift']."' and tanggal='".$data2[0]['tanggal']."'
		        and pabrik='".$data2[0]['kodeorg']."'";
	//echo $sData;
	$rData=fetchdata($sData);
	$disbtn='disabled=disabled';
	if(count($rData)!=0){
	//if($data2[0]['jamstagnasi']!=0){
		$disbtn='';
	}
	#ambil data station dan mesin
	$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
	    "induk='".$param['kodeorg']."'");
	$optMesinAll = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
	    "tipe='STENGINE'",'0',true);	


	$tab.="<fieldset style=width:1180px><legend>Form Mesin</legend>";
	$tab.="<fieldset><legend><b>".$_SESSION['lang']['list']."</b></legend>";
	$tab.="<button class=mybutton onclick=getNo(event) ".$disbtn.">".$_SESSION['lang']['addtranperawatan']."</button>";
	$tab.="<table cellspacing=1 cellpadding=1 border=0 class=sortable><thead class=rowheader>";
	$tab.="<tr>
		   <td>".$_SESSION['lang']['action']."</td>
		   <td>".$_SESSION['lang']['station']."</td>";
	$tab.="<td>".$_SESSION['lang']['mesin']."</td>";
	$tab.="<td>".$_SESSION['lang']['jamawalperbaikan']."</td>";
	$tab.="<td>".$_SESSION['lang']['jamakhirperbaikan']."</td>";
	$tab.="<td>".$_SESSION['lang']['jamstagnasi']."</td>";
	$tab.="<td>".$_SESSION['lang']['downstatus']."</td>";
	$tab.="<td>".$_SESSION['lang']['keterangan']."</td></thead><tbody id=detData>";
	
		foreach($data as $row=>$isiData){
			$tab.="<tr class=rowcontent>";
			$tab.="<td><img src=\"images/application/application_delete.png\" class=\"resicon\" title=\"Delete \" onclick=\"delData('".$isiData['nopengolahan']."','".$isiData['station']."','".$isiData['tahuntanam']."')\"></td>";
			$tab.="<td>".$optOrg[$isiData['station']]."</td>";
			$tab.="<td>".$optMesinAll[$isiData['tahuntanam']]."</td>";
			$tab.="<td align=center>".$isiData['jammulai']."</td>";
			$tab.="<td align=center>".$isiData['jamselesai']."</td>";
			$tab.="<td align=right>".$isiData['jamstagnasi']."</td>";
			$tab.="<td>".$optDwnStat[$isiData['downstatus']]."</td>";
			$tab.="<td>".$isiData['keterangan']."</td>";
			$tab.="</tr>";
		}
	
	$tab.="</tbody></table>";
	$tab.="</fieldset>";
	$tab.="</fieldset>";

	echo $tab;
	break;
	case'getForm':
		$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
	    "induk='".$param['kodeorg']."'");

		$optMesinAll = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
		    "tipe='STENGINE'",'0',true);
		#data perawatan
		$sData="select * from ".$dbname.".pabrik_rawatmesinht 
		        where shift='".$param['shift']."' and tanggal='".tanggalsystemn($param['tanggal'])."'
		        and pabrik='".$_SESSION['empl']['lokasitugas']."' order by mesin asc";
		//echo $sData;
		$rData=fetchdata($sData);
		
		

		$title=$_SESSION['lang']['addtranperawatan'];
		$tab.="<div style=height:530px;overflow:auto;>";
		$tab.="<button class=mybutton onclick=addToDb(".count($rData).") ".$disbtn.">".$_SESSION['lang']['addtodetail']."</button>";
		$tab.="<table cellpadding=1 cellspacing=1 border=0  class=sortable><thead>";
		$tab.="<tr class=rowheader>";
		$tab.="<td>&nbsp;</td>";
		$tab.="<td>".$_SESSION['lang']['notransaksi']."</td>";
		$tab.="<td>".$_SESSION['lang']['station']."</td>";
		$tab.="<td>".$_SESSION['lang']['mesin']."</td>";
		$tab.="<td>".$_SESSION['lang']['tanggal']."</td>";
		$tab.="<td>".$_SESSION['lang']['jammulai']."</td>";
		$tab.="<td>".$_SESSION['lang']['jamselesai']."</td>";
		$tab.="<td>".$_SESSION['lang']['uraiankerusakan']."</td>";
		$tab.="<td>".$_SESSION['lang']['jamstagnasi']."</td>";
		$tab.="<td>".$_SESSION['lang']['downstatus']."</td>";
		$tab.="</tr></thead><tbody>";
		if(count($rData)==0){
			$tab.="<tr class=rowcontent>";
			$tab.="<td colspan=10>".$_SESSION['lang']['dataempty']."</td>";
			$tab.="</tr>";
		}else{
			foreach($rData as $row=>$shData){
				$tampil='';
				$tmChk='';
				#data maintain pengolahan
				$sPeng="select noperawatan,tahuntanam,kodeorg from ".$dbname.".pabrik_pengolahanmesin 
				        where nopengolahan='".$param['nopengolahan']."' and tahuntanam='".$shData['mesin']."'
				        and kodeorg='".$shData['statasiun']."'  order by tahuntanam asc";
				$rPeng=fetchdata($sPeng);
				if(count($rPeng)!=0){
					$tampil='style=display:none';
					$tmChk="checked";
				}
				$no+=1;
				$tab.="<tr class=rowcontent ".$tampil.">";
				$tab.="<td><input type=checkbox id=dt_".$no."  ".$tmChk." /></td>";
				$tab.="<td id=notrans_".$no.">".$shData['notransaksi']."</td>";
				$tab.="<td><input type=hidden id=stat_".$no." value='".$shData['statasiun']."' />".$optOrg[$shData['statasiun']]."</td>";
				$tab.="<td><input type=hidden id=msn_".$no." value='".$shData['mesin']."' />".$optMesinAll[$shData['mesin']]."</td>";
				$tab.="<td>".$shData['tanggal']."</td>";
				$tab.="<td><input type=hidden id=jmml_".$no." value='".substr($shData['jammulai'],-8,8)."' />".$shData['jammulai']."</td>";
				$tab.="<td><input type=hidden id=jmsls_".$no." value='".substr($shData['jamselesai'],-8,8)."' />".$shData['jamselesai']."</td>";
				$tab.="<td><input type=hidden id=ket_".$no." value='".$shData['kegiatan']."' />".$shData['kegiatan']."</td>";
				$tab.="<td><input type=hidden id=stag_".$no." value='".$shData['jumlahjamperbaikan']."' />".$shData['jumlahjamperbaikan']."</td>";
				$tab.="<td><input type=hidden id=dwnstat_".$no." value='".$shData['downstatus']."' />".$optDwnStat[$shData['downstatus']]."</td>";
				$tab.="</tr>";
			}
		}

		
		$tab.="<tbody></table></div><input type=hidden id=nopeng value='".$param['nopengolahan']."' />
		       <input type=hidden id=totJmStag value='".$totJmStag."' />";
		echo $title."####".$tab;
	break;
	case'addDetail':
	
	
	
		$whr="nopengolahan='".$param['nopengolahan']."'";
		$optJm=makeOption($dbname,'pabrik_pengolahan','nopengolahan,jamstagnasi',$whr);
		$sCek="select sum(jamstagnasi) as jmljam from ".$dbname.".pabrik_pengolahanmesin where nopengolahan='".$param['nopengolahan']."'";
		$rCek=fetchdata($sCek);
		if(count($rCek)==0){
			if($param['totJmStag']>$optJm[$param['nopengolahan']]){
				exit('warning:  '.$_SESSION['lang']['total'].''.$_SESSION['lang']['jamstagnasi'].'  tidak sama dengan data header');
			}	
		}else{
			if($rCek[0]['jmljam']>$optJm[$param['nopengolahan']]){
				exit('warning: '.$_SESSION['lang']['total'].''.$_SESSION['lang']['jamstagnasi'].' tidak sama dengan data header');
			}
		}

		
		
		#= delete 1st
	
		$sdel="delete from  ".$dbname.".`pabrik_pengolahan_barang` where nopengolahan='".$param['nopengolahan']."'";
		try{
			$owlPDO->exec($sdel);
		}
		catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}
		
		
		foreach($_POST['notrans'] as $row=>$stat){
			#cari barang di notrans tsb
			$str=" select * from ".$dbname.".pabrik_rawatmesindt where notransaksi='".$_POST['notrans'][$row]."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){
				 $strins="INSERT INTO ".$dbname.".`pabrik_pengolahan_barang` 
				(`nopengolahan`, `kodeorg`, `tahuntanam`, `kodebarang`,`jumlah`)
				values ('".$param['nopengolahan']."','".$_POST['dtStation'][$row]."','".$_POST['dtMesin'][$row]."','".$bar['kodebarang']."',
						'".$bar['jumlah']."')";
				try{
					$owlPDO->exec($strins);
				}
				catch (PDOException $e) {
					print " Gagal  !: " . $e->getMessage() . "<br/>"; 
					die(); 
				}
				
			}
		}
		
		$awal=0;
		$sIns="insert into ".$dbname.".pabrik_pengolahanmesin (nopengolahan,kodeorg,tahuntanam,jammulai,jamselesai,jamstagnasi,downstatus,keterangan,noperawatan) values";
		foreach($_POST['dtStation'] as $row=>$stat){
			#data maintain pengolahan
			$sPeng="select noperawatan,tahuntanam,kodeorg from ".$dbname.".pabrik_pengolahanmesin 
			        where nopengolahan='".$param['nopengolahan']."' and tahuntanam='".$_POST['dtMesin'][$row]."'
			        and kodeorg='".$stat."'  order by tahuntanam asc";
			$rPeng=fetchdata($sPeng);
			if(count($rPeng)!=0){
				continue;
			}
			if($awal==0){
				$sIns.="('".$param['nopengolahan']."','".$stat."','".$_POST['dtMesin'][$row]."','".$_POST['dtJammulai'][$row]."','".$_POST['dtJamselesai'][$row]."','".$_POST['dtStag'][$row]."','".$_POST['dtDownstatus'][$row]."','".$_POST['dtKet'][$row]."','".$_POST['notrans'][$row]."')";
				$awal=1;
			}else{

				$sIns.=",('".$param['nopengolahan']."','".$stat."','".$_POST['dtMesin'][$row]."','".$_POST['dtJammulai'][$row]."','".$_POST['dtJamselesai'][$row]."','".$_POST['dtStag'][$row]."','".$_POST['dtDownstatus'][$row]."','".$_POST['dtKet'][$row]."','".$_POST['notrans'][$row]."')";
			}
		}
		try{
            $owlPDO->exec($sIns);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
		
		
		#insert materialnya
		#ambil data material dari perawatan
		//pabrik_rawatmesindt
		
		
		
		
	break;
	case'delDetail':
		$sIns="delete from  ".$dbname.".pabrik_pengolahanmesin where 
		       nopengolahan='".$param['nopengolahan']."' and kodeorg='".$param['station']."' and tahuntanam='".$param['mesin']."'";
		try{
            $owlPDO->exec($sIns);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
	break;
	case'getJam':
		$sShift="select  jammulai,jamselesai from ".$dbname.".pabrik_5shift 
		         where shift='".$param['shift']."' and kodeorg='".$_SESSION['empl']['lokasitugas']."'";
		$rShift=fetchdata($sShift);

		echo $rShift[0]['jammulai']."####".$rShift[0]['jamselesai'];
	break;
	
	case'getdowntime':
		#get data downtime
		
		$str=" select sum(jumlahjamperbaikan) as dt from ".$dbname.".pabrik_rawatmesinht where pabrik='".$param['kodeorg']."' 
			and tanggal='".tanggalsystemn($param['tanggal'])."' and shift='".$param['shift']."' and downstatus='EDT' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$dt=$bar['dt'];
		if($dt==''){
			$dt=0;
		}
		echo $dt;
		
	break;
	
    case 'showDetail2':
	# Get Data
	$where = "nopengolahan='".$param['nopengolahan']."'";
	$cols = "kodeorg as station,tahuntanam,jammulai,jamselesai,jamstagnasi,downstatus,".
	    "tekananawal,tekananakhir,keterangan";
	$query = selectQuery($dbname,'pabrik_pengolahanmesin',$cols,$where);
	$data = fetchData($query);
	
	# Options
	/*if(!empty($whereBarang)) {
	    $whereBarang = "kodebarang in (";
	    foreach($data as $key=>$row) {
		if($key==0) {
		    $whereBarang .= "'".$row['kodebarang']."'";
		} else {
		    $whereBarang .= ",'".$row['kodebarang']."'";
		}
	    }
	    $whereBarang .= ")";
	    $optBarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',
		$whereBarang);
	} else {
	    $optBarang = array();
	}*/
	$optOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
	    "induk='".$param['kodeorg']."'");
	/*$optMesin = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
	    "tipe='STENGINE' and induk='".end(array_reverse(array_keys($optOrg)))."'");
        */
        $optMesin = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
	    "tipe='STENGINE' and induk='".$optOrg."'");
	$optMesinAll = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
	    "tipe='STENGINE'",'0',true);
        //$optDwnStat = makeOption($dbname,'pabrik_pengolahanmesin','downstatus','','7');
	

        //$optDwnStat=array(''=>'','breakdown'=>'Breakdown','stagnasi'=>'Stagnasi','emergency'=>'Emergency');
    
	
	# Data Show
	$dataShow = $data;
	foreach($dataShow as $key=>$row) {
            $dataShow[$key]['downstatus']=$optDwnStat[$row['downstatus']];
	    $dataShow[$key]['station'] = $optOrg[$row['station']];
	    $dataShow[$key]['tahuntanam'] = $optMesinAll[$row['tahuntanam']];
	}
	
	# Form
	$theForm1 = new uForm('mesinForm',$_SESSION['lang']['form']." ".$_SESSION['lang']['mesin'],2);
	// $theForm1->addEls('station',$_SESSION['lang']['station'],'','select','L',25,$optOrg);
	// $theForm1->_elements[0]->_attr['onchange']='updMesin()';
	// $theForm1->addEls('tahuntanam',$_SESSION['lang']['mesin'],'0','select','L',25,$optMesin);
	// $theForm1->addEls('jammulai',$_SESSION['lang']['jamawalperbaikan'],'0','jammenit','R',10);
	// $theForm1->addEls('jamselesai',$_SESSION['lang']['jamakhirperbaikan'],'0','jammenit','R',10);
        
	// $theForm1->addEls('jamstagnasi',$_SESSION['lang']['jamstagnasi'],'0','textnum','R',10);
 //        $theForm1->addEls('downstatus',$_SESSION['lang']['downstatus'],'0','select','L',25,$optDwnStat);
        
 //        $theForm1->addEls('tekananawal',$_SESSION['lang']['tekananawal'],'0','textnum','R',10);
 //        $theForm1->addEls('tekananakhir',$_SESSION['lang']['tekananakhir'],'0','textnum','R',10);
        
	// $theForm1->addEls('notranPeng','No. Transaksi Pengolahan','','text','L',25);
	// $theForm1->_elements[0]->_attr['onchange']='updMesin()';
	// $theForm1->_elements[0]->_attr['readonly']='readonly';
	// #$theForm1->addEls('kodebarang',$_SESSION['lang']['kodebarang'],'','searchBarang','L',20,null,null,'jumlahbarang_satuan');
	// #$theForm1->addEls('jumlahbarang',$_SESSION['lang']['jumlahbarang'],'0','textnumwsatuan','L',10);
	
	// # Table
	// $theTable1 = new uTable('mesinTable',$_SESSION['lang']['tabel']." ".$_SESSION['lang']['mesin'],$cols,$data,$dataShow);
	
	// # FormTable
	// $formTab1 = new uFormTable('ftMesin',$theForm1,$theTable1,null,array('nopengolahan'));
	// $formTab1->_target = "pabrik_slave_pengolahan_mesin";
	// $formTab1->_addActions = array(
	//     'material'=>array(
	// 	'img'=>'detail1.png',
	// 	'onclick'=>'showMaterial'
	//     )
	// );
	
	#== Display View
	# Draw Tab
	echo "<fieldset><legend><b>Detail</b></legend>";
	//$formTab1->render();
	echo "</fieldset>";
	break;
    case 'updMesin':
	$opt = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',
	    "tipe='STENGINE' and induk='".$param['station']."'");
	echo json_encode($opt);
	break;
    default:
	break;
}
?>