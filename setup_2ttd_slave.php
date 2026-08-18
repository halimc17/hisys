<?php

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/rTable.php');
include_once('lib/paging.php');


$method = checkPostGet('method','');
$param = $_POST;
if(count($param)==0){
	$param = $_GET;
}


switch ($method) {
   
    case 'insert':
        $str = "insert into " . $dbname . ".setup_2ttd (menuid,kodeunit,judul,karyawanid,jabatan,createby,createtime,updateby)
            values ('".$param['menuid']."','".$param['kodeunit']."','".$param['judul']."','".$param['karyawanid']."','".$param['jabatan']."','" . $_SESSION['standard']['userid'] . "','" . date('Y-m-d H:i:s') . "','" . $_SESSION['standard']['userid'] . "')";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            echo " Gagal Insert," . addslashes($e->getMessage());
        }
    break;

    case 'update':
        $str = "update " . $dbname . ".setup_2ttd set menuid='" . $param['menuid'] . "',kodeunit='" . $param['kodeunit'] . "',judul='".$param['judul']."',karyawanid='" . $param['karyawanid'] . "',jabatan='" . $param['jabatan'] . "',updateby='" . $_SESSION['standard']['userid'] . "' where id='" . $param['id'] . "'";
		
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            echo " Gagal Update," . addslashes($e->getMessage());
        }

    break;
	case 'addnew':
		$optunit = $optmenu=$optkaryawan= "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
		
		if($param['id']!=''){			
			$str = "select * from " . $dbname . ".setup_2ttd where id='".$param['id']."'";
			$res=fetchdata($str);
			foreach($res as $bar){
				$kodeunit  =$bar['kodeunit'];
				$karyawanid=$bar['karyawanid'];
				$judul     =$bar['judul'];
				$jabatan   =$bar['jabatan'];
				$menuid    =$bar['menuid'];
				$id        =$bar['id'];
			}
		}
		
		
		$arrunit=array();
		$arrunit=getOrgDetail(1);
		foreach($arrunit as $val=>$nama){
			if($kodeunit==$val){
				$select="selected";
			}else{
				$select="";
			}
			$optunit.="<option value='".$val."' ".$select.">".$val." - ".$nama."</option>";
			$dtunit[$val]=$val;
		} 




		$str = "select menuid FROM ".$dbname.".auth where namauser='".$_SESSION['standard']['username']."' and status=1";
		$res = fetchdata($str);
		foreach ($res as $bar) {
		   $arrmenu[$bar['menuid']]=$bar['menuid'];
		}

		$str = "select * FROM ".$dbname.".menu where id in (select menuid FROM ".$dbname.".auth where namauser='".$_SESSION['standard']['username']."' and status=1) and class='click' and action!='' ";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if($menuid==$bar['id']){
				$select="selected";
			}else{
				$select="";
			}
			$optmenu .= "<option value='".$bar['id']."'  ".$select.">".$bar['id']." - ".getMenu($bar['action'],'x')."</option>";
		}

		$str = "select * FROM ".$dbname.".datakaryawan where lokasitugas in (".getOrgDetail(2).") order by namakaryawan asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if($karyawanid==$bar['karyawanid']){
				$select="selected";
			}else{
				$select="";
			}
			$optkaryawan .= "<option value='".$bar['karyawanid']."'  ".$select.">".$bar['nik']." - ".$bar['namakaryawan']." - ".$bar['lokasitugas']."</option>";
		}

		$str = "select * FROM ".$dbname.".datakaryawan where tipekaryawan=0 order by namakaryawan asc";
		$res = fetchdata($str);
		foreach ($res as $bar) {
			if($karyawanid==$bar['karyawanid']){
				$select="selected";
			}else{
				$select="";
			}
			$optkaryawan .= "<option value='".$bar['karyawanid']."'  ".$select.">".$bar['nik']." - ".$bar['namakaryawan']." - ".$bar['lokasitugas']."</option>";
		}
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td>".$_SESSION['lang']['menu']."</td>
					<td><select class='select2' style='width:500px;' id=menuid >".$optmenu."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['judul']."</td>
					<td><input class=myinputtext style='width:495px;height:30px;font-size:14px;' nkeypress=\"return tanpa_kutip(event);\" id=judul value=".$judul."></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['jabatan']."</td>
					<td><input class=myinputtext style='width:495px;height:30px;font-size:14px;' nkeypress=\"return tanpa_kutip(event);\" id=jabatan value=".$jabatan."></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['unit']."</td>
					<td><select class='select2' style='width:500px;' id=kodeunit >".$optunit."</select></td>
				</tr>
				<tr>
					<td>".$_SESSION['lang']['namakaryawan']."</td>
					<td><select class='select2' style='width:500px;' id=karyawanid >".$optkaryawan."</select></td>
				</tr>
                <tr>
                    <td colspan=40 align=center>
						<input type=hidden id=method value='insert'><input type=hidden id=id value=".$id.">
						<button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
						
                    </td>
                </tr>
            </table>
		";
		echo $tab;
	break;
	
    case 'loaddata':
        $where = '';
		
		$arrunit=getOrgDetail(1);
		foreach($arrunit as $val=>$nama){
			$dtunit[$val]=$val;
		} 
		$where=" 1=1 and  kodeunit in ('".implode("','",$dtunit)."') ";
		
        if ($param['kodeunit'] != '') {
            $where .= " AND kodeunit = '".$param['kodeunit']."'";
        }
        if ($param['menuid'] != '') {
            $where .= " AND menuid = '".$param['menuid']."'";
        }
		
        $tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
        <thead>
           <tr class=rowheader>
                <th rowspan=2 align=center>No</th>
                <th rowspan=2 align=center>" . $_SESSION['lang']['menu'] . " </th>
                <th rowspan=2 align=center>" . $_SESSION['lang']['unit'] . "</th>
                <th rowspan=2 align=center>" . $_SESSION['lang']['judul'] . "</th>
                <th rowspan=2 align=center>" . $_SESSION['lang']['namakaryawan'] . "</th>
                <th rowspan=2 align=center>" . $_SESSION['lang']['jabatan'] . " </th>
                <th rowspan=2 align=center>" . $_SESSION['lang']['updateby'] . "</th>
                <th align=center colspan=2>" . $_SESSION['lang']['action'] . " </th>
            </tr>
			<tr class=rowheader>
				<th style=display:none></th>
				<th style=display:none></th>
            </tr>
		</thead>
		<tbody>";

        // $limit = 10;
        // $page = 1;
        // $p = new Paging;

        // if (isset($_POST['page'])) {
            // $page = $_POST['page'];
            // if ($page < 1)
                // $page = 1;
        // }
        // // $offset = $page * $limit;
        // $maxdisplay = ($page * $limit);
        // $offset = $p->cariPosisi($limit,$page);

        // $ql2 = "select count(*) as jmlhrow from " . $dbname . ".setup_2ttd WHERE ".$where.""; // echo $ql2;notran
        // $query2 = $owlPDO->query($ql2) or die(print " Gagal: " . PDOException::getMessage());
        // $query2->setFetchMode(PDO::FETCH_OBJ);
        // while ($jsl = $query2->fetch()) {
            // $jlhbrs = $jsl->jmlhrow;
        // }
        // $jml = $p->jumlahHalaman($jlhbrs,$limit);
        // //  limit " . $offset . "," . $limit . "
        // $no = $offset;
        // //$str = "select * from " . $dbname . ".setup_2ttd WHERE  ".$where." ORDER BY menuid,kodeunit asc limit ".$offset.",".$limit;
        $str = "select * from " . $dbname . ".setup_2ttd WHERE  ".$where." ORDER BY menuid,kodeunit asc";
		$res=fetchdata($str);
		foreach($res as $bar) {
			$no++;
            $nmkaryawan  = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan',"karyawanid in ('".$bar['createby']."','".$bar['updateby']."','".$bar['karyawanid']."')");
            $nikkaryawan  = makeOption($dbname, 'datakaryawan', 'karyawanid,nik',"karyawanid in ('".$bar['createby']."','".$bar['updateby']."','".$bar['karyawanid']."')");
            $nmmenu  = makeOption($dbname, 'menu', 'id,action',"id='".$bar['menuid']."'");
            $tab.="<tr class=rowcontent>";
            $tab.="<td align=center>" . $no . "</td>";
            $tab.="<td align=left >".$bar['menuid']." - ".getMenu($nmmenu[$bar['menuid']],'x')."</td>";
            $tab.="<td align=left >".$bar['kodeunit']."</td>";
            $tab.="<td align=left >".$bar['judul']."</td>";
            $tab.="<td align=left >".$nikkaryawan[$bar['karyawanid']]." - ".$nmkaryawan[$bar['karyawanid']]."</td>";
            $tab.="<td align=left >" . $bar['jabatan'] . "</td>";
			$tab.="<td align=left >".$nmkaryawan[$bar['updateby']]."</td>";
			$tab.="<td align=center><img src=images/application/application_edit.png class=resicon  caption='Edit' onclick=\"edit('" . $bar['id'] . "');\"></td>";
			$tab.="<td align=center><img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('" . $bar['id'] . "');\"></td>";
            $tab.="</tr>"; //<img src=images/application/application_delete.png class=resicon  caption='Delete' onclick=\"del('".$d['parameter']."');\">
        }
        // echo "<tr class=rowheader>
              // <td colspan=16 align=center>".($offset+1)." to ".($page*$limit)." of ". $jlhbrs."<br />";
        // $buttonaction = array(
            // 'first' =>  'onclick="loaddata(1);"',
            // 'prev'  =>  'onclick="loaddata('.($page-1).');"',
            // 'next'  =>  'onclick="loaddata('.($page+1).');"',
            // 'last'  =>  'onclick="loaddata('.($jml).')"',
            // 'pages' =>  'id="pages" name="pages" onchange="loaddata(this.value);"'
        // );
        // echo $p->navHalaman($page,$jml,$buttonaction);
        // echo "</td></tr>";

        $tab.="</tbody><tfoot>
		</tfoot></table>";
		
		echo $tab;
    break;

    case 'delete':
        $str = "delete from " . $dbname . ".setup_2ttd where id='" . $param['id'] . "'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            echo " Gagal Delete," . addslashes($e->getMessage());
        }
    break;

    case 'edit':
      
        $str = "select * from " . $dbname . ".setup_2ttd where id='".$param['id']."'";
		$res=fetchdata($str);
		foreach($res as $bar){
			$kodeunit=$bar['kodeunit'];
			$karyawanid=$bar['karyawanid'];
			$judul=$bar['judul'];
			$jabatan=$bar['jabatan'];
			$menuid=$bar['menuid'];
			$id=$bar['id'];
		}
        echo $menuid."###".$kodeunit."###".$judul."###".$karyawanid."###".$jabatan."###".$id;
		// exit("Error:A");
    break;

  
    default:
}
?>
