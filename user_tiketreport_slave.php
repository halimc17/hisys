<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}
$enum_fields = ['0'=>'Perubahan','1'=>'Saran','2'=>'Bugs','3'=>'Pengecekan','4'=>'Support','5'=>'Unposting'];

switch($method){
	case 'delete':
		try {
		$owlPDO->beginTransaction();
			$where = " id='".$param['id']."'";
			$str = "delete from " . $dbname . ".log_5list_purchaser where ".$where."";
			$owlPDO->exec($str);
			
			#execute
			$owlPDO->commit();
		} catch (PDOException $e) {
			$owlPDO->rollback();
			echo "Errorcode, " . addslashes($e->getMessage());
			die();
		}
		
	break;
	case 'update':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'tipeorg'         => $param['tipeorg'],
					'managerid'       => $param['managerid'],
					'purchaserid'     => $param['purchaserid'],
					'createtime'      => date("Y-m-d H:i:s"),
					'createdby'       => $_SESSION['standard']['userid']
				);
				$where = "id='".$param['id']."'";
				$query = updateQuery($dbname,'log_5list_purchaser',$data,$where); //exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	case 'insert':
		try {
			$owlPDO->beginTransaction();
			$data = array(
				'tipeorg'         => $param['tipeorg'],
				'managerid'       => $param['managerid'],
				'purchaserid'     => $param['purchaserid'],
				'createtime'      => date("Y-m-d H:i:s"),
				'createdby'        => $_SESSION['standard']['userid']
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'log_5list_purchaser',$data,$cols); #exit("error".$query);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'addnew':
		$optkodeorg="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$optpurc="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		
		$str = "select distinct tipe from ".$dbname.".organisasi  where length(kodeorganisasi)='4' order by tipe";
		$res = fetchdata($str);
		foreach($res as $bar){
			$optkodeorg.="<option value='".$bar['tipe']."'>".$bar['tipe']."</option>";
		}
		
		$where=" and (tanggalkeluar = '0000-00-00' or tanggalkeluar > '".date("Y-m-d")."') and tanggalmasuk<='".date("Y-m-d")."'";
		$str = "select karyawanid,namakaryawan from ".$dbname.".`datakaryawan` where bagian='PRO'  ".$where." order by namakaryawan asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$optpurc.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']."</option>";
		}	
		
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td style=min-width:150px>".str_replace("."," ",$_SESSION['lang']['orgtype'])."</td>
					<td><select class='select2' style='width:400px;' id=tipeorg >".$optkodeorg."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['manager']."</td>
					<td><select class='select2' style='width:400px;' id=managerid >".$optpurc."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['nama']." ".$_SESSION['lang']['purchaser']."</td>
					<td><select class='select2' style='width:400px;' id=purchaserid >".$optpurc."</select></td>
				</tr>
				
                <tr>
                    <td colspan=4 align=center>
						<input type=hidden id=id >
						<input type=hidden id=method value=insert>
						<button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
						
                    </td>
                </tr>
            </table>
		";
		echo $tab;
	break;
	case 'loaddata':
		$tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
		<thead>
			<tr class=rowheader>
				<th style='text-align:center;' rowspan=2>".$_SESSION['lang']['nourut']."</th>
				<th rowspan=2 align='center'>ID</th>
				<th rowspan=2 align='center'>Menu</th>
				<th rowspan=2 align='center'>Tanggal</th>
				<th rowspan=2 align='center'>Subject</th>
				<th rowspan=2 align='center'>Jenis</th>
				<th rowspan=2 align='center'>From</th>
				<th rowspan=2 align='center'>Last Reply</th>
				<th rowspan=2 align='center'>Status</th>
				<th colspan=2 align='center' style='width:30px' colspan=2>Action</th>
			</tr>
			<tr class=rowheader>
				<th style='text-align:center;display:none;'>".$_SESSION['lang']['edit']."</th>
				<th style='text-align:center;display:none;'>".$_SESSION['lang']['delete']."</th>
			</tr>
		</thead>
		<tbody >";
		$arrHsl = array("0"=>"Belum diajukan","9"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);
		$arrStat = array("0"=>"Open","1"=>"Close","3"=>$_SESSION['lang']['ditolak']);
		
		$str = "select * from ".$dbname.".user";
		$res = fetchdata($str);
		foreach($res as $bar){
			$userid[$bar['namauser']]=$bar['karyawanid'];
		}
		
		$admin=false;
		$query = "select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
		$res = fetchdata($query);
		if(!empty($res)){			
			$admin=true;
		}
		
		
		$listticket=[];
		$str = "select * from ".$dbname.".owlhelp_ticket_dt where idht in (select id from ".$dbname.".owlhelp_ticket where 1=1) order by iddt asc";
		$res = fetchdata($str);
		foreach($res as $bar){
			$detail[$bar['idht']]=$bar['idht'];
			
			if($bar['pictindaklajut']==$_SESSION['standard']['userid']){
				$listticket[$bar['idht']]=$bar['idht'];
			}
			if($bar['username']==$_SESSION['standard']['username']){
				$listticket[$bar['idht']]=$bar['idht'];
			}
			
			$lastreply[$bar['idht']]=$bar['date'];
			$lastreplyby[$bar['idht']]=$bar['username'];
		}
		$str = "select * from ".$dbname.".owlhelp_ticket where username='".$_SESSION['standard']['username']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$listticket[$bar['id']]=$bar['id'];
		}	
		if($admin==false){
			$where=" and id in ('".implode("','",$listticket)."')";
		}
		
		$sudahbaca=[];
		$str = "select * from ".$dbname.".owlhelp_read where jenis='tiket' and karyawanid='".$_SESSION['standard']['userid']."'";
		$res = fetchdata($str);
		foreach($res as $bar){
			$sudahbaca[$bar['idmenu']]=$bar['idmenu'];
		}
		
		
		$st2="style=\"vertical-align:baseline;box-sizing:border-box;display:inline-block;background-color:#e74c3c;color:#ffffff;border-radius:0.3rem;text-align:center;height: 20px;padding-left:10px;padding-right:10px;padding-top:3px;padding-bottom:3px;\"";

		$st0="style=\"vertical-align:baseline;box-sizing:border-box;display:inline-block;background-color:#f1c40f;color:#ffffff;border-radius:0.3rem;text-align:center;height: 20px;padding-left:10px;padding-right:10px;padding-top:3px;padding-bottom:3px;\"";

		$st1="style=\"vertical-align:baseline;box-sizing:border-box;display:inline-block;background-color:#3498db;color:#ffffff;border-radius:0.3rem;text-align:center;height: 20px;padding-left:10px;padding-right:10px;padding-top:3px;padding-bottom:3px;\"";
		
		$str = "select * from ".$dbname.".owlhelp_ticket where 1=1 ".$where." order by status asc, id desc, date desc";
		$res = fetchdata($str);
		$buat = count($res);
		foreach($res as $bar){
			$optjenis="";
			foreach($enum_fields as $key => $value){
				$disabeled="";
				if($bar['category_ticket']=='5' and $key!='5'){
					$disabeled="disabled";
				}
				if($key==$bar['category_ticket']){
					$optjenis.="<option value=".$key." ".$disabeled." selected>".$value."</option>";
				}else{				
					$optjenis.="<option value=".$key." ".$disabeled.">".$value."</option>";
				}
			}
			$no++;
			if($bar['persetujuan']!='0'){
				$click=" style=cursor:pointer; title='Click to open conversations' onclick=openConvhelppopup('".$bar['id']."') ";
			}else{
				$click="";
			}
			
			$new="";
			if($bar['lastupdateby']!=$_SESSION['standard']['userid'] and $bar['status']=='0'){
				$new="<br><span class='badge badge-danger badge-smaller' style='vertical-align: text-top;cursor:pointer;font-size:9px;' title='New Update.'>New</span>";
			}elseif($bar['status']=='1' and empty($sudahbaca[$bar['id']])){
				$new="<br><span class='badge badge-danger badge-smaller' style='vertical-align: text-top;cursor:pointer;font-size:9px;' title='New Update.'>New</span>";
			}
			
			
			$tab.="<tr class=rowcontent>";
			$tab.="<td ".$click." align=center>".$no."</td>";
			$tab.="<td ".$click." align=center>#".$bar['id']."</td>";
			$tab.="<td onclick=jump(".$bar['info_menu'].",event) style=cursor:pointer; title='Click untuk menuju ke menu ".getNamaMenu($bar['info_menu'])."' align=left>".getNamaMenu2($bar['info_menu'])."</td>";
			$tab.="<td ".$click." align=center>".$bar['date']."</td>";
			$tab.="<td ".$click.">".$bar['subject']."</td>";
			$tab.="<td align=center>";
				if($bar['category_ticket']=='2'){
					$tab.="<span ".$st2.">".$enum_fields[$bar['category_ticket']]."</span>";
				}elseif($bar['category_ticket']=='0' or $bar['category_ticket']=='5'){
					$tab.="<span ".$st0.">".$enum_fields[$bar['category_ticket']]."</span>";
				}else{
					$tab.="<span ".$st1.">".$enum_fields[$bar['category_ticket']]."</span>";
				}
				if($admin==true  and $bar['status']=='0'  and ($bar['persetujuan']=='1' or $bar['persetujuan']=='0')){
					$tab.="<div style='clear:both;margin-top:2px;'></div><select class='select2 help' id=jenishelppopup_".$no." onchange=\"gantijenishelppopup('".$bar['id']."','".$bar['info_menu']."',this.value)\">".$optjenis."</select>";
				}	
			$tab.="</td>";
			$tab.="<td ".$click." align=center>".$bar['username']."</td>";
			// $tab.="<td align=center>".$arrHsl[$bar['persetujuan']]."</td>";
			$tab.="<td align=center nowrap><font color=blue>".$lastreplyby[$bar['id']]."</font><br>".str_replace(" ","<br>",$lastreply[$bar['id']])."</td>";
			// $tab.="<td align=center>".$arrStat[$bar['status']]."</td>";
			$tab.="<td align=center nowrap>";
				if($bar['persetujuan']=='0' and $bar['status']=='0'){
					if($bar['category_ticket']=='5'){
						$post="Post";
						$post="Ajukan";
					}else{
						$post="Post";
					}
					$tab.="<button style=\"background-color:red;color:white;border-color:white;\" title='Post ?' class=mybutton onclick=getticketsupportajukan(".$bar['id'].",".$bar['info_menu'].")>".$post."</button>";
				}elseif($bar['status']=='0' and $bar['persetujuan']=='9'){
					$tab.="<button style=\"background-color:#084a04;color:#f1c40f;border-color:#f1c40f;\" title='Wait' onclick=gethistoriapproval('".$bar['id']."','event','UNPOST') class=mybutton>Waiting Approval</button>";
				}elseif($bar['status']=='0'){
					if($lastreply[$bar['id']]!=''){						
						$labelbtn="In Progress";
						$stylebtn="style=\"background-color:#f1c40f;color:white;border-color:#f1c40f;\"";
					}elseif($lastreply[$bar['id']]==''){
						$labelbtn="Waiting";
						$stylebtn="style=\"background-color:#084a04;color:#f1c40f;border-color:#f1c40f;\"";
					}else{
						$labelbtn=$arrStat[$bar['status']];
						$stylebtn="style=\"background-color:#f1c40f;color:white;border-color:#f1c40f;\"";
					}
					$tab.="<button ".$stylebtn." title='Click untuk menutup ticket' class=mybutton onclick=getticketsupportclose(".$bar['id'].",".$bar['info_menu'].")>".$labelbtn."</button>";
				}elseif($bar['status']=='1'){
					$tab.="<button style=\"background-color:#26a69a;color:white;border-color:#26a69a;\" class=mybutton>".$arrStat[$bar['status']]."</button>";
				}elseif($bar['status']=='3'){
					$tab.="<button style=\"background-color:#e74c3c;color:white;border-color:#e74c3c;\" onclick=gethistoriapproval('".$bar['id']."','event','UNPOST') class=mybutton>".$arrStat[$bar['status']]."</button>";
				}else{
					$tab.="<button style=\"background-color:#26a69a;color:white;border-color:#26a69a;\" class=mybutton>".$arrStat[$bar['status']]."</button>";
				}
			$tab.="</td>";
			
			if($bar['persetujuan']=='0'){
				if($detail[$bar['id']]!=''){
					$tab.="<td align=center width=20px></td>";
				
					$tab.="<td ".$click." align=center><img title='Click to open conversations' class='zImgBtn' src='images/chat1.png' onclick=openConvhelppopup('".$bar['id']."')>".$new."</td>";
				}else{				
					$tab.="<td align=center width=20px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"tambahreporthelppopup('".$bar['info_menu']."','".$bar['id']."','edit');\" ></td>";
				
					$tab.="<td align=center width=20px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delreporthelppopup('".$bar['info_menu']."','".$bar['id']."','delete');\" ></td>";
				}
			}else{
				if($detail[$bar['id']]!=''){
					$tab.="<td align=center></td>";
					$tab.="<td ".$click." align=center><img title='Click to open conversations' class='zImgBtn' src='images/chat1.png' onclick=openConvhelppopup('".$bar['id']."')>".$new."</td>";
				}else{				
					$tab.="<td align=center></td>";
					$tab.="<td ".$click." align=center><img title='Click to open conversations' class='zImgBtn' src='images/chat0.png' onclick=openConvhelppopup('".$bar['id']."')>".$new."</td>";
				}
			}
		}
		
		$tab.="</tbody>
		<tfoot>
		</tfoot>
		</table>";
		echo $tab;
	break;
}

function getNamaMenu($idmenu){
	include('lib/zLib.php');
	global $dbname;
	global $owlPDO;
	
	$menu=[];
	$str="SELECT f.* FROM (SELECT @id AS _id, (SELECT @id := parent FROM ".$dbname.".menu WHERE id = _id)FROM (SELECT @id := ".$idmenu." ) tmp1 JOIN ".$dbname.".menu ON @id <> 0) tmp2 JOIN ".$dbname.".menu f ON tmp2._id = f.Id order by action,parent";
	$res = fetchdata($str);
	foreach($res as $bar){
		if($bar['parent']==0){
			$modul  =$bar['caption'];
			$modulid=$bar['id'];
		}
		$menu[$bar['caption']]=strtoupper(strtolower($bar['caption']));
	}
	return implode(" - ",$menu);
}

function getNamaMenu2($idmenu){
	include('lib/zLib.php');
	global $dbname;
	global $owlPDO;
	
	$menu='';
	$str="SELECT * from ".$dbname.".menu where id= '".$idmenu."'";
	$res = fetchdata($str);
	foreach($res as $bar){
		$menu=strtoupper(strtolower($bar['caption']));
	}
	return $menu;
}
?>
