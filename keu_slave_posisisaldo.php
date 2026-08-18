<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$param=$_POST;

switch($param['method']){
    case 'getbank':
        $optbank.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str = "select * from ".$dbname.".keu_5akunbank where pemilik='".$param['unit']."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch()){
            $wheredz =" kodebank='".$bar['namabank']."'";
            $optnama = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',$wheredz);
            if ($param['rekening']==$bar['noakun']) {
                $optbank.="<option value='".$bar['noakun']."' selected>".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
            }else{
                $optbank.="<option value='".$bar['noakun']."' >".$bar['rekening']." - ".$optnama[$bar['namabank']]."</option>";
            }
            }
        echo $optbank;
        
    break;

	case 'insert':
    // echo"<pre>";
    // echo print_r($param);
    // echo"</pre>";
    // exit('warning');
		if ($param['unit'] == '' || $param['tanggal'] == '' || $param['rekening'] == '') {
            echo "1. ".$_SESSION['lang']['unit']." ".$_SESSION['lang']['kosong'];
            echo "2. ".$_SESSION['lang']['rekening']." ".$_SESSION['lang']['kosong'];
            echo "3. ".$_SESSION['lang']['tanggal']." ".$_SESSION['lang']['kosong'];
			exit('warning : Field di atas mandatory.');
		}
        if($param['tanggal_lama']!=""){
            $str="update ".$dbname.".keu_posisisaldobank set tanggal='".tanggalsystemn($param['tanggal'])."',posisisaldo='".$param['saldoberjalan']."',estimasi='".$param['estimasi']."'
                 ,keterangan='".$param['keterangan']."',updateby='".$_SESSION['standard']['userid']."',waktu='".$param['jam']."'
                 where kodeorg='".$param['unit']."' and norekening='".$param['rekening']."' and tanggal='".tanggalsystemn($param['tanggal_lama'])."' and waktu='".$param['jam_lama']."'";
            //exit('warning'.$str);
            try{
                $owlPDO->exec($str); 
            }
            catch (PDOException $e){
                echo " Gagal : ".addslashes($e->getMessage());
            }
        }else{
            #= insert
            $cekd=explode(":",$param['jam']);
            if(count($cekd)==0){
                exit('warning: '.$_SESSION['lang']['jam'].' '.$_SESSION['lang']['tidaknormal']);
            }
            $str="insert into ".$dbname.".keu_posisisaldobank (kodeorg,norekening,tanggal,waktu,posisisaldo,estimasi,keterangan,createdby,createtime)
                  values('".$param['unit']."','".$param['rekening']."','".tanggalsystemn($param['tanggal'])."','".$param['jam']."','".$param['saldoberjalan']."','".$param['estimasi']."','".$param['keterangan']."',
                  '".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
            try{
                $owlPDO->exec($str); 
            }
            catch (PDOException $e){
                echo " Gagal : ".addslashes($e->getMessage().$str);
            }
        }
	break;

	case 'loadData':
		$limit=20;
        $page=0;
        if(isset($_POST['page'])){
            $page=$_POST['page'];
            if($page<0)
            $page=0;
        }
        $whrcr="";
        if($param['tanggalCari']!=''){
            $whrcr.=" and tanggal='".tanggalsystemn($param['tanggalCari'])."'";
        }
        if($param['rekeningCari']!=''){
            $whrcr.=" and norekening='".$param['rekeningCari']."'";
        }
        if($param['createdCari']!='0'){
            $whrcr.=" and createdby='".$param['createdCari']."'";
        }
        $offset=$page*$limit;
        $maxdisplay=($page*$limit);
        $str="select * from ".$dbname.".keu_posisisaldobank where 1=1 ".$whrcr."";
        //echo $str;
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=11>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".keu_posisisaldobank where 1=1 ".$whrcr." order by tanggal desc, kodeorg asc limit ".$offset.",".$limit."";
           // echo $str;
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $whr="kodeorganisasi='".$bar['kodeorg']."'";
                $optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whr);

                $sql="select namabank,rekening from ".$dbname.".keu_5akunbank where noakun='".$bar['norekening']."'";
                $scek2=$owlPDO->query($sql);
                $scek2->setFetchMode(PDO::FETCH_ASSOC);
                $rcek2=$scek2->fetch();
                $kodebank=$rcek2['namabank'];
                $wheredz =" kodebank='".$kodebank."'";
                $optnama = makeOption($dbname,'keu_5daftarbank','kodebank,namabank',$wheredz);
                $norek=$rcek2['rekening'];
                $no+=1;
                $nmKar=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$bar['createdby']."'");
                $nmKar2=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$bar['updateby']."'");
                //<td>".$bar['keterangan']."</td>
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$optorg[$bar['kodeorg']]."</td>
                    <td align='center'>".$optnama[$kodebank]."</td>
                    <td align='center'>".$norek."</td>
                    <td>".tanggalnormal($bar['tanggal'])." ".substr($bar['waktu'],0,5)."</td>
                    <td align=right>".number_format($bar['posisisaldo'])."</td>
                    <td align=right>".number_format($bar['estimasi'])."</td>
                    <td>".$bar['keterangan']."</td>
                    <td>".$nmKar[$bar['createdby']]."</td>
                    <!--<td>".$nmKar2[$bar['updateby']]."</td>-->";
                if ($bar['close']==0) {
                    $tab.="<td align=center>
                        <img src='images/skyblue/edit.png' class='resicon' title='Edit (".$optnama[$kodebank]."-".$norek.")-".tanggalnormal($bar['tanggal'])."' onclick=\"fillfield('".$bar['kodeorg']."','".$bar['norekening']."','".tanggalnormal($bar['tanggal'])."','".number_format($bar['posisisaldo'],2)."','".number_format($bar['estimasi'],2)."','".$bar['keterangan']."','".substr($bar['waktu'],0,5)."')\">
                    </td>
                    <td align=center>
                        <img src='images/skyblue/delete.png' class='resicon' title='Delete (".$optnama[$kodebank]."-".$norek.")-".tanggalnormal($bar['tanggal'])."' onclick=\"deldata('".$bar['kodeorg']."','".$bar['norekening']."','".$bar['tanggal']."','".$bar['waktu']."')\">
                    </td>
                    <td align=center>
                        <img src='images/skyblue/zoom.png' class='resicon' title='Detail (".$optnama[$kodebank]."-".$norek.")-".tanggalnormal($bar['tanggal'])."' onclick=\"previewdata('".$bar['kodeorg']."','".$bar['norekening']."','".$bar['tanggal']."','".$optnama[$kodebank]."-".$norek."','".tanggalnormal($bar['tanggal'])."','".$bar['waktu']."',event)\">
                    </td>";
                }else{
                    $tab.="<td align=center></td>";
                }
                
                $tab.="</tr>";
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
            $footd="
                <tr><td colspan=11 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;
    case'deldata':
            $str="delete from ".$dbname.".keu_posisisaldobank where kodeorg='".$param['unit']."' and norekening='".$param['rekening']."' and tanggal='".$param['tanggal']."' and waktu='".$param['jam']."'";
            try{
                $owlPDO->exec($str); 
            }
            catch (PDOException $e){
                echo " Gagal : ".addslashes($e->getMessage());
            }
    break;
	case'getDetail':
    $sData="select * from ".$dbname.".keu_posisisaldobank where  kodeorg='".$param['unit']."' and norekening='".$param['rekening']."' and tanggal='".$param['tanggal']."' and waktu='".$param['jam']."'";
    //echo $sData;
    $rData=fetchData($sData);
    $isiData=$rData[0];
    $optNmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$isiData['kodeorg']."'");
    $nmKar=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$isiData['createdby']."'");
    $nmKar2=makeOption($dbname,"datakaryawan","karyawanid,namakaryawan","karyawanid='".$isiData['updateby']."'");
    $tab.="<fieldset><legend>Detail</legend><table cellspacing=1 cellpadding=1 border=0>";
    $tab.="<tr>
            <td>".$_SESSION['lang']['unit']."</td><td> : </td>
            <td>".$isiData['kodeorg']."-".$optNmOrg[$isiData['kodeorg']]."</td></tr>
          <tr>
            <td>".$_SESSION['lang']['rekening']."</td><td> : </td>
            <td>".$param['rektmp']."</td>
          </tr>
           <tr>
                <td>".$_SESSION['lang']['tanggal']."</td>
                <td>:</td>
                <td>".$param['tgl']." ".$isiData['waktu']."</td>               
            </tr>
          <tr>  
            <td>".$_SESSION['lang']['saldoberjalan']."</td>
            <td> : </td>
            <td>".number_format($isiData['posisisaldo'],2)."</td>
          </tr>
           <tr> 
            <td>".$_SESSION['lang']['keterangan']."</td>
            <td> : </td>
            <td>".$isiData['keterangan']."</td>
          </tr>
          <tr> 
            <td>".$_SESSION['lang']['dibuat']."</td>
            <td> : </td>
            <td>".$nmKar[$isiData['createdby']]."</td>
          </tr>
          <tr> 
            <td>".$_SESSION['lang']['updateby']."</td>
            <td> : </td>
            <td>".$nmKar2[$isiData['updateby']]."</td>
          </tr>";
    $tab.="</table></fieldset>";
    echo $tab;
    break;
    default:
	break;					
}


?>