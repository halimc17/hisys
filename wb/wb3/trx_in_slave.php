<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

switch($method){
	case 'generatenotiket':
        echo generatenotiket();
    break;
	
	case 'getkontrak':
        $optkontrak="<option value=''>Silahkan pilih</option>";

		## GET KONTRAK
		$str="select * from ".$dbname.".mscontractpurchase";
		$res=fetchdata($str);
		foreach ($res as $val) {
			$optkontrak.="<option value='".$val['ctrno']."'>".$val['ctrno']."</option>";
		}
        
        echo $optkontrak;
    break;
	
	case 'loadData':
		$strx="select * from ".$dbname.".mssystem limit 1";
		$res=fetchdata($strx);
		$idwb=$res[0]['idwb'];
		
		$wherekodeproduk = "and kodeproduk='".$param['kodeproduk']."' ";

		$str="select * from ".$dbname.".trxtimbang where idwb='".$idwb."' and timbang2 = 0 ".$wherekodeproduk."";

		$res=fetchdata($str);
		echo "
		<div class=table-scroll style='height:200px'>
			<table class=sortable></center>
				<thead>
				<tr class=rowheader>
					<th align=center><b>No. Tiket</b></th>
					<th align=center><b>No. SPB</b></th>
					<th align=center><b>Produk</b></th>
					<th align=center><b>Tgl Masuk</b></th>
					<th align=center><b>Timbang I</b></th>
					<th align=center><b>Timbang II</b></th>
				</tr>
				</thead>
				<tbody>";
		if ($res) {
			foreach ($res as $val) {
				echo"
				<tr class=rowcontent onmouseover=\"this.style.backgroundColor='#00FF00';\" onmouseout=\"this.style.backgroundColor='#FFFFFF';\" style='cursor:pointer;' title='Click' onclick=\"fillfield('".$val['notiket']."');\">
				<td align=center>".$val['notiket']."</td>
				<td align=center>".$val['nospb']."</td>
				<td align=center>".$nmproduk[$val['kodeproduk']]."</td>
				<td align=center>".substr($val['waktumasuk'],0,10)." ".substr($val['waktumasuk'],11,8)."</td>
				<td align=center>".$val['timbang1']."</td>
				<td align=center>".$val['timbang2']."</td>
				</tr>";
			}
		}else{
			echo "<tr class=rowcontent>
			<td colspan=10 align=center>Data kosong</td>
			</tr>";
		}
		echo "
		</tbody>
		</table>
		</div>";
	break;
	
	case 'addnew':
		$tab="";
		foreach($arrstatus as $key => $val){
			if($key=='1'){
				$optstatus.="<option value=".$key." selected>".$val."</option>";							
			}else{
				$optstatus.="<option value=".$key.">".$val."</option>";							
			}
		}
		
		## GET PERUSAHAAN
		$no=0;
		$kodeperusahaanx="";
		$str="select compcode,compname from ".$dbname.".mscompany where compstatus='1' order by compname asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$no++;
			if($no==1){
				$optperusahaan.="<option value='".$val['compcode']."' selected>".$val['compname']."</option>";
				$kodeperusahaanx=$val['compcode'];
			}else{
				$optperusahaan.="<option value='".$val['compcode']."'>".$val['compname']."</option>";				
			}
		}
		
		## GET UNIT
		$str="select unitcode,unitname from ".$dbname.".msunit where unitstatus='1' and compcode='".$kodeperusahaanx."' order by unitname asc";
		$res=fetchdata($str);
		foreach($res as $val){
			$optunit.="<option value='".$val['unitcode']."'>".$val['unitname']."</option>";	
		}
	
		$tab.="
			 <table border=0 cellpadding=2 cellspacing=1>
				<tr>
					<td style=min-width:150px>Perusahaan</td>
					<td>
						<select class='select2' style='width:350px;height:30px;' onchange='getunit(this.value)' id='kodeperusahaan'>".$optperusahaan."</select>
					</td>
				</tr>
				<tr>
					<td style=min-width:150px>Unit</td>
					<td>
						<select class='select2' style='width:350px;height:30px;' id='kodeunit'>".$optunit."</select>
					</td>
				</tr>
				<tr>
					<td style=min-width:150px>Kode Divisi</td>
					<td>
						<input class=myinputtext maxlength=6 style='width:70px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=kode>
					</td>
				</tr>
				<tr>
					<td>Nama Divisi</td>
					<td>
						<input class=myinputtext maxlength=50 style='width:345px;height:30px;font-size:14px;' onkeydown='upperCaseF(this)' id=deskripsi>
					</td>
				</tr>
				<tr>
					<td>Status</td>
					<td>
						<select class='select2' style='width:350px;height:30px;' id=status >".$optstatus."</select>
					</td>
				</tr>
                <tr>
                    <td><input type=hidden id=method value=insert></td>
                    <td colspan=4>
						<button onclick=simpan(); style='width:150px;height:30px' class=mybutton>Save</button>
						
                    </td>
                </tr>
            </table>
		";
		echo $tab;
	break;
	
	case 'insert':
		try{
			$owlPDO->beginTransaction();
			
			$data = array(
				'compcode'		=> $param['kodeperusahaan'],
				'unitcode' 		=> $param['kodeunit'],
				'divcode' 		=> $param['kode'],
				'divname' 		=> $param['deskripsi'],
				'divstatus' 	=> $param['status'],
				'updateby' 		=> $_SESSION['standard']['username'],
				'createby'		=> $_SESSION['standard']['username'],
				'createtime'	=> date('Y-m-d H:i:s')
			);
			
			$cols = array();
			foreach($data as $key=>$row) {
				$cols[] = $key;
			}

			$query = insertQuery($dbname,'msdivisi',$data,$cols);
			$owlPDO->exec($query);
		
			$owlPDO->commit();
		}catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;
	
	case 'update':
		try {
			$owlPDO->beginTransaction();
				$data = array(
					'divname'	=> $param['deskripsi'],
					'divstatus'=> $param['status'],
					'updateby'	=> $_SESSION['standard']['username']
				);
				$where = "divcode='".$param['kode']."'";
				$query = updateQuery($dbname,'msdivisi',$data,$where); #exit("warningcode".$query);
				$owlPDO->exec($query);
				
			$owlPDO->commit();
		} catch (PDOException $e) {$owlPDO->rollback(); echo "Errorcode, " . addslashes($e->getMessage()); die();}	
	break;	
	
	case 'getunit':
		$str="select unitcode,unitname from ".$dbname.".msunit where unitstatus='1' and compcode='".$param['kodeperusahaan']."' order by unitname asc";
		$res = fetchdata($str);
		$res=fetchdata($str);
		foreach($res as $val){
			if($param['kodeunit']==$val['unitcode']){$sel="selected";}
			$optperusahaan.="<option value='".$val['unitcode']."' ".$sel.">".$val['unitname']."</option>";					
		}
		
		echo $optperusahaan;
	break;
}

function generatenotiket(){
    global $dbname;
    ##generate notiket
    $str2="select * from ".$dbname.".mssystem limit 1";
    $res2=fetchdata($str2);
    $idwb=$res2[0]['idwb'];

    $str="select distinct RIGHT(notiket,6) as notiket from ".$dbname.".trxtimbang";
    $res=fetchdata($str);
    if(!$res)
    {
        $no_1=1;
        $no=str_pad($no_1,6,"0",STR_PAD_LEFT);
    }
    else
    {   
        $str2="select RIGHT(notiket,6) as notiket from ".$dbname.".trxtimbang where idwb='".$idwb."' order by notiket desc limit 1";
        $res2=fetchdata($str2);
        if ($res2){
            $ticketno=$res2[0]['notiket'];
            $no_1=intval($ticketno)+1;
            $no=str_pad($no_1,6,"0",STR_PAD_LEFT);
        }
        else
        {
            $no3=1;
            $no=str_pad($no3,6,"0",STR_PAD_LEFT);
        }
    }
    return $idwb."".$no;

}
?>
