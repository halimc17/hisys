<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$nama = checkPostGet('nama', '');
$jabatan = checkPostGet('jabatan', '');
$status = checkPostGet('status', '');
$method = checkPostGet('method', '');
$optKarid = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

$param = $_POST;
if (count($param) == 0) {
    $param = $_GET;
}

$arrStatus = array('1' => 'AKTIF', '0' => 'NON AKTIF');

switch($method){
    case'loaddata':
        $tab.="<table id=mytable class='sortable'  cellspacing='1' cellpadding='5' border='0' width=100%>
                <thead>
                <tr class=rowheader>
                    <th align=center>".$_SESSION['lang']['nourut']."</th>
                    <th align=center>".$_SESSION['lang']['nama']."</th>
                    <th align=center>".$_SESSION['lang']['jabatan']."</th>
                    <th align=center>".$_SESSION['lang']['status']."</th>
                    <th align=center>".$_SESSION['lang']['updateby']."</th>
                    <th class='no-sort' align=center>".$_SESSION['lang']['action']."</th>
                </tr>
                </thead>
            <tbody>";

        $no = 0;
        $str = "select * from ".$dbname.".pmn_5ttd order by status desc, nama asc, jabatan asc";
        $res = fetchdata($str);
        foreach ($res as $val) {
            $no += 1;
            $tab.="<tr class=rowcontent>";
            $tab.="<td style='text-align: center;'>".$no."</td>";
            $tab.="<td style='text-align: left;'>".getNamaKaryawan($val['nama'])."</td>";
            $tab.="<td style='text-align: center;'>".$val['jabatan']."</td>";
            $tab.="<td nowrap style='text-align:center'>".$arrStatus[$val['status']]."</td>";
            $tab.="<td style='text-align: center;'>".getNamaKaryawan($val['updateby'])."</td>";
            $tab.="<td style='text-align: center;' class='no-sort'>
				<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"editdata('".$val['nama']."','".$val['jabatan']."','".$val['status']."');\">
            </td>";
        }
        $tab.="</tr></tbody></table>";

        echo $tab;
    break;
	
	case 'create':
        $tab="";
        foreach ($arrStatus as $key=>$val) {
            if ($key == '1') {
                $optstatus.="<option value=".$key." selected>".$val."</option>";
            } else {
                $optstatus.="<option value=".$key.">".$val."</option>";
            }
        }
        
        ## GET NAMA
		$optNama.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $strNama = "SELECT DISTINCT karyawanid, namakaryawan FROM ".$dbname.".datakaryawan order by namakaryawan asc";
        $resNama = fetchdata($strNama);
        foreach ($resNama as $val) {
            $optNama.="<option value=".$val['karyawanid'].">".$val['namakaryawan']."</option>";
        }

        

        $tab.="<table border=0 cellpadding=3 cellspacing=1>
            <tr>
                <td>".$_SESSION['lang']['nama']."</td>
                <td>:</td>
                <td>
                    <select class='select2' id='nama' onchange='getJabatan(this.value)'>".$optNama."</select>
                </td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['jabatan']."</td>
                <td>:</td>
                <td>
                    <input type=text id=jabatan class=myinputtext style='text-align: left; height: 25px; font-size: 14px; width: 260px'>
                </td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['status']."</td>
                <td>:</td>
                <td>
                    <select class='select2' id='status'>".$optstatus."</select>
                </td>
            </tr>
            <tr>
                <td><input type=hidden id=method value=insert></td>
                <td colspan=4>
                    <button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
                </td>
            </tr>
        </table>";

        echo $tab;
    break;
    
    case 'insert':
		try{
			$owlPDO->beginTransaction();
			
			## VALIDASI
			$str="select count(nama) as jlhitem from ".$dbname.".pmn_5ttd where nama='".$nama."'";
			$res=fetchdata($str);
			$jlhitem=$res[0]['jlhitem'];
			
			if($jlhitem > 0){
				throw new PDOException('Nama sudah pernah terdaftar sebelumnya.');
			}
			
			$i="insert into ".$dbname.".pmn_5ttd (nama, jabatan, status, createby, createtime, updateby)
            values ('".$nama."','".$jabatan."','".$status."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."')";
			$owlPDO->exec($i);
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning, " . addslashes($e->getMessage());
			die();
		}
    break; 

	case 'delete':
	    //exit("Error:hahaha");
	    $i = "DELETE FROM ".$dbname.".pmn_5ttd where nama='".$nama."'";
		echo $i;
        //exit("Error.$str");
		/*if(mysql_query($i))
		echo"";
		else
		echo " Gagal,".addslashes(mysql_error($conn));*/
        try{$owlPDO->exec($i); 
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n"; 
            die(); 
        }
	break;

    case 'update':
        try {
            $owlPDO->beginTransaction();
            $str = "update ".$dbname.".pmn_5ttd set status = '".$status."', jabatan='".$jabatan."', updateby='".$_SESSION['standard']['userid']."' where nama='".$nama."'";
            $owlPDO->exec($str);

            $owlPDO->commit();
        } catch (PDOException $e) {
            $owlPDO->rollBack();
            echo "Errorcode, " . addslashes($e->getMessage());
        }
    break;

    case 'getJabatan':
        ## GET NAMA JABATAN
        $kodejabatan = '';
        $strJbtn = "SELECT b.namajabatan FROM ".$dbname.".datakaryawan as a join ".$dbname.".sdm_5jabatan as b on b.kodejabatan = a.kodejabatan where a.karyawanid = '".$param['id']."'";
		$resJbtn = fetchdata($strJbtn);
        $kodejabatan = $resJbtn[0]['namajabatan'];
		
		echo $kodejabatan;
    break;
}
?>