<?
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');

$tahun   = checkPostGet('tahun','');
$unit    = checkPostGet('unit','');
$rpnya   = checkPostGet('rpnya','');
$method  = checkPostGet('method','');
$namakary= checkPostGet('namakary','');
$jenis   = checkPostGet('jenis','');


 
switch($method){
	case 'insert':
		if ($tahun == '' || $unit == '') {
			echo 'Gagal : Tahun dan unit wajib diisi.';exit("Warning");
		}
		if ($jenis == '') {
			echo 'Gagal : Jenis wajib diisi.';exit("Warning");
		}
		// if ($jenis == '45' and $namakary!='') {
			// echo 'Gagal : Nama karyawan pilih seluruhnya.';exit("Warning");
		// }
		// if ($jenis == '69' and $namakary=='') {
			// echo 'Gagal : Nama karyawan wajib dipilih.';exit("Warning");
		// }
		
		#= delete
		$str="delete from ".$dbname.".sdm_5gajipokok where tahun='".$tahun."' and kodeorg='".$unit."' and idkomponen='".$jenis."' and keterangan='".$namakary."'";
		try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal : ".addslashes($e->getMessage());}
	    
		#= insert
		$str="insert into ".$dbname.".sdm_5gajipokok (kodeorg,idkomponen,tahun,jumlah,karyawanid,keterangan)
			  values('".$unit."','".$jenis."','".$tahun."','".$rpnya."',0,'".$namakary."')";
		try{$owlPDO->exec($str); }catch (PDOException $e){echo " Gagal : ".addslashes($e->getMessage());}
	break;
	case 'loadData':
        $lstUnit=getOrgDetail(1);
        $dtMul=0;
        $listOrg="";
        foreach($lstUnit as $row=>$isiDt){
            if(substr($row,0,5)=='Pilih'){
                continue;
            }
            if($dtMul==0){
                $listOrg="'".$row."'";
                $dtMul=1;
            }else{
                $listOrg.=",'".$row."'";
            }
        }
		
		$where.=" and kodeorganisasi in(".$listOrg.")";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where 1=1 ".$where."";
		$res=fetchData($str);$no=0;
		foreach ($res as $key => $bar){
			$no+=1;
			if($dtMul==0){
                $listOrg="'".$bar['kodeorganisasi']."'";
                $dtMul=1;
            }else{
                $listOrg.=",'".$bar['kodeorganisasi']."'";
            }
		}

		$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
		$nmtipe=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
		$nmjenis=makeOption($dbname,'sdm_ho_component','id,name');
		
		$limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=intval($_POST['page']);
            if($page<0)
            $page=0;
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
		
		$wh="";
		if($tahun!=''){
			$wh.="and tahun='".$tahun."'";
		}
		if($unit!=''){
			$wh.="and kodeorg='".$unit."'";
		}
		if($namakary!=''){
			$wh.="and keterangan ='".$namakary."'";
		}
		if($jenis!=''){
			$wh.="and idkomponen ='".$jenis."'";
		}

		
        $str="select * from ".$dbname.".sdm_5gajipokok where 1=1 ".$wh." and idkomponen in ('45','69') and karyawanid ='0000000000' and kodeorg in (".$listOrg.") ";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=6>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".sdm_5gajipokok where 1=1 ".$wh." and idkomponen in ('45','69') and karyawanid ='0000000000' and kodeorg in (".$listOrg.") limit ".$offset.",".$limit."";
		
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                @$no+=1;
                $tab.="<tr class=rowcontent>
                    <td align=center>".$bar['tahun']."</td>
                    <td>".$bar['kodeorg']." - ".$nmorg[$bar['kodeorg']]."</td>
                    <td>".($bar['keterangan']==''?$_SESSION['lang']['all']:$nmtipe[$bar['keterangan']])."</td>
                    <td>".$nmjenis[$bar['idkomponen']]."</td>
                    <td align=right>".number_format($bar['jumlah'],2)."</td>
                    <td align=center width=50px>
                    	<img src='images/skyblue/edit.png' class='zImgBtn' title='Edit' onclick=\"fillfield('".$bar['tahun']."','".$bar['kodeorg']."','".$bar['jumlah']."','".$bar['keterangan']."','".$bar['idkomponen']."')\">
                    </td>
                	</tr>";
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
            $tab.="
                <tr><td colspan=6 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
		$footd="";
        echo $tab."####".$footd;
    break;

	default:
	   break;					
}


?>