<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');


$method = checkPostGet('method', '');
if(count($_POST)>0){
	$param=$_POST;
}else{
	$param=$_GET;
}
@$param['nilai']  =str_replace(",","",$param['nilai']);


$nmorg= makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');
$nmkar= makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$jab  = getPostingJabatan('budget');

switch ($method) {
    case'html':
        $tab = "<table cellpadding=5 cellspacing=1 border=0 class=sortable  style=width:100%>
            <thead><tr class=rowheader>
             <th align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['tahun'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kodeorg'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kodegolongan'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kodegolongan'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nilai'] . "</th>
		</tr></thead>";
        $no = 0;
        $str = "select * from " . $dbname . ".bgt_upah where tahunbudget='".$param['tahun']."' and kodeorg like '".$param['kodeorg']."%'";
        $res = fetchdata($str);
        foreach($res as $bar){
			$nmgol= makeOption($dbname, 'bgt_kode', 'kodebudget,nama',"kodebudget='".$bar['golongan']."'");
			
            $no+=1;
            $tab.="<tr class=rowcontent style=height:25px>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['tahunbudget'] . "</td>";
            $tab.="<td align=left>".$nmorg[$bar['kodeorg']]."</td>";
            $tab.="<td align=left>" . $bar['golongan'] . "</td>";
            $tab.="<td align=left>" . $nmgol[$bar['golongan']] . "</td>";
            $tab.="<td align=right>" . @number_format($bar['jumlah'], 2) . "</td>";
        }
        $tab.="</tr>";
        $tab.="</table>";
        echo $tab;
	break;
    case'insert':
		try {
			$owlPDO->beginTransaction();
		
			$sql = "select * from " . $dbname . ".bgt_upah where tahunbudget='" . $param['tahun'] . "' and kodeorg ='" . $param['kodeorg'] . "' and closed=1";
			$res = fetchdata($sql);
			$jlhbrs = count($res);
			if ($jlhbrs > 0) {
				throw new PDOException("Data sudah di posting / tutup.");
			}
			
			$sql = "select * from " . $dbname . ".bgt_upah where tahunbudget='" . $param['tahun'] . "' and kodeorg ='" . $param['kodeorg'] . "' and  golongan ='" . $param['golongan'] . "'";
			$res = fetchdata($sql);
			$jlhbrs = count($res);
			if ($jlhbrs > 0) {
				throw new PDOException("Data sudah ada.");
			}
			
			$data = array(
				'tahunbudget'=> $param['tahun'],
				'kodeorg'    => $param['kodeorg'],
				'golongan'   => $param['golongan'],
				'jumlah'     => $param['nilai'],
				'updateby'   => $_SESSION['standard']['userid'],
				'lastupdate' => date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'bgt_upah',$data,$cols);
			$owlPDO->exec($query);
			
		
			$owlPDO->commit();
			} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
			
	break;
    case'delete':
		$sql = "select * from " . $dbname . ".bgt_upah where tahunbudget='" . $param['tahun'] . "' and kodeorg ='" . $param['kodeorg'] . "' and closed=1";
		$res = fetchdata($sql);
		$jlhbrs = count($res);
		if ($jlhbrs > 0) {
			exit("Warning : Data sudah di posting / tutup.");
		}
		
        $str = "delete from " . $dbname . ".bgt_upah where tahunbudget='" . $param['tahun'] . "' and kodeorg ='" . $param['kodeorg'] . "'";
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
    case'deletedetail':
		$str = "delete from " . $dbname . ".bgt_upah where tahunbudget='" . $param['tahun'] . "' and kodeorg ='" . $param['kodeorg'] . "' and golongan='".$param['golongan']."'"; #exit("error".$str);
		try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
	case'updatedetail':
	
	   try {
			$owlPDO->beginTransaction();
		
			$sql = "select * from " . $dbname . ".bgt_upah where tahunbudget='" . $param['tahun'] . "' and kodeorg ='" . $param['kodeorg'] . "' and closed=1";
			$res = fetchdata($sql);
			$jlhbrs = count($res);
			if ($jlhbrs > 0) {
				throw new PDOException("Data sudah di posting / tutup.");
			}
			
			$data = array(
				'jumlah'     => $param['nilai'],
				'updateby'   => $_SESSION['standard']['userid'],
				'lastupdate' => date('Y-m-d H:i:s')
			);
			
			$where = "tahunbudget='".$param['tahun']."' and kodeorg='".$param['kodeorg']."' and golongan='".$param['golongan']."'";
		
			$query = updateQuery($dbname,'bgt_upah',$data,$where);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
			} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
		
		break;
    case'posting':
		 try {
			$owlPDO->beginTransaction();
				
			$str = "select * from " . $dbname . ".bgt_upah where tahunbudget='".$param['tahun']."' and kodeorg='".$param['kodeorg']."' and jumlah>'0'";
			$res = fetchdata($str);
			foreach($res as $bar){
				$sql = "select * from " . $dbname . ".bgt_budget where tahunbudget='" . $param['tahun'] . "' and kodeorg like '" . $param['kodeorg'] . "%' and kodebudget='".$bar['golongan']."' and jumlah>'0' and pta='BGT'";
				$req = fetchdata($sql);
				$rupiah=0; $fisik=0;
				foreach($req as $val){
					if($val['tipebudget']=='TRK' || $val['tipebudget']=='WS'){
						$rupiah=round($bar['jumlah']*$val['volume']);
						$fisik=$val['volume'];
					}else{
						$rupiah=round($bar['jumlah']*$val['jumlah']);						
						$fisik=$val['jumlah'];
					}
					if($rupiah>0){
						$query = "update " . $dbname . ".bgt_budget set rupiah='".$rupiah."' where kunci='".$val['kunci']."'";
						$owlPDO->exec($query);
					}
					
					$ttlpersen = 0; $persen=[];
					$sql = "select * from " . $dbname . ".bgt_distribusi where kunci='".$val['kunci']."'";
					$req = fetchdata($sql);
					if(count($req)>0){
						foreach($req as $val){
							for($i=1;$i<=12;$i++){
								$ttlpersen+=$val['rp'.addZero($i,2)];
								$persen[$i]=$val['rp'.addZero($i,2)];
							}
						}
						
						$str = "delete from " . $dbname . ".bgt_distribusi where kunci='".$val['kunci']."'";
						$owlPDO->exec($str);
						
						$str = "insert into ".$dbname.".bgt_distribusi (`kunci`";
						for($i=1;$i<=12;$i++){
							$str.=",`rp".addZero($i,2)."`";
							$str.=",`fis".addZero($i,2)."`";
						}
						$str.=") values('".$val['kunci']."'";
						for($i=1;$i<=12;$i++){
							$str.=",'".$persen[$i]/$ttlpersen*$rupiah."'";
							$str.=",'".$persen[$i]/$ttlpersen*$fisik."'";
						}
						$str.=");";
						$owlPDO->exec($str);
					}
				}
			}
			$str = "update " . $dbname . ".bgt_upah set closed='1' where tahunbudget='".$param['tahun']."' and kodeorg='".$param['kodeorg']."'";
			$owlPDO->exec($str);
			
			#unposting ws
			$str = "update " . $dbname . ".bgt_budget set tutup='0' where tahunbudget = '".$param['tahun']."' and kodeorg like '".$param['kodeorg']."%' and tipebudget='WS'"; //exit("error".$str);
			$owlPDO->exec($str);
			
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Error, " . addslashes($e->getMessage()); die();}
	break;
	case'unposting':
		$str = "update " . $dbname . ".bgt_upah set closed='0' where tahunbudget='".$param['tahun']."' and kodeorg='".$param['kodeorg']."'";
        try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
	break;
    case'loaddatadetail':
        $tab = "
			<table cellpadding=5 cellspacing=1 border=0 class=sortable>
            <thead><tr class=rowheader>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nourut'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['tahun'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kodeorg'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kodegolongan'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['kodegolongan'] . "</th>
            <th align=center rowspan='2'>" . $_SESSION['lang']['nilai'] . "</th>
            <th align=center rowspan='2' colspan=2>" . $_SESSION['lang']['action'] . "</th>
		</tr></thead>";
		
        $no = 0;
        $str = "select * from " . $dbname . ".bgt_upah where tahunbudget='".$param['tahun']."' and kodeorg like '".$param['kodeorg']."%'";
        $res = fetchdata($str);
        foreach($res as $bar){
			$nmgol= makeOption($dbname, 'bgt_kode', 'kodebudget,nama',"kodebudget='".$bar['golongan']."'");
			
            $no+=1;
            $tab.="<tr class=rowcontent style=height:25px>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['tahunbudget'] . "</td>";
            $tab.="<td align=left>".$nmorg[$bar['kodeorg']]."</td>";
            $tab.="<td align=left>" . $bar['golongan'] . "</td>";
            $tab.="<td align=left>" . $nmgol[$bar['golongan']] . "</td>";
            $tab.="<td align=right>" . @number_format($bar['jumlah'], 2) . "</td>";
			if($bar['closed']=='0'){				
				$tab.="<td align=center width=25px>
						<img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"editdetail('" . $bar['tahunbudget'] . "','" . $bar['kodeorg'] . "','" . $bar['golongan'] . "','" . $bar['jumlah'] . "');\" ></td>";
				$tab.="<td align=center width=25px>	
						<img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"deletedetail('" . $bar['tahunbudget'] . "','" . $bar['kodeorg'] . "','" . $bar['golongan'] . "');\" >
						</td>";
			}else{
				$tab.="<td align=center width=25px></td>";
				$tab.="<td align=center width=25px></td>";
			}
        }
        $tab.="</tr>";
        $tab.="</table>";
		
        echo $tab;
	break;
	case'getbgtkode':
		$optgol="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$nmtipe= makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$param['kodeorg']."'");
		
		if($nmtipe[$param['kodeorg']]=='PABRIK'){	
			$where="and kodebudget like 'EXPL-%'";
		}elseif($nmtipe[$param['kodeorg']]=='BULKING'){	
			$where="and kodebudget like 'EXPLBULK%'";
		}else{
			$where="and kodebudget like 'SDM%'";
		}

		$str="select * from ".$dbname.".bgt_kode where 1=1 ".$where." order by kodebudget asc ";
		$res=fetchdata($str);
		foreach($res as $bar){
			$s="";
			if($param['golongan']==$bar['kodebudget']){
				$s="selected";
			}
			$optgol.="<option value=".$bar['kodebudget']." ".$s.">".$bar['nama']."</option>";
		}
		echo $optgol;
	break;
    case'loaddata':
        $where = "";
		// if($_SESSION['empl']['tipelokasitugas']=='HOLDING'){
			// $where = "";
		// } else if ($_SESSION['empl']['tipelokasitugas']=='KANWIL'){
			// $where = " and kodeorg in (select kodeorganisasi from ".$dbname.".organisasi where induk = '".$_SESSION['empl']['kodeorganisasi']."')";
		// } else {
			// $where = " and kodeorg = '".$_SESSION['empl']['lokasitugas']."'";
		// }
		
		$where = " and kodeorg in (".getOrgDetail(2).")";
		
		if($param['tahun']!=''){
			$where.=" and tahunbudget = '".$param['tahun']."'";
		}
		if($param['kodeorg']!=''){
			$where.=" and kodeorg = '".$param['kodeorg']."'";
		}
		
		
        $limit = 15;
        $page = 0;
        $_POST['page'] = isset($_POST['page']) ? $_POST['page'] : '0';
        if (isset($_POST['page'])) {$page = $_POST['page'];if ($page < 0){$page = 0;}}
		
        $offset = $page * $limit;
        $maxdisplay = ($page * $limit);
		
        $sql = "select count(*) as jmlhrow from " . $dbname . ".bgt_upah where 1=1 " . $where . " group by tahunbudget,kodeorg";
        $res = fetchdata($sql);
        $jlhbrs = count($res);
		
        $no = 0;
        $tab = "";
        $no = $maxdisplay;
		$colspan=8;
		
        $str = "SELECT * FROM " . $dbname . ".bgt_upah where 1=1 " . $where . " group by tahunbudget,kodeorg order by tahunbudget desc, kodeorg asc limit " . $offset . "," . $limit . "";
        $res = fetchdata($str);
        foreach($res as $bar){
            $no+=1;
            $tab.="<tr class=rowcontent style=height:25px id=tr_$no>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=center>" . $bar['tahunbudget'] . "</td>";
            $tab.="<td>" . $nmorg[$bar['kodeorg']] . "</td>";
            $tab.="<td>" . $nmkar[$bar['updateby']] . "</td>";
			
            if($bar['closed'] == 0) {
                $tab.="<td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"edit('" . $bar['tahunbudget'] . "','" . $bar['kodeorg'] . "');\" ></td>";
                $tab.="<td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"del('" . $bar['tahunbudget'] . "','" . $bar['kodeorg'] . "');\" ></td>";
				$tab.="<td align=center width=25px><img src=images/icons/04/16/01.png class=zImgBtn class=zImgBtn height='30'  title='Close ???' onclick=\"posting('" . $bar['tahunbudget'] . "','" . $bar['kodeorg'] . "');\" ></td>";
            } else {
				if(in_array($_SESSION['empl']['jabatan'],$jab)){
					$icon="images/icons/04/16/04.png";
					$title="Unposting";
					$unpost=" onclick=\"unposting('" . $bar['tahunbudget'] . "','" . $bar['kodeorg'] . "');\" ";
				}else {
					$icon="images/icons/04/16/02.png";
					$title="Posted";
					$unpost='';
				}
				$tab.="<td align=center width=25px></td><td align=center width=25px></td>";
                $tab.="<td align=center width=25px><img src=".$icon." class=zImgBtn class=zImgBtn height='30'  title='".$title."' ".$unpost." ></td>";
            }
            $tab.="<td align=center width=25px><img src=images/skyblue/zoom.png class=zImgBtn class=zImgBtn height='30'  title='View HTML' 
                    onclick=\"html('" . $bar['tahunbudget'] . "','" . $bar['kodeorg'] . "');\" ></td>";
            $tab.="</tr>";
        }
        
		## PAGING
		$tab.=createpaging($jlhbrs,$limit,$page,$colspan,'loaddata','getPage');
		
        echo $tab . "####" . $footd;
        break;
}
?>	