<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once ('config/connection.php');

$owlPDOX="";

$proses               = checkPostGet('proses', '');
$db                   = checkPostGet('db', '');
$user                 = $uname;
$pass                 = $passwd;
$user                 = checkPostGet('user', '');
$pass                 = checkPostGet('pass', '');

$tabel                = checkPostGet('tabel', '');
$texttofind           = checkPostGet('txtcari', '');
$texttofind2           = checkPostGet('txtcari2', '');
$texttofind3           = checkPostGet('txtcari3', '');
$texttofind4           = checkPostGet('txtcari4', '');
$texttofind5           = checkPostGet('txtcari5', '');
$fieldname            = checkPostGet('field', '');
$fieldname2            = checkPostGet('field2', '');
$fieldname3            = checkPostGet('field3', '');
$fieldname4            = checkPostGet('field4', '');
$fieldname5            = checkPostGet('field5', '');
$order1               = checkPostGet('order1', '');
$order2               = checkPostGet('order2', '');	
$orderby1             = checkPostGet('orderby1', '');	
$orderby2             = checkPostGet('orderby2', '');	
$page                 = checkPostGet('page', '');	
$limit                 = checkPostGet('limit', '');	
$baris                 = checkPostGet('baris', '');	
$mode                 = checkPostGet('mode', '');	
$baris                 = checkPostGet('baris', '');	



try{
  $owlPDOX = new PDO('mysql:host='.$dbserver.';dbname='.$db, $user, $pass, array(PDO::ATTR_PERSISTENT => false));
  $owlPDOX->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}catch (PDOException $e){
	print " Gagal, could not connect\n";	
	print "Error!: " . $e->getMessage() . "<br/>";
	die();
}


