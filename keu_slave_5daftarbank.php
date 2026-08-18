<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
$method=checkPostGet('method','');
$kodebank=checkPostGet('kodebank','');
$bank=checkPostGet('bank','');
$jumlah_hari=checkPostGet('jumlah_hari','');
$jumlah_hari2=checkPostGet('jumlah_hari2','');
$status=checkPostGet('status','');
$noinduk=checkPostGet('noinduk','');
$pemisah=checkPostGet('pemisah','');
$namafile=checkPostGet('namafile','');
$unitsch=checkPostGet('unitsch','');
$banksch=checkPostGet('banksch','');
$inisial=checkPostGet('inisial','');
$reksch=checkPostGet('reksch','');
$path='tempExcel';
$nmkary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
$nmstatus=array('0'=>'Non Active','1'=>'Active');
switch ($method) {
case'nonaktif':
	$str = "update " . $dbname . ".keu_5daftarbank set status='".$status."' where kodebank='".$kodebank."' and namabank='".$bank."'";
	try {$owlPDO->exec($str);} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
break;
case'getkodebank':
	$sql = "SELECT max(kodebank) as nomor FROM ".$dbname.".keu_5daftarbank order by nomor desc"; 
	$res = $owlPDO->query($sql)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	$data = $res->fetch();
	//exit('error'.$data['nomor']);
	if($data['nomor']==''){
		$nobank='10001';
	}else{
		$nobank=$data['nomor']+1;
	}
	
	echo $nobank;
break;
case 'insert':
	$ha = "insert into ".$dbname.".keu_5daftarbank (`kodebank`,`namabank`,`jumlah_hari`,`jumlah_hari2`,`inisial`,`updateby`,`createdby`,`createtime`)
		values ('".$kodebank."','".$bank."','".$jumlah_hari."','".$jumlah_hari2."','".$inisial."','".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."','".date('H:i:s')."')";
	try {
		$owlPDO->exec($ha);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;
case 'update':
	$ha = "update ".$dbname.".keu_5daftarbank set namabank='".$bank."',inisial='".$inisial."', jumlah_hari='".$jumlah_hari."', jumlah_hari2='".$jumlah_hari2."', updateby='".$_SESSION['standard']['userid']."' where kodebank='".$kodebank."'";// exit('error'.$ha);
	try {
		$owlPDO->exec($ha);
	} catch (PDOException $e) {
		print " Gagal  !: ".$e->getMessage()."\n";
		die();
	}
	break;
case 'loadData':
	echo "<div id=container>
	<table class=sortable cellspacing=1 border=0 style=min-width:650px>
	<thead>
	<tr class=rowheader>
	<td align=center>No</td>
	<td align=center>".$_SESSION['lang']['kode']." Bank</td>
	<td align=center>".$_SESSION['lang']['namabank']."</td>
	<td align=center>".$_SESSION['lang']['inisial']."</td>
	<td align=center>".$_SESSION['lang']['updateby']."</td>
	<td align=center>".$_SESSION['lang']['status']."</td>
	<td align=center width=50px>Action</td>
	</tr>
	</thead>
	<tbody>";
	$where='';
	if($banksch!=''){
		$where.=" and namabank like '%".$banksch."%'";
	}
	
	$iList = "select * from ".$dbname.".keu_5daftarbank where 1=1 ".$where." order by status asc, namabank asc";
	$res = $owlPDO->query($iList)or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while ($dList = $res->fetch()) {
		$no += 1;
		echo "<tr class=rowcontent>";
		echo "<td align=center>".$no."</td>";
		echo "<td align=left>".$dList['kodebank']."</td>";
		echo "<td align=left>".$dList['namabank']."</td>";
		echo "<td align=center>".$dList['inisial']."</td>";
		echo "<td align=left>".$nmkary[$dList['updateby']]."</td>";
		echo "<td align=left>".$nmstatus[$dList['status']]."</td>";
		echo "<td align=center>";
		if($dList['status']==1){
			echo"<img src=images/application/application_edit.png class=resicon  title='Edit'
			onclick=\"fillField('".$dList['kodebank']."','".$dList['namabank']."','".$dList['jumlah_hari']."','".$dList['jumlah_hari2']."','".$dList['inisial']."')\">";
			echo"&nbsp;<img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$dList['kodebank']."')\" src='images/upload-2-xxl.png'/>";
			echo"&nbsp;<img src=images/reject.png class=resicon  title='Deactivate'
			onclick=\"nonaktif('".$dList['kodebank']."','".$dList['namabank']."','0')\">";
		}else{
			echo"<img src=images/approve.png class=resicon  title='Activated'
			onclick=\"nonaktif('".$dList['kodebank']."','".$dList['namabank']."','1')\">";
		}
			
		echo"</td>";
		echo "</tr>";
	}
	break;
	case 'showupload':
        $tab="";
        $tab.="<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
        $tab.="<tr>
                <td>".$_SESSION['lang']['nomor']."</td>
                <td>:</td>
                <td>
                    <label id='noupload' style='display:none'>".$noinduk."</label>
                    <label style='font-weight:bold'>".$noinduk."</label>
                </td>
            </tr>
            <tr>
            <span>Format: kodeorg,periode,noakun,saldo<br>Eg. SOGE,201304,1110001,190000<br><b>This form must be preceded by a header on the first line</b> <a href=5daftarbank_detail_getExample.php? target=frame>Click here for example</a></span>; 
            </tr>";

        $tab.="<tr><td colspan=4><hr></td></tr>
                <tr>
                    <td>Filename</td>
                    <td>:</td>
                    <td>
                        <input type='file' name='upload' id='upload' >
                    </td>
                    
                </tr>
                <tr>
                    <td>Separated by</td>
                    <td>:</td>
                    <td><select id=pemisah name=pemisah>
                                        <option value=','>, (comma)</option>
                                        <option value=';'>; (semicolon)</option>
                                        <option value=':'>: (two dots)</option>
                                        <option value='/'>/ (slash)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td colspan=2></td>
                    <td>
                        <button class=mybutton onclick=\"submitfile()\">Submit</button>
                    </td>
                </tr>
            </table>
            <p />";
            
       
            
        echo $tab;
    break;
    case 'submitfile':
        $tgl = date("YmdHis");
        $his = date("His");
        $nmTemp=str_replace('-','',str_replace('/','',$noinduk));
        /*echo"<pre>";
        print_r($_FILES['file']);
        echo"</pre>";*/
        // exit('error');
        if($_POST['fileupload']!=''){
        	//exit('error');
            if($_FILES['file']['error']==0){    
                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                $filename = $nmTemp."_".$his."".$filetype;
                $file_tmpname = file_get_contents($_FILES['file']['tmp_name']); 
                
                if($filetype=='.csv'){
                    if($_FILES['file']['size'] <= 250000){
                        /*$str = "insert into ".$dbname.".listfile_pad_survey values ('','".$noinduk."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";*/
                        /*try{
                            $owlPDO->exec($str);*/
                            if (!file_exists($path)) {
                            mkdir($path, 0777, true);
                        	}
                            file_put_contents($path.$filename,$file_tmpname);
                           	//$x=readCSV($path,$pemisah);
                            $dir=$path;
						    $ext=explode('.', basename( $_FILES['file']['name']));
						    $ext=$ext[count($ext)-1];
						    $ext=strtolower($ext);
						    $path = $dir."/".date('ymd').".".$ext;
        					@unlink($path);
        					try
							{
								if(move_uploaded_file($_FILES['file']['tmp_name'], $path))
								{
									$x=readCSV($path,$pemisah);
									 $jmlhRow=count($x);
							        // $key=1;
							        for($row=1;$row<$jmlhRow;$row++){
							        $strkr="select * from ".$dbname.".keu_5daftarbankdt where noinduk='".$noinduk."' and kodebank='".$x[$row][2]."' and namabank='".$x[$row][3]."' and branchname='".$x[$row][4]."'";
						            $reskr=$owlPDO->query($strkr) or die(print " Gagal: ".PDOException::getMessage());
						            $reskr->setFetchMode(PDO::FETCH_ASSOC);
                                    $jlhbank=0;
						            while($barkr=$reskr->fetch()){
                                        $jlhbank++;
                                    }

						            if($jlhbank==0)
						            {
									$str="insert into ".$dbname.".keu_5daftarbankdt (`noinduk`,`kodebank`,`namabank`,`kodeclearing`,`kodertgs`,`branchname`,`city`) 
									VALUES ('".$noinduk."','".$x[$row][2]."','".trim($x[$row][3])."','".$x[$row][0]."','".$x[$row][1]."','".$x[$row][4]."','".$x[$row][5]."')";
									}
									else
									{
									$str="update ".$dbname.".keu_5daftarbankdt set kodeclearing='".$x[$row][0]."', kodertgs='".$x[$row][1]."' 
										where noinduk='".$noinduk."' and kodebank='".$x[$row][2]."' and namabank='".trim($x[$row][3])."' and branchname='".$x[$row][4]."'";
									}
                                    /*echo $str;
                                    exit();*/
									try{
							              $owlPDO->exec($str);      
							            }
							            catch (PDOException $e)
							            {
							              print " Gagal  !: " . $e->getMessage() . "<br/>";
							              die();
							            }
									}
								}
							}
							catch(Exception $e)
							{
								echo "<script>alert(\"Error Writing File".addslashes($e->getMessage())."\");</script>";
							}

                        /*}
                        catch(PDOException $e){
                            echo " Gagal," . addslashes($e->getMessage());
                        }*/
                    }
                    	else
                    	{
                        	exit("warning : Ukuran file upload maksimal 250kb");
                    	}
                }
                	else
                	{
                    		exit("Warning : Format file upload harus .csv");
                	}
            }
        }
    break;
    
    
    
default:
}
?>