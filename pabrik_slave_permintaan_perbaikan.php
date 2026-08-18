<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method=checkPostGet('method','');

$optNm=makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
$optNik=makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
$optDiv=makeOption($dbname, 'sdm_5departemen','kode,nama');

$whBrg='';
$whKar='';
$whOrg='';
$nmBrg=makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',$whBrg);
$satBrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan',$whBrg);
$nmKar=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whKar);
$nikKar=makeOption($dbname,'datakaryawan','karyawanid,nik',$whKar);
$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whOrg);


$nmdownst=array('EDT'=>'EDT - Emergency Downtime','SDT'=>'SDT - Sequential Downtime','CDT'=>'CDT - Commercial Downtime');
$nodok=checkPostGet('nodok','');
$tglOrder=tanggalsystemn(checkPostGet('tglOrder',''));
$tglOrderDok=tanggalsystem(checkPostGet('tglOrder',''));
$jmOrder=checkPostGet('jmOrder','');
$mnOrder=checkPostGet('mnOrder','');
$waktuOrder=$jmOrder.":".$mnOrder.":00";
$namaPemohon=checkPostGet('namaPemohon','');
$statusPemohon=checkPostGet('statusPemohon','');
$pabrik=checkPostGet('pabrik','');
$station=checkPostGet('station','');
$mesin=checkPostGet('mesin','');
$shift=checkPostGet('shift','');
$tipePerbaikan=checkPostGet('tipePerbaikan','');
$uraianKerusakan=checkPostGet('uraianKerusakan','');
$tglMulai=tanggalsystemn(checkPostGet('tglMulai',''));
$jmMulai=checkPostGet('jmMulai','');
$mnMulai=checkPostGet('mnMulai','');



$dwnStat=checkPostGet('dwnStat','');
#kondisi opt
$arrKondisi=array('normal'=>'Normal','perbaikan'=>'Perlu Perbaikan','rusak'=>'Rusak');

$schdwnStat=checkPostGet('schdwnStat','');
$schstatusKetuntasan=checkPostGet('schstatusKetuntasan','');
$schstation=checkPostGet('schstation','');

$arrtipeperbaikan=array('prev'=>'Preventive Maintenance','mayor'=>'Mayor Maintenance','corrective'=>'Corrective Maintenance');

@$per['persetujuan1']=$_POST['persetujuan1'];
@$per['persetujuan2']=$_POST['persetujuan2'];
@$per['persetujuan3']=$_POST['persetujuan3'];
@$jenispersetujuan='PKSMAINTENANCE';

$notransaksi=checkPostGet('notransaksi','');
$namafile        = trim(checkPostGet('namafile', ''));
$path               = "fileupload/servicepabrik/";