switch($proses){
	case'showtable':
	
		$tablelist="<option value=''></option>";
		$str="show tables from ".$db;
		$res=$owlPDOX->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_NUM);
		while ($bar = $res->fetch()) {
			if($bar[0]=='user' || $bar[0]=='admin_list'|| $bar[0]=='auth' || $bar[0]=='master_lisensi'|| $bar[0]=='menu'|| $bar[0]=='error_log_mobile' || $bar[0]=='authmobile'){
				continue;
			}
			$tablelist.="<option value='".$bar[0]."'>".$bar[0]."</option>";
		}
		echo $tablelist;
	break;
	case'showkolom':
		$field=getKolomName($tabel,'option');
		
		echo $field;
	break;
	case'tampilkan':
		if($limit>1000){
			exit("Warning : Limit maksimal = 1.000 baris");
		}
		if($limit==0 or $limit==''){
			$limit=50;
		}
		
		$curpage    = floatval($page);
		$MAX_ROW	= $limit;
		$order = '';
		$offset     = $curpage*$MAX_ROW;
		$disp_page  = $curpage+1;
		
		$field=getKolomName($tabel,'array'); 
		$where='';
		if($texttofind!=''){
			$where=" and ".$fieldname." like '%".$texttofind."%'"; 
		}
		if($texttofind2!=''){
			$where.=" and ".$fieldname2." like '%".$texttofind2."%'"; 
		}
		if($texttofind3!=''){
			$where.=" and ".$fieldname3." like '%".$texttofind3."%'"; 
		}
		if($texttofind4!=''){
			$where.=" and ".$fieldname4." like '%".$texttofind4."%'"; 
		}
		if($texttofind5!=''){
			$where.=" and ".$fieldname5." like '%".$texttofind5."%'"; 
		}
		
		
		$xxx="select * from ".$db.".".$tabel." where 1=1 ".$where;
		$strXur=$owlPDO->query($xxx) or die(print " Gagal: ".PDOException::getMessage());
		$strXur->setFetchMode(PDO::FETCH_NUM);
		$numrows=owlBaris($strXur);
		if($numrows>$MAX_ROW){
			if(($numrows%$MAX_ROW)!=0)
				$page=(floor($numrows/$MAX_ROW))+1;
			else
				$page=$numrows/$MAX_ROW;	
		}	else{
			$page=1;
		}
		echo $_SESSION['lang']['page']." ".$disp_page." ".$_SESSION['lang']['of']." ".$page." (Max.".$MAX_ROW." / ".$_SESSION['lang']['page'].")";
		echo" [ ".$_SESSION['lang']['gotopage']." : <select id=page>";
		for($y=0;$y<$page;$y++){
			$n="";
			if($y==($disp_page-1)){
				$n=" selected ";
			}
			echo"<option value=".$y." ".$n.">".($y+1)."</option>";
		}
		echo "</select> <button onclick=\"tampilkankehalaman('".$tabel."');\" class=mybutton>Go</button> ]";
			
		$str="SHOW KEYS from ".$db.".".$tabel." WHERE Key_name = 'PRIMARY'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$colprimary[$bar['Column_name']]=$bar['Column_name'];
		}
		
		echo"<table class=sortable cellspacing=1 border=0>
			 <thead><tr class=rowheader>";
		echo"<th align=center>No</th>";
		echo"<th align=center>Modify</th>";
		for($x=0;$x<count($field);$x++){
			$col="";
			$colind="";
			if(@$colprimary[$field[$x]]!=""){
				$col="style=color:red";
				$colind=$x;
			}
			echo"<th ".$col." align=center>".$field[$x]."</th>";
		}	 
		echo"</tr></thead><tbody>";
		
		$num=0;
		$no=$offset;
		$str="select * from ".$db.".".$tabel." where 1=1 ".$where."  order by ".$order1." ".$orderby1.",".$order2." ".$orderby2." limit ".$offset.",".$MAX_ROW;
		$strXu=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$strXu->setFetchMode(PDO::FETCH_NUM);
		while($barXu=$strXu->fetch()){
			$no++;
			echo"<tr class=rowcontent>";
			echo"<td align=center>".$no."</td>";
			echo"<td align=center >
						<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"tampilkan('edit','".$no."')\">
						<img id=tblsave".$no." src=images/save.png class=zImgBtn  title='Save' onclick=\"simpan('".$no."','".count($field)."')\">
			</td>";
			for($x=0;$x<count($field);$x++){
				$col="";
				if(@$colprimary[$field[$x]]!=""){
					$col="style=color:red";
					echo"<td ".$col." id=".$no."_".$x." name=label_".$no."_".$x.">".$barXu[$x]."</td>";
				}elseif(@$colprimary[$field[$x]]=="" and $mode=='edit' and $baris==$no){
					echo"<td ".$col."><input style=width:98% name=label_".$no."_".$x." type=text id=".$no."_".$x." class='myinputtext' value=\"".$barXu[$x]."\"></td>";
				}else{
					echo"<td id=".$no."_".$x." name=label_".$no."_".$x.">".$barXu[$x]."</td>";
				}
				
			}	
			echo"</tr>";		
		}
		echo"</tbody><tfoot></tfoot></table>";
	break;
	case'update':
		$field=getKolomName($tabel,'array'); 
		
		$str="SHOW KEYS from ".$db.".".$tabel." WHERE Key_name = 'PRIMARY'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$colprimary[$bar['Column_name']]=$bar['Column_name'];
			$idxprimary[$bar['Column_name']]=$bar['Seq_in_index'];
		}
		$where=$data="";
		for($x=0;$x<count($field);$x++){
			$isi = checkPostGet($baris."_".$x, '');
			if(@$colprimary[$field[$x]]!=""){
				if($idxprimary[$field[$x]]==1){
					$where="".$field[$x]."='".$isi."'";
				}else{
					$where.=" and ".$field[$x]."='".$isi."'";
				}
			}else{
				if(($x+1)<count($field)){					
					$data.="".$field[$x]."='".$isi."',";
				}else{
					$data.="".$field[$x]."='".$isi."'";
				}
			}
		}	 
		$str = "update ".$db.".".$tabel." set ".$data." where ".$where."";
		
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
}

function getKolomName($TABLENAME,$output){
	global $db;
	global $owlPDOX;
	$option='';
	$arrReturn=Array();
	try { 
		$strUx=$owlPDOX->query("select * from ".$db.".".$TABLENAME." limit 1");
		$raw_column_data = $strUx->fetchAll();
		$jlh_Kolom=$strUx->columnCount();
			for($x=0;$x<$jlh_Kolom;$x++){
				$test=$strUx->getColumnMeta($x);
				$column_names[] = $test['name'];
				array_push($arrReturn, $test['name']);
				$option.="<option value='".$test['name']."'>".$test['name']."</option>"; 
			} 
	} catch (PDOException $e){
		echo " Gagal: ".$e->getMessage(); //return exception
		return false;
	}         
        
	if($output=='array')
	  return $arrReturn;
	else
	  return $option; 
}
?>