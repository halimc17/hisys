<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$parent=$_POST['parent'];
$sub=$_POST['sub'];
$_POST['parent']==''?$menu=$_POST['sub']:$menu=$_POST['parent'];
$proses=$_GET['proses'];
$sclok = checkPostGet('sclok','');
$scjbt = checkPostGet('scjbt','');
$sctpk = checkPostGet('sctpk','');
$scact = checkPostGet('scact','');

$menuId=	isset($_POST['id_menu'])? $_POST['id_menu']: '';
$usrname=	isset($_POST['usernm'])? $_POST['usernm']: '';
$stat=		isset($_POST['stat'])? $_POST['stat']: '';
switch($proses)
{
    case'getForm':
	$optact=$opttpk=$optjbt=$optlok="<option value=''>".$_SESSION['lang']['all']."</option>";
	##GET TIPE KARYAWAN
	$str="select id,tipe from ".$dbname.".sdm_5tipekaryawan where aktif='1' order by tipe asc";
	$res=fetchdata($str);
	foreach($res as $val){
		$opttpk.="<option value='".$val['id']."'>".$val['tipe']."</option>";		
	}
	
	##GET JABATAN
	$str="select kodejabatan,namajabatan from ".$dbname.".sdm_5jabatan where aktif='1' order by namajabatan asc";
	$res=fetchdata($str);
	foreach($res as $val){
		$optjbt.="<option value='".$val['kodejabatan']."'>".$val['namajabatan']."</option>";		
	}
	
	##GET LOKASI TUGAS
	$str="select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where length(kodeorganisasi)='4' order by namaorganisasi asc";
	$res=fetchdata($str);
	foreach($res as $val){
		$optlok.="<option value='".$val['kodeorganisasi']."'>".$val['kodeorganisasi']."</option>";		
	}
	
	$optact.="<option value='2'>Uncheck</option>";		
	$optact.="<option value='1'>Checked</option>";		
	
	echo"<input type=hidden id=id_menu value='".$menu."' />
             <input type=button class=mybutton value='".$_SESSION['lang']['close']."' onclick=closeOrderEditor()>";
		echo"<div class='table-scroll'>
		<table class='sortable' cellspacing=1 cellpadding=3 border=0> 
		<thead>
		<tr class='rowheader'>
			 <th style=width:50px>".$_SESSION['lang']['action']."
			 <p>
				<select id='scact' onchange='gettpk()'>".$optact."</select>
			 </th>
			 <th id=usr_nm_>".$_SESSION['lang']['username']."</th>
			 <th>".$_SESSION['lang']['namakaryawan']."</th>
			 <th>
				".$_SESSION['lang']['lokasitugas']."
				<p>
				<select id='sclok' onchange='gettpk()'>".$optlok."</select>
			 </th>
			 <th>
				".$_SESSION['lang']['jabatan']."
				<p>
				<select id='scjbt' onchange='gettpk()'>".$optjbt."</select>
			</th>
			 <th>
				".$_SESSION['lang']['tipekaryawan']."
				<p>
				<select id='sctpk' onchange='gettpk()'>".$opttpk."</select>
			</th>
			 </tr>
			 </thead><tbody id='containerdt'>";
                echo getValue();
       echo"</tbody></table></div>
	   <br>";
    break;
    case'addData':
        if($stat==1)
        {
          #==============
            $menu[]=$menuId;
            for($x=0;$x<=7;$x++){
                if($menuId!=''){
                        $str=$owlPDO->query("select parent from ".$dbname.".menu where id=".$menuId);
                        $str->setFetchMode(PDO::FETCH_OBJ);
                            while($bar=$str->fetch()){
                                if($bar->parent!=0){
                                    $menu[]=$bar->parent;
                                    $menuId=$bar->parent;                    
                                }
                            }
                }
            }
            #================================   Add juga untuk semua parent nya
                foreach($menu as $key=>$val){      
                  $stra="delete from ".$dbname.".auth where menuid=".$val." and namauser='".$usrname."'";     
                  $strb="insert into ".$dbname.".auth(namauser, menuid, status, lastuser, detail)
                                   values('".$usrname."',".$val.",1,".$_SESSION['standard']['userid'].",0)";
                  $owlPDO->exec($stra);
                  $owlPDO->exec($strb);
                 }
        }
        else
        {
            $sDel="delete from ".$dbname.".auth where namauser='".$usrname."' and menuid='".$menuId."'";
            $owlPDO->exec($sDel);
        }
    break;
	
	case'gettpk':
		echo getValue();
	break;
	
    default:
    break;
}

function getValue(){
	global $sclok;
	global $sctpk;
	global $scjbt;
	global $scact;
	global $menu;
	global $dbname;
	global $owlPDO;
	
	$tab="";
	$where="";
	if($sctpk!=''){
		$where.=" and b.tipekaryawan='".$sctpk."'";
	}
	if($sclok!=''){
		$where.=" and b.lokasitugas='".$sclok."'";
	}
	if($scjbt!=''){
		$where.=" and b.kodejabatan='".$scjbt."'";
	}
	if($scact=='1'){
		$where.=" and c.menuid='".$menu."'";
	}elseif($scact=='0'){
		$where.=" and c.menuid!='".$menu."'";
	}

		$sData=$owlPDO->query("select distinct a.karyawanid,a.namauser,b.namakaryawan,b.lokasitugas,b.kodejabatan,b.tipekaryawan from ".$dbname.".user a left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid left join ".$dbname.".auth c on a.namauser=c.namauser where a.status!='0' ".$where." ");      
		$sData->setFetchMode(PDO::FETCH_ASSOC);
		$no=0;
		while($rData=$sData->fetch()){
			$no++;
			$sJbtn=$owlPDO->query("select namajabatan from ".$dbname.".sdm_5jabatan where kodejabatan='".$rData['kodejabatan']."'");
			$sJbtn->setFetchMode(PDO::FETCH_ASSOC);
			$rJbtn=$sJbtn->fetch();
			
			$stpk=$owlPDO->query("select tipe from ".$dbname.".sdm_5tipekaryawan where id='".$rData['tipekaryawan']."'");
			$stpk->setFetchMode(PDO::FETCH_ASSOC);
			$rtpk=$stpk->fetch();
			
			$arrd="";
			$sAuth=$owlPDO->query("select distinct * from ".$dbname.".auth   where namauser='".$rData['namauser']."' and menuid='".$menu."' and status=1");
			$sAuth->setFetchMode(PDO::FETCH_ASSOC);
			$rAuth=owlBaris($sAuth); 
			if($rAuth==1){
				$arrd="checked";
			}
			
			$tab.="<tr class=rowcontent>
			 <td style='text-align:center'><input type=checkbox id=adddt_".$no." onclick=addData(".$no.",".$menu.") ".$arrd." /></td>
			 <td id=usr_nm_".$no.">".$rData['namauser']."</td>
			 <td>".$rData['namakaryawan']."</td>
			 <td align=center>".$rData['lokasitugas']."</td>
			 <td>".$rJbtn['namajabatan']."</td>
			 <td>".$rtpk['tipe']."</td>
			 </tr>";
			
		}
				
	return $tab;
}
?>