switch($method){
	
	
	case 'showupload':
		$tab="";
		$tab.="<fieldset><legend>Upload</legend>
		<table border=0 >
			<tr>
				<td>Nomor</td>
				<td>:</td>
				<td id='notransaksiupload'>".trim($notransaksi)."
				</td>
			</tr>
			<tr>
				<td>Filename</td>
				<td>:</td>
				<td>
					<input type='file' name='upload' id='upload' >
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button id=btnsubmit class=mybutton onclick=\"submitfile()\">Submit</button>
				</td>
			</tr>
		</table>
		</fieldset>
			<p />";
		
		$tab.="<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>
			<table class='sortable' cellspacing='1' border='0' width=100%>
				<thead>
				<tr class=rowheader>
					<td align='center' width=50px>No.</td>
					<td align='center' width=50px>File Type</td>
					<td align='center'>Filename</td>
					<td align='center' width=50px>Action</td>
				</tr>
				</thead>
				<tbody id='listfiles'>
				</tbody>
			</table>
		</fieldset> ";
		
		echo $tab;
		
		

	break;
	case 'submitfile':
	
	#cek data
	$tgl = date("YmdHis");
	$his = date("His");
	$data = $_POST;
	if($data['fileupload']!=''){
		if($_FILES['file']['error']==0){
			$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
			$filename = $_FILES['file']['name'];
			$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);
			if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
				/*if($_FILES['file']['size'] <= 250000){*/
					$str = "insert into ".$dbname.".listfileupload values ('','".trim($notransaksi)."','".$filename."','".$filetype."','','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
					try{
						$owlPDO->exec($str);
						if (!file_exists($path)) {
							mkdir($path, 0777, true);
						}
						file_put_contents($path.$filename,$file_tmpname);
					}
					catch(PDOException $e){
						echo " Gagal," . addslashes($e->getMessage());
					}
				/*}else{
					exit("warning : Ukuran file upload maksimal 250kb");
				}*/
			}else{
				exit("Warning : Format file upload harus .jpg atau .jpeg");
			}
		}
	}
	break;
	
	case 'loadfiles':
	
	$no = 0;
	$tab = "";
	$str="select * from ".$dbname.".listfileupload where notransaksi = '".trim($notransaksi)."' and status='1'";
	// exit("Error:".$str);
	$res=fetchData($str);
	if(empty($res)){
		$tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
	}else{
		foreach($res as $key=>$val){
			$no++;
			$tab.="<tr class=rowcontent>
					<td style='text-align:center'>".$no."</td>";
			$icon=seticonfile($val['formaticon']);
			$tab.="<td style='text-align:center'>
					<a href='".$path.$val['namafile']."' download><img src=".$icon." class=resicon></a>
				</td>";
			$nfile='';
			$nfile = $val['namafile'];
			$tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('event','".$val['namafile']."')\">".$nfile."</td>
				<td align=center>
					<a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon	 title='download'></a>&nbsp";
			$tab.="<img src=images/application/application_delete.png class=resicon	 title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['namafile']."');\" >";
			$tab."	</td>
				</tr>";
		}
	}
	echo $tab;
	break;
	
	case'viewfile':
		$tab="";
		$tab.="<img src='".$path.$namafile."' style='width:600px;height:400px;'>";
		echo $tab;
	break;
	
	case 'deletefile':
	$str="delete from ".$dbname.".listfileupload where notransaksi='".$notransaksi."' and namafile='".$namafile."'";
	try{
		$owlPDO->exec($str);
		$pathx = $path.$namafile;
		unlink($pathx);
	}
	catch(PDOException $e){
		echo " Gagal," . addslashes($e->getMessage());
	}
	break;
	

     ########### case insert header
    case 'insert': 
      
			#= cek apakah masih ada job order sebelumnya yg belum tuntas untuk mesin ini
			#= parameternya mesin,
			$str="select count(*) as jumlah from ".$dbname.".pabrik_rawatmesinht where mesin = '".$mesin."' and statusketuntasan!='Selesai'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
				$flag=$bar['jumlah'];
				
			if($flag>0){
				exit("Warning:Masih Job Order yang belum selesai");
			}			
			
			if($per['persetujuan1']=='' or $per['persetujuan2']=='' or $per['persetujuan3']==''){
				exit("Warning:Persetujuan belum ada");
			}
		

            $str="insert into ".$dbname.".pabrik_rawatmesinht (`notransaksi`, `pabrik`, `tanggal`, `jam`,
                    `shift`, `statasiun`, `mesin`, `kegiatan`,`updateby`,
					`downstatus`,`namapemohon`,`tipeperbaikan`,`statuspemohon`,`flag`) 
            values ('".$nodok."','".$pabrik."','".$tglOrder."','".$waktuOrder."',
                    '".$shift."','".$station."','".$mesin."','".$uraianKerusakan."','".$_SESSION['standard']['userid']."',
					'".$dwnStat."','".$namaPemohon."','".$tipePerbaikan."','".$statusPemohon."','0')";
					// exit("Error:$str");
                    
            try
            {
                $owlPDO->exec($str);

               for($i=1; $i<4; $i++){
					if($per['persetujuan'.$i]!=''){
						$str="insert into ".$dbname.".approval (`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`) values 
						  ('".$nodok."','".$jenispersetujuan."','".$i."','".$per['persetujuan'.$i]."')";

						try{
			            	$owlPDO->exec($str); 
				        }catch(PDOException $e){
				            echo " Gagal," . addslashes($e->getMessage());
				        }
					}
				}


            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                die(); 
            }
	break;
        
    case'update':
        //exit("Error:MASUK");
        $str="update ".$dbname.".pabrik_rawatmesinht set namapemohon='".$namaPemohon."',statuspemohon='".$statusPemohon."',
                  shift='".$shift."',tipeperbaikan='".$tipePerbaikan."',kegiatan='".$uraianKerusakan."',jammulai='".$waktuMulai."',
                  jamselesai='".$waktuSelesai."',jumlahjamperbaikan='".$jumlahJamPerbaikan."',statusketuntasan='".$statusKetuntasan."',
                  hasilkerja='".$hasilKerja."',komentarmainten='".$komMain."',komentarproses='".$komPros."',downstatus='".$dwnStat."'
                  where notransaksi='".$nodok."'";
        //exit("Error:$iUpdate");
        try
        {
            $owlPDO->exec($str);
        }
        catch (PDOException $e) 
        {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;
    
    case'getNodok':
        
        $iList="select notransaksi,tanggal,statasiun  from ".$dbname.".pabrik_rawatmesinht where statasiun ='".$station."' "
            . "and tanggal='".$tglOrder."' order by notransaksi desc limit 1";
        $nList=$owlPDO->query($iList) or die(print " Gagal: ".PDOException::getMessage());
        $nList->setFetchMode(PDO::FETCH_ASSOC);
        $dList=$nList->fetch();
        
            
        if($dList['notransaksi']!='')
        {
            $listDok=  explode('/', $dList['notransaksi']);
            $noUrut=$listDok[2]+1;
        }
        else
        {
            $noUrut=1;
        }
        $counter=addZero($noUrut,4);
        $noDok=$station.'/'.$tglOrderDok.'/'.$counter;
        echo $noDok;
        
    break;
    
    case'getMesin':
        $optMesin.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $optMesin.="<option value='Others'>Others</option>";
        $iMesin="select * from ".$dbname.".organisasi where induk='".$station."' ";
        $nMesin=$owlPDO->query($iMesin) or die(print " Gagal: ".PDOException::getMessage());
        $nMesin->setFetchMode(PDO::FETCH_ASSOC);
        while($dMesin=$nMesin->fetch())
        {
            if($mesin==$dMesin['kodeorganisasi'])
            {$select="selected=selected";}
            else
            {$select="";}
           
            $optMesin.="<option ".$select." value=".$dMesin['kodeorganisasi'].">".$dMesin['namaorganisasi']."</option>";
        }
        echo $optMesin;
    break;
	
	
	case'ajukan':
		$str="update ".$dbname.".pabrik_rawatmesinht set statuspersetujuan='9' where notransaksi='".$nodok."'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
	break;
    
    
    case'loadData':
       //exit("Error:ASDASDAS");
            echo"
            <table cellspacing=1 cellpadding=2 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <td align=center rowspan=2>No.</td>
            <td align=center rowspan=2>No. Transaksi</td>
            <td align=center rowspan=2>".$_SESSION['lang']['tanggal']."</td>
            <td align=center colspan=2>".$_SESSION['lang']['pabrik']."</td>
            <td align=center colspan=2>".$_SESSION['lang']['station']."</td>
            <td align=center colspan=2>".$_SESSION['lang']['mesin']."</td>
			 <td align=center rowspan=2>".$_SESSION['lang']['downstatus']."</td>
			 <td align=center rowspan=2>".$_SESSION['lang']['status']."</td>
            <td align=center rowspan=2>".$_SESSION['lang']['action']."</td>
            </tr>
            <tr>
                <td align=center>".$_SESSION['lang']['kode']."</td>
                <td align=center>".$_SESSION['lang']['nama']."</td>
                <td align=center>".$_SESSION['lang']['kode']."</td>
                <td align=center>".$_SESSION['lang']['nama']."</td>
                <td align=center>".$_SESSION['lang']['kode']."</td>
                <td align=center>".$_SESSION['lang']['nama']."</td>
            </tr>
            </thead>
            <tbody>
            ";//<td align=center>".$_SESSION['lang']['kdpabrik']."</td>

           //exit("Error:$schTgl");
            $wheresch='';
            if(@$schNodok!='') {
                $wheresch.=" and notransaksi like '%".$schNodok."%' ";
            }
            
            if(@$schTgl!=''){
                $wheresch.=" and tanggal like '%".$schTgl."%' ";
            }
			
			if($schdwnStat!=''){
                $wheresch.=" and downstatus='".$schdwnStat."' ";
            }
			
			if($schstation!=''){
                $wheresch.=" and statasiun='".$schstation."' ";
            }

			

            $limit=20;
            $page=0;
            if(isset($_POST['page']))
            {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
            }
            $offset=$page*$limit;
            $maxdisplay=($page*$limit);
            $ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_rawatmesinht where flag!='2' and 
				pabrik='".$_SESSION['empl']['lokasitugas']."' ".$wheresch."  order by `notransaksi` desc";// echo $ql2;notran
           
            $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
            $query2->setFetchMode(PDO::FETCH_OBJ);
            while($jsl=$query2->fetch())
            {  
                $jlhbrs= $jsl->jmlhrow;
            }
           
            $no=$maxdisplay;	
            $iList="select * from ".$dbname.".pabrik_rawatmesinht where flag!='2' and   pabrik='".$_SESSION['empl']['lokasitugas']."' ".$wheresch." order by `tanggal` desc, `notransaksi` desc limit ".$offset.",".$limit."";
            $nList=$owlPDO->query($iList) or die(print " Gagal: ".PDOException::getMessage());
            $nList->setFetchMode(PDO::FETCH_ASSOC);
            while($dList=$nList->fetch())
            {
                $whOrg="kodeorganisasi='".$dList['mesin']."'";
                setIt($nmOrg[$dList['mesin']],'');
               
				$postDt="style='cursor:pointer' onclick=ajukan('".$dList['notransaksi']."')";
                
				$str="select * from ".$dbname.".approval where notransaksi='". $dList['notransaksi']."' order by  level asc";
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					$kar[$bar['level']]=$bar['karyawanid'];
				}
				
                
                $no+=1;
                echo"
                <tr class=rowcontent>
                <td align=center>".$no."</td>
                <td>".$dList['notransaksi']."</td>
                <td style='min-width:80px;text-align:center'>".tanggalnormal($dList['tanggal'])."</td>    
                <td>".$dList['pabrik']."</td>
                <td>".$nmOrg[$dList['pabrik']]."</td>
                <td>".@$dList['statasiun']."</td>
                <td>".@$nmOrg[$dList['statasiun']]."</td>
                <td>".$dList['mesin']."</td>
                <td>".$nmOrg[$dList['mesin']]."</td>  
                <td>".$nmdownst[$dList['downstatus']]."</td>";  

				if($dList['statuspersetujuan']=='0' || $dList['statuspersetujuan']==''){
					echo"<td style='text-align:center'>Belum Diajukan</td>
					
					<td align=center>
						<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$dList['notransaksi']."','".tanggalnormal($dList['tanggal'])."','".substr($dList['jam'],0,2)."','".substr($dList['jam'],3,2)."','".$dList['namapemohon']."','".$dList['statuspemohon']."','".$dList['pabrik']."','".$dList['statasiun']."','".$dList['mesin']."','".$dList['shift']."','".$dList['tipeperbaikan']."','".str_replace("\n",'<br />',$dList['kegiatan'])."','".tanggalnormal(substr($dList['jammulai'],0,10))."','".substr($dList['jammulai'],11,2)."','".substr($dList['jammulai'],14,2)."','".tanggalnormal(substr($dList['jamselesai'],0,10))."','".substr($dList['jamselesai'],11,2)."','".substr($dList['jamselesai'],14,2)."','".$dList['jumlahjamperbaikan']."','".$dList['statusketuntasan']."','".str_replace("\n",'<br />',$dList['hasilkerja'])."','".$nmOrg[$dList['mesin']]."','".str_replace("\n",'<br />',$dList['komentarmainten'])."','".str_replace("\n",'<br />',$dList['komentarproses'])."','".$dList['downstatus']."','".$kar['1']."','".$kar['2']."','".$kar['3']."');\">
						<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deleteHead('".$dList['notransaksi']."');\" >
						<img src=images/skyblue/posting.png class=resicon  title='Ajukan' ".$postDt." >
					
					";					
				}else if($dList['statuspersetujuan']=='9'){
					echo"<td style='text-align:center'>Proses Persetujuan</td>
					
					<td align=center>
					<img src=images/icons/04/16/04.png class=resicon  title='Proses Persetujuan' >&nbsp;";					
				}else{
					echo"<td style='text-align:center'>Sudah Disetujui</td>
					
					<td align=center>
					<img src=images/skyblue/posted.png class=resicon  title='Sudah Disetujui' >&nbsp;";					
				}

                echo"<img src=images/upload-2-xxl.png class=zImgBtn onclick=showupload('".$dList['notransaksi']."') title=Upload>
				</td>
				</td>";
            }
			
			echo createpaging($jlhbrs,$limit,$page,12,'loaddata','getpage');
           
            echo"</tbody></table>";
            break;
		
		
	##########case delete
	case 'deleteHead':
		$iDel="delete from ".$dbname.".pabrik_rawatmesinht where notransaksi='".$nodok."' ";
                try
                {
                    $owlPDO->exec($iDel);
                }
                catch (PDOException $e) 
                {
                    print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                    die(); 
                }
	break;
	
	
	
	
}
?>	