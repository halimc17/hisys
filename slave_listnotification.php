<?//@Copy nangkoelframework
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/zFunction.php');
include_once('lib/fpdf.php');

$method = checkPostGet('method', '');
$id = checkPostGet('id', '');
$xnotif = checkPostGet('xnotif', '');

$karyawanid = $_SESSION['standard']['userid'];
$bagian = $_SESSION['empl']['bagian'];
$kodejabatan = $_SESSION['empl']['kodejabatan'];
$tipekaryawan = $_SESSION['empl']['tipekaryawan'];

switch($method)
{
	case'showmorenotif':		
		$str="select count(id) as count from ".$dbname.".list_notification a 
			left join ".$dbname.".setup_notification_ht b on a.kodenotification = b.kodejenis 
			where (a.karyawanid='".$karyawanid."' or a.kodedepartement='".$bagian."' or a.kodetipekaryawan='".$tipekaryawan."' or a.kodejabatan='".$kodejabatan."') and shownotif='0' and readnotif='0' order by a.tanggal desc";
		$res=fetchdata($str);
		$countnotif = $res[0]['count'];
		
		$newxnotif = $xnotif + 1;
		$no=($xnotif*10);
		$str="select a.id,b.namajenis, a.detail, a.readnotif from ".$dbname.".list_notification a 
			left join ".$dbname.".setup_notification_ht b on a.kodenotification = b.kodejenis 
			where (a.karyawanid='".$karyawanid."' or a.kodedepartement='".$bagian."' or a.kodetipekaryawan='".$tipekaryawan."' or a.kodejabatan='".$kodejabatan."') and shownotif='0' order by a.tanggal desc limit ".$no.",10";
		$res=fetchdata($str);
		$countnotif2 = count($res);
		
		$tab.="<hr>";
		foreach($res as $key=>$val){
			$no++;
			if($val['readnotif']=='0'){
				$tagntf = "tagntf";
				$image = "unread.png";
				$title = "Mark as Read";
				$onclick = "markasread('".$val['id']."')";
			}else{
				$tagntf = "tagntfoff";
				$image = "read.png";
				$title = "Mark as Unread";
				$onclick = "markasunread('".$val['id']."')";
			}
			$tab.="<table id='tablenotif_".$val['id']."' width='100%' class='".$tagntf."'>
				<tr>
					<td><label style='color:#365899;font-weight:bold;'>".$val['namajenis']."</label></td>
					<td rowspan=2 style='vertical-align:bottom'><img id='imgnotif_".$val['id']."' style='cursor:pointer' src='images/".$image."' width='20px' title='".$title."' onclick=\"".$onclick."\"></td>
				</tr>
				<tr>
					<td><label style='color:grey;font-style:italic;'>".$val['detail']."</label></td>
				</tr>
			</table>";
			
			if(($countnotif2+($xnotif*10))==$no){}else{
				$tab.="<hr>";
			}
		}
		
		$showmore = 0;
		if($countnotif > ($newxnotif*10)){
			$tab.="<div id='divshowmore_".$newxnotif."'>";
			$showmore = 1;
		}else{
			$showmore = 0;
		}
		
		echo $tab."####".$newxnotif."####".$showmore;
	break;
	
	case'loadnotification':		
		/*$str="select a.id,b.namajenis, a.detail,a.readnotif from ".$dbname.".list_notification a 
			left join ".$dbname.".setup_notification_ht b on a.kodenotification = b.kodejenis 
			where (a.karyawanid='".$karyawanid."' or a.kodedepartement='".$bagian."' or a.kodetipekaryawan='".$tipekaryawan."' or a.kodejabatan='".$kodejabatan."') and shownotif='0' and readnotif='0' order by a.tanggal desc";
		$res=fetchdata($str);
		$countnotif = count($res);*/
		
		$str="select a.id,b.namajenis, a.detail, a.readnotif from ".$dbname.".list_notification a 
			left join ".$dbname.".setup_notification_ht b on a.kodenotification = b.kodejenis 
			where (a.karyawanid='".$karyawanid."' or a.kodedepartement='".$bagian."' or a.kodetipekaryawan='".$tipekaryawan."' or a.kodejabatan='".$kodejabatan."') and shownotif='0' order by a.tanggal desc";
			echo $str;
		$res=fetchdata($str);
		$countnotif2 = count($res);
		$countnotif = 0;
		if($countnotif2 <= 0){
			$tab.="<label style='color:grey'>No Notifications</label>";
		}else{
			$no=0;
			foreach($res as $key=>$val){
				$no++;
				if($val['readnotif']=='0'){
					$countnotif +=1;
					$tagntf = "tagntf";
					$image = "unread.png";
					$title = "Mark as Read";
					$onclick = "markasread('".$val['id']."')";
				}else{
					$tagntf = "tagntfoff";
					$image = "read.png";
					$title = "Mark as Unread";
					$onclick = "markasunread('".$val['id']."')";
				}
				$tab.="<table id='tablenotif_".$val['id']."' width='100%' class='".$tagntf."'>";
					if($no==1){
						$tab.="<tr>
							<td colspan=2 style='text-align:right;border-bottom:1px solid #888'><label style='color:blue;cursor:pointer' title='Read All Notifications' onclick=\"markreadall()\">read all</label></td>
						</tr>";
					}
					$tab.="<tr>
						<td><label style='color:#365899;font-weight:bold;'>".$val['namajenis']."</label></td>
						<td rowspan=2 style='vertical-align:bottom'><img id='imgnotif_".$val['id']."' style='cursor:pointer' src='images/".$image."' width='20px' title='".$title."' onclick=\"".$onclick."\"></td>
					</tr>
					<tr>
						<td><label style='color:grey;font-style:italic;'>".$val['detail']."</label></td>
					</tr>
				</table>";
				
				if($countnotif2==$no){}else{
					$tab.="<hr>";
				}
			}
		}
		
		$lbl ="";
		if($countnotif > 0){
			$lbl = "<label class='badge1' data-badge='".$countnotif."'></label>";
		}
		$result['lbl'] = $countnotif;
		$result['content'] = $tab;
		echo json_encode($result);
	break;
	
	case'readnotif':
		$str="update ".$dbname.".list_notification set readnotif='1' where id='".$id."'";
		try{$owlPDO->exec($str);}catch(PDOException $e){print "Gagal  !: " . $e->getMessage() . "\n"; die();}
		
		$str="select a.id,b.namajenis, a.detail from ".$dbname.".list_notification a 
			left join ".$dbname.".setup_notification_ht b on a.kodenotification = b.kodejenis 
			where (a.karyawanid='".$karyawanid."' or a.kodedepartement='".$bagian."' or a.kodetipekaryawan='".$tipekaryawan."' or a.kodejabatan='".$kodejabatan."') and readnotif='0' order by a.tanggal desc";
		$res=fetchdata($str);
		$countnotif = count($res);
		$lbl ="";
		if($countnotif > 0){
			$lbl = "<label class='badge1' data-badge='".$countnotif."'></label>";
		}
		echo $lbl;
	break;
	
	case'markasread':
		$str="update ".$dbname.".list_notification set readnotif='1' where id='".$id."'";
		try{$owlPDO->exec($str);}catch(PDOException $e){print "Gagal  !: " . $e->getMessage() . "\n"; die();}
		
		$str="select a.id,b.namajenis, a.detail from ".$dbname.".list_notification a 
			left join ".$dbname.".setup_notification_ht b on a.kodenotification = b.kodejenis 
			where (a.karyawanid='".$karyawanid."' or a.kodedepartement='".$bagian."' or a.kodetipekaryawan='".$tipekaryawan."' or a.kodejabatan='".$kodejabatan."') and readnotif='0' order by a.tanggal desc";
		$res=fetchdata($str);
		$countnotif = count($res);
		$lbl ="";
		if($countnotif > 0){
			$lbl = "<label class='badge1' data-badge='".$countnotif."'></label>";
		}
		echo $countnotif;
	break;
	
	case'markasunread':
		$str="update ".$dbname.".list_notification set readnotif='0' where id='".$id."'";
		try{$owlPDO->exec($str);}catch(PDOException $e){print "Gagal  !: " . $e->getMessage() . "\n"; die();}
		
		$str="select a.id,b.namajenis, a.detail from ".$dbname.".list_notification a 
			left join ".$dbname.".setup_notification_ht b on a.kodenotification = b.kodejenis 
			where (a.karyawanid='".$karyawanid."' or a.kodedepartement='".$bagian."' or a.kodetipekaryawan='".$tipekaryawan."' or a.kodejabatan='".$kodejabatan."') and readnotif='0' order by a.tanggal desc";
		$res=fetchdata($str);
		$countnotif = count($res);
		$lbl ="";
		if($countnotif > 0){
			$lbl = "<label class='badge1' data-badge='".$countnotif."'></label>";
		}
		echo $countnotif;
	break;
	
	case'markreadall':
		$str="update ".$dbname.".list_notification set readnotif='1' where karyawanid='".$karyawanid."'";
		try{$owlPDO->exec($str);}catch(PDOException $e){print "Gagal  !: " . $e->getMessage() . "\n"; die();}
		
		$str="select a.id,b.namajenis, a.detail from ".$dbname.".list_notification a 
			left join ".$dbname.".setup_notification_ht b on a.kodenotification = b.kodejenis 
			where (a.karyawanid='".$karyawanid."' or a.kodedepartement='".$bagian."' or a.kodetipekaryawan='".$tipekaryawan."' or a.kodejabatan='".$kodejabatan."') and readnotif='0' order by a.tanggal desc";
		$res=fetchdata($str);
		$countnotif = count($res);
		$lbl ="";
		if($countnotif > 0){
			$lbl = "<label class='badge1' data-badge='".$countnotif."'></label>";
		}
		echo $lbl;
	break;
	
	case'jumlahnotif':
		$tab.='<style>
				.switch {
				  position: relative;
				  display: inline-block;
				  width: 40px;
				  height: 20px;
				}

				.switch input { 
				  opacity: 0;
				  width: 0;
				  height: 0;
				}

				.slider {
				  position: absolute;
				  cursor: pointer;
				  top: 0;
				  left: 0;
				  right: 0;
				  bottom: 0;
				  background-color: #ccc;
				  -webkit-transition: .4s;
				  transition: .4s;
				}

				.slider:before {
				  position: absolute;
				  content: "";
				  height: 18px;
				  width: 18px;
				  left: 1px;
				  bottom: 1px;
				  background-color: white;
				  -webkit-transition: .4s;
				  transition: .4s;
				}

				input:checked + .slider {
				  background-color: #2196F3;
				}

				input:focus + .slider {
				  box-shadow: 0 0 1px #2196F3;
				}

				input:checked + .slider:before {
				  -webkit-transform: translateX(18px);
				  -ms-transform: translateX(18px);
				  transform: translateX(18px);
				}

				/* Rounded sliders */
				.slider.round {
				  border-radius: 20px;
				}

				.slider.round:before {
				  border-radius: 50%;
				}
				</style>';
		
		$str="select a.id,b.namajenis, a.detail, a.readnotif from ".$dbname.".list_notification a 
			left join ".$dbname.".setup_notification_ht b on a.kodenotification = b.kodejenis 
			where (a.karyawanid='".$karyawanid."' or a.kodedepartement='".$bagian."' or a.kodetipekaryawan='".$tipekaryawan."' or a.kodejabatan='".$kodejabatan."') and shownotif='0' order by a.tanggal desc";
		$res=fetchdata($str);
		$countnotif2 = count($res);
		$countnotif = 0;
		if($countnotif2 <= 0){
			$tab.="<center><label style='color:grey'><i>--== No Notifications ==--</i></label></center>";
		}else{
			$no=0;
			foreach($res as $key=>$val){
				$no++;
				$tagntf = "";
				if($val['readnotif']=='0'){
					$countnotif +=1;
					$tagntf = "";
					$image = "unread.png";
					$title = "Mark as Read";
					$onclick = "markasreadx('".$val['id']."')";
				}else{
					$tagntf = "checked=checked";
					$image = "read.png";
					$title = "Mark as Unread";
					$onclick = "markasunreadx('".$val['id']."')";
				}
				$tab.="<div style='width:80%;float:left'>";
				$tab.="<label style='color:grey;font-style:italic;'>".$val['detail']."</label>";
				$tab.="</div>";
				$tab.="<div style='width:20%;float:right'>";
				$tab.="<label class='switch'>";
				$tab.="<input type='checkbox' ".$tagntf." id='chkbx_".$val['id']."' onclick=\"".$onclick."\">";
				$tab.="<span class='slider round' id='spn_".$val['id']."' title='".$title."'></span>";
				$tab.="</div>";
				
				if($countnotif2==$no){}else{
					$tab.="<div style='clear:both'></div>";
					$tab.="<hr>";
				}
			}
		}
		
		$lbl ="";
		if($countnotif > 0){
			$lbl = "<label class='badge1' data-badge='".$countnotif."'></label>";
		}
		$result['lbl'] = $countnotif;
		$result['content'] = $tab;
		echo json_encode($result);
	break;
}