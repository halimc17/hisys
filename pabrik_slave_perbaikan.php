<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method=checkPostGet('method','');

// $optNm =makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
// $optNik=makeOption($dbname, 'datakaryawan', 'karyawanid,nik');
// $optDiv=makeOption($dbname, 'sdm_5departemen','kode,nama');

$whBrg ='';
$whKar ='';
$whOrg ='';
//$nmBrg =makeOption($dbname,'log_5masterbarang','kodebarang,namabarang');
//$satBrg=makeOption($dbname,'log_5masterbarang','kodebarang,satuan');
//$nmKar =makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whKar);
// $nikKar=makeOption($dbname,'datakaryawan','karyawanid,nik',$whKar);
$nmOrg =makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',$whOrg);
$nmOrg2=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
/*$jam1=$_POST['jm1'].":".$_POST['mn1'].":00";
//exit("Error:$jam1");
$jam2=$_POST['jm2'].":".$_POST['mn2'].":00";*/

$nmdownst=array('EDT'=>'EDT - Emergency Downtime','SDT'=>'SDT - Sequential Downtime','CDT'=>'CDT - Commercial Downtime');
$nodok              =checkPostGet('nodok','');
$tglOrder           =tanggalsystemn(checkPostGet('tglOrder',''));
$tglOrderDok        =tanggalsystem(checkPostGet('tglOrder',''));
$jmOrder            =checkPostGet('jmOrder','');
$mnOrder            =checkPostGet('mnOrder','');
$waktuOrder         =$jmOrder.":".$mnOrder.":00";
$namaPemohon        =checkPostGet('namaPemohon','');
$statusPemohon      =checkPostGet('statusPemohon','');
$pabrik             =checkPostGet('pabrik','');
$station            =checkPostGet('station','');
$mesin              =checkPostGet('mesin','');
$shift              =checkPostGet('shift','');
$tipePerbaikan      =checkPostGet('tipePerbaikan','');
$uraianKerusakan    =checkPostGet('uraianKerusakan','');
$tglMulai           =tanggalsystemn(checkPostGet('tglMulai',''));
$jmMulai            =checkPostGet('jmMulai','');
$mnMulai            =checkPostGet('mnMulai','');
$waktuMulai         =$tglMulai." ".$jmMulai.":".$mnMulai.":00";
$tglSelesai         =tanggalsystemn(checkPostGet('tglSelesai',''));
$jmSelesai          =checkPostGet('jmSelesai','');
$mnSelesai          =checkPostGet('mnSelesai','');
$waktuSelesai       =$tglSelesai." ".$jmSelesai.":".$mnSelesai.":00";
$jumlahJamPerbaikan =checkPostGet('jumlahJamPerbaikan','');
$statusKetuntasan   =checkPostGet('statusKetuntasan','');
$hasilKerja         =checkPostGet('hasilKerja','');
$namaBarangCari     =checkPostGet('namaBarangCari','');
$jenisperbaikan     =checkPostGet('jenisperbaikan','');
#barang
$kodeBarang         =checkPostGet('kodeBarang','');
$jumlahBarang       =checkPostGet('jumlahBarang','');
$keteranganBarang   =checkPostGet('keteranganBarang','');
$satuanBarang       =checkPostGet('satuanBarang','');
$hargabarang        =checkPostGet('hargabarang','');
$nogudang           =checkPostGet('nogudang','');


#karyawan
$karyawan           =checkPostGet('karyawan','');


#pekerjaan
$nomor              =checkPostGet('nomor','');
$rincian            =checkPostGet('rincian','');
$kondisi            =checkPostGet('kondisi','');
$schNodok           =checkPostGet('schNodok','');
$schTgl             =tanggalsystemn(checkPostGet('schTgl',''));


#komentar ketinggalan
$komMain            =checkPostGet('komMain','');
$komPros            =checkPostGet('komPros','');

if($schTgl=='--'){
	$schTgl='';
}
$kdmesin   =checkPostGet('kdmesin','');
$sbMesin   =checkPostGet('sbMesin','');
$dwnStat   =checkPostGet('dwnStat','');
#kondisi opt
$arrKondisi=array('normal'=>'Normal','perbaikan'=>'Perlu Perbaikan','rusak'=>'Rusak');

$schdwnStat         =checkPostGet('schdwnStat','');
$schstatusKetuntasan=checkPostGet('schstatusKetuntasan','');
$schstation         =checkPostGet('schstation','');


$arrtipeperbaikan=array('prev'=>'Preventive Maintenance','mayor'=>'Mayor Maintenance','corrective'=>'Corrective Maintenance');
$notransaksi=checkPostGet('notransaksi','');
$namafile        = trim(checkPostGet('namafile', ''));
$path               = "fileupload/servicepabrik/";
switch($method){

	case'getno':    
        $tab = "<table cellpadding=1 cellspacing=1 border=0 class=sortable>";
        $tab.="<thead>";
        $tab.="<tr><td>No Transaksi</td>";
       
        $tab.="<td>Station</td>";
        $tab.="<td>Mesin</td>";
        $tab.="
        <td style='text-align:center'>Tanggal Permintaan</td>
        

        </tr></thead><tbody>";


        $sdata = "select * from ".$dbname.".pabrik_rawatmesinht where pabrik='".$_SESSION['empl']['lokasitugas']."' and statuspersetujuan='1' and flag=0 and notransaksi like '%".$_POST['txtfind']."%' ";
        $qdata=$owlPDO->query($sdata) or die(print " Gagal: ".PDOException::getMessage());
        $qdata->setFetchMode(PDO::FETCH_ASSOC);
        while($rdata=$qdata->fetch())
        {
            $brt = "style=cursor:pointer; onclick=\"setData('".$rdata['notransaksi']."','".tanggalnormal($rdata['tanggal'])."',
                     '".substr($rdata['jam'],0,2)."','".substr($rdata['jam'],3,2)."','".$rdata['namapemohon']."',
                     '".$rdata['statuspemohon']."','".$rdata['pabrik']."','".$rdata['statasiun']."','".$rdata['mesin']."',
                     '".$rdata['shift']."','".$rdata['tipeperbaikan']."','".str_replace("\n",'<br />',$rdata['kegiatan'])."',
                     '".tanggalnormal(substr($rdata['jammulai'],0,10))."','".substr($rdata['jammulai'],11,2)."',
                     '".substr($rdata['jammulai'],14,2)."',
                     '".tanggalnormal(substr($rdata['jamselesai'],0,10))."','".substr($rdata['jamselesai'],11,2)."',
                     '".substr($rdata['jamselesai'],14,2)."','".$rdata['jumlahjamperbaikan']."',
                     '".$rdata['statusketuntasan']."','".str_replace("\n",'<br />',$rdata['hasilkerja'])."','".getNamaOrg($rdata['mesin'])."',
                     '".str_replace("\n",'<br />',$rdata['komentarmainten'])."','".str_replace("\n",'<br />',$rdata['komentarproses'])."','".$rdata['downstatus']."','".$rdata['jenisperbaikan']."')\" ";
            $tab.="<tr " . $brt . " class=rowcontent>
            <td>" . $rdata['notransaksi'] . "</td>
            <td>" . $nmOrg2[$rdata['statasiun']] . "</td>
            <td>" . $nmOrg2[$rdata['mesin']] . "</td>
            <td>" . tanggalnormal($rdata['tanggal']) . "</td>
     
            ";
          
           
        }
        $tab.="</tbody></table>";
        echo $tab;
        break;


	case'getFormPerbaikan':
        $form = "<fieldset style=float: left;>
               <legend>" . $_SESSION['lang']['find'] . " Permintaan Perbaikan</legend>
               No Permintaan Perbaikan &nbsp;<input type=text class=myinputtext id=nocr />&nbsp;&nbsp;&nbsp;<button class=mybutton onclick=findNo('" . $param['noso'] . "')>" . $_SESSION['lang']['find'] . "</button></fieldset>
               <fieldset><legend>" . $_SESSION['lang']['result'] . "</legend><div id=container2 style=overflow:auto;width:100%;height:430px;></fieldset></div>";
        echo $form;
        break;
	
	
	
	
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
					<input type='file' name='uploaddata' id='uploaddata' >
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
				<tbody id='listdatafiles'>
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

	
	
	
	case'getlistdatalalu':
		echo"<fieldset  style='float:left;' >
					<legend></legend>
						<table>
						<thead>
						<tr class=rowheader>
								<td>No</td>
								<td>".$_SESSION['lang']['nodok']."</td>
								<td>".$_SESSION['lang']['tanggal']."</td>
								<td>".$_SESSION['lang']['downstatus']."</td> 
								<td>".$_SESSION['lang']['tipeperbaikan']."</td> 
								<td>".$_SESSION['lang']['statusketuntasan']."</td> 
						</tr></thead>";
							$str="select * from ".$dbname.".pabrik_rawatmesinht where tanggal < '".$tglOrder."' and mesin='".$mesin."'
								order by tanggal desc";
								
							$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
							$res->setFetchMode(PDO::FETCH_ASSOC);
							while($bar=$res->fetch()){
								@$no+=1;
								echo"
								<tr class=rowcontent>
										<td>".$no."</td>
										<td>".$bar['notransaksi']."</td>
										<td>".tanggalnormal($bar['tanggal'])."</td>
										<td>".$nmdownst[$bar['downstatus']]."</td>
										<td>".@$arrtipeperbaikan[$bar['tipeperbaikan']]."</td>
										<td>".$bar['statusketuntasan']."</td>
								</tr>";
							}
						
						echo"</table>
			</fieldset>";
	break;
	
	
	
	
    case'getListBarang':
        echo"<fieldset  style='float:left;' >
                <legend>".$_SESSION['lang']['nodok']." ".$_SESSION['lang']['gudang']."</legend>
                    <table cellspacing=1 border=0 class=data>
                        <tr>
                            <td colspan=2>".$_SESSION['lang']['nodok']." ".$_SESSION['lang']['gudang']."</td>

                            <td colspan=5>: 
                                    <input type=text id=namaBarangCari  class=myinputtext maxlength=100 onkeypress=\"return tanpa_kutip(event);\" style='width:100px;'>
                                    <button class=mybutton onclick=cariListBarang()>cari</button>
                            <td>
                        <tr>
                    </table>

                    <table id=listCariBarang >
                    <thead>
                    <tr class=rowheader>
                            <td>No</td>
                            <td>".$_SESSION['lang']['nodok']."</td>
                            <td>".$_SESSION['lang']['kodebarang']."</td>
                            <td>".$_SESSION['lang']['namabarang']."</td>
                            <td>".$_SESSION['lang']['jumlah']."</td>
                            <td>".$_SESSION['lang']['satuan']."</td>
                            <td hidden>".$_SESSION['lang']['harga']."</td>    
                    </tr></thead>";

                    if($namaBarangCari==''){
                    }
                    else
                    {
						
						$str="select a.*, b.namabarang from ".$dbname.".log_transaksi_vw AS a LEFT JOIN ".$dbname.".log_5masterbarang AS b ON b.kodebarang = a.kodebarang where a.kodeblok='".$mesin."' AND b.namabarang LIKE '%".$namaBarangCari."%' and post='1'";
						// echo $str;
                        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                        $res->setFetchMode(PDO::FETCH_ASSOC);
                        while($bar=$res->fetch())
                        {
						
							// $str1="select * from ".$dbname.".log_5masterbarang where kodebarang='".$bar['kodebarang']."'";
							// $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
							// $res1->setFetchMode(PDO::FETCH_ASSOC);
							// $bar1=$res1->fetch();
							// // notransaksi	kodebarang
							// #= cek apakah sudah pernah ditarik ditransaksi lain
							// $str2="select count(*) as jumlah from ".$dbname.".pabrik_rawatmesindt where noreferensi='".$bar['notransaksi']."' and kodebarang='".$bar['kodebarang']."'";
							// $res2=$owlPDO->query($str2) or die(print " Gagal: ".PDOException::getMessage());
							// $res2->setFetchMode(PDO::FETCH_ASSOC);
							// $bar2=$res2->fetch();
								
							// if($bar2['jumlah']=='0'){	
								$no+=1;
								echo"
								<tr class=rowcontent  style='cursor:pointer;' title='Click It' onclick=\"moveDataBarang('".$bar['notransaksi']."','".$bar['kodebarang']."','".$bar1['namabarang']."','".$bar['satuan']."','".$bar['hargarata']."','".$bar['jumlah']."');\">
										<td>".$no."</td>
										<td>".$bar['notransaksi']."</td>
										<td>".$bar['kodebarang']."</td>
										<td>".getNamaBrg($bar['kodebarang'])."</td>
										<td>".$bar['jumlah']."</td>
										<td>".$bar['satuan']."</td>
										<td hidden>".$bar['hargarata']."</td>    
								</tr>";
							// }
                        }
                    }
                    echo"</table>
        </fieldset>";
	
    break;  
    
    
     ########### case insert header
    case 'insert':  //$komMain=$_POST['komMain']; //$komPros=$_POST['komPros'];
      
			#= cek apakah masih ada job order sebelumnya yg belum tuntas untuk mesin ini
			#= parameternya mesin,
			// $str="select count(*) as jumlah from ".$dbname.".pabrik_rawatmesinht where mesin = '".$mesin."' and statusketuntasan!='Selesai'";

            #= Tambahkan where pabrik mana
            #= Sudah diskusi dengan Pak Surya lewat chat
			$str="select count(*) as jumlah from ".$dbname.".pabrik_rawatmesinht where mesin = '".$mesin."' and pabrik='".$pabrik."' and statusketuntasan!='Selesai'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
				$flag=$bar['jumlah'];
				
			if($flag>0){
				exit("Warning:Masih Job Order yang belum selesai");
			}			
			
			#= cek apakah ada mesin yang sama diperbaiki ditanggal yang sama
			
			/*
			$str="select count(*) as jumlah,notransaksi from ".$dbname.".pabrik_rawatmesinht where mesin = '".$mesin."' and tanggal='".$tglOrder."'";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
				$flag=$bar['jumlah'];
				$notranada=$bar['notransaksi'];
				
			if($flag>0){
				exit("Warning:Sudah ada job report di mesin ini ditanggal ".tanggalnormal($tglOrder).", yaitu transaksi : ".$notranada." ");
			}		
			*/	

            $str="insert into ".$dbname.".pabrik_rawatmesinht (`notransaksi`, `pabrik`, `tanggal`, `jam`,
                    `shift`, `statasiun`, `mesin`, `kegiatan`, `jammulai`, `jamselesai`, `updateby`,
                    `namapemohon`, `statuspemohon`, `tipeperbaikan`,
                    `jumlahjamperbaikan`, `statusketuntasan`, `hasilkerja`,`komentarmainten`,`komentarproses`,`downstatus`,`flag`,`jenisperbaikan`) 
            values ('".$nodok."','".$pabrik."','".$tglOrder."','".$waktuOrder."',
                    '".$shift."','".$station."','".$mesin."','".$uraianKerusakan."','".$waktuMulai."','".$waktuSelesai."',
                    '".$_SESSION['standard']['userid']."','".$namaPemohon."','".$statusPemohon."','".$tipePerbaikan."',
                    '".$jumlahJamPerbaikan."','".$statusKetuntasan."','".$hasilKerja."','".$komMain."','".$komPros."','".$dwnStat."','2','".$jenisperbaikan."')";
                    
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
        
    case'update':
        //exit("Error:MASUK");
		
		#= cek apakah statusnya 0
		$flagawal=2;
		$str="select flag from ".$dbname.".pabrik_rawatmesinht where notransaksi = '".$nodok."'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
				$flagawal=$bar['jumlah'];
		
		if($flagawal==0 || $flagawal==1){
			$flag=1;
		}
		
        $str="update ".$dbname.".pabrik_rawatmesinht set namapemohon='".$namaPemohon."',statuspemohon='".$statusPemohon."',
                  shift='".$shift."',tipeperbaikan='".$tipePerbaikan."',kegiatan='".$uraianKerusakan."',jammulai='".$waktuMulai."',
                  jamselesai='".$waktuSelesai."',jumlahjamperbaikan='".$jumlahJamPerbaikan."',statusketuntasan='".$statusKetuntasan."',
                  hasilkerja='".$hasilKerja."',komentarmainten='".$komMain."',komentarproses='".$komPros."',downstatus='".$dwnStat."',flag='".$flag."',jenisperbaikan='".$jenisperbaikan."',mesin='".$mesin."'
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
    
    
    case'loadData':
        $tab="<table cellspacing=1 cellpadding=5 border=0 class=sortable width=100%>
            <thead>
            <tr class=rowheader>
				<th align=center rowspan=2>No.</th>
				<th align=center rowspan=2>No. Transaksi</th>
				<th align=center rowspan=2>".$_SESSION['lang']['tanggal']."</th>
				<th align=center colspan=2>".$_SESSION['lang']['pabrik']."</th>
				<th align=center colspan=2>".$_SESSION['lang']['station']."</th>
				<th align=center colspan=2>".$_SESSION['lang']['mesin']."</th>
				<th align=center colspan=2>".$_SESSION['lang']['status']."</th>
				<th align=center rowspan=2>".$_SESSION['lang']['dibuatoleh']."</th>
				<th align=center rowspan=2 colspan=5>".$_SESSION['lang']['action']."</th>
            </tr>
            <tr>
                <th align=center>".$_SESSION['lang']['kode']."</th>
                <th align=center>".$_SESSION['lang']['nama']."</th>
                <th align=center>".$_SESSION['lang']['kode']."</th>
                <th align=center>".$_SESSION['lang']['nama']."</th>
                <th align=center>".$_SESSION['lang']['kode']."</th>
                <th align=center>".$_SESSION['lang']['nama']."</th>
                <th align=center>".$_SESSION['lang']['downstatus']."</th>
                <th align=center>".$_SESSION['lang']['statusketuntasan']."</th>
            </tr>
            </thead>
            <tbody>";

            $wheresch='';
            if($schNodok!='') {
                $wheresch.=" and notransaksi like '%".$schNodok."%' ";
            }
            
            if($schTgl!=''){
                $wheresch.=" and tanggal like '%".$schTgl."%' ";
            }
			
			if($schdwnStat!=''){
                $wheresch.=" and downstatus='".$schdwnStat."' ";
            }
			if($schstatusKetuntasan!=''){
                $wheresch.=" and statusketuntasan='".$schstatusKetuntasan."' ";
            }
			if($schstation!=''){
                $wheresch.=" and statasiun='".$schstation."' ";
            }

			

            $limit=20;
            $page=0;
            if(isset($_POST['page'])){
				$page=$_POST['page'];
				if($page<0)
				$page=0;
            }
            $offset=$page*$limit;
            $maxdisplay=($page*$limit);
            $str="select count(*) as jmlhrow from ".$dbname.".pabrik_rawatmesinht where flag!=0 and pabrik in (".getOrgDetail(2).") ".$wheresch."  order by `notransaksi` desc";
            $res=fetchdata($str);
            $jlhbrs= $res[0]['jmlhrow'];
           
            $no=$maxdisplay;	
            
			if($jlhbrs <= 0){
				$tab.="<tr class=rowcontent><td colspan='16' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
			}else{
                $iList="select * from ".$dbname.".pabrik_rawatmesinht where  flag!=0 and pabrik in (".getOrgDetail(2).") ".$wheresch." order by `tanggal` desc, `notransaksi` desc limit ".$offset.",".$limit."";
				$res=fetchdata($iList);
				foreach($res as $val){
                    setIt($nmOrg[$val['mesin']],'');
                    $postDt="style='cursor:pointer' onclick=postTrans('".$val['notransaksi']."',".$page.",'".$val['statusketuntasan']."')";
                
                    $no+=1;
                    $tab.="
                    <tr class=rowcontent>
                    <td align=center>".$no."</td>
                    <td>".$val['notransaksi']."</td>
                    <td>".tanggalnormal($val['tanggal'])."</td>    
                    <td>".$val['pabrik']."</td>
                    <td>".$nmOrg[$val['pabrik']]."</td>
                    <td>".@$val['statasiun']."</td>
                    <td>".@$nmOrg[$val['statasiun']]."</td>
                    <td>".$val['mesin']."</td>
                    <td>".$nmOrg[$val['mesin']]."</td>  
                    <td>".$nmdownst[$val['downstatus']]."</td>      
                    <td>".$val['statusketuntasan']."</td>
                    <td>".getNamaKaryawan($val['updateby'])."</td>";

                    if($val['statPost']==0){
                        $tab.="<td align=center width=25px><img src=images/application/application_edit.png class=resicon  title='Edit' 
                        onclick=\"fillField('".$val['notransaksi']."','".tanggalnormal($val['tanggal'])."',
                        '".substr($val['jam'],0,2)."','".substr($val['jam'],3,2)."','".$val['namapemohon']."',
                        '".$val['statuspemohon']."','".$val['pabrik']."','".$val['statasiun']."','".$val['mesin']."',
                        '".$val['shift']."','".$val['tipeperbaikan']."','".str_replace("\n",'<br />',$val['kegiatan'])."',
                        '".tanggalnormal(substr($val['jammulai'],0,10))."','".substr($val['jammulai'],11,2)."',
                        '".substr($val['jammulai'],14,2)."',
                        '".tanggalnormal(substr($val['jamselesai'],0,10))."','".substr($val['jamselesai'],11,2)."',
                        '".substr($val['jamselesai'],14,2)."','".$val['jumlahjamperbaikan']."',
                        '".$val['statusketuntasan']."','".str_replace("\n",'<br />',$val['hasilkerja'])."','".$nmOrg[$val['mesin']]."',
                        '".str_replace("\n",'<br />',$val['komentarmainten'])."','".str_replace("\n",'<br />',$val['komentarproses'])."','".$val['downstatus']."','".$val['jenisperbaikan']."');\"></td>
                        
                        <td align=center width=25px><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deleteHead('".$val['notransaksi']."');\" ></td>";
                        
                        $tab.="<td align=center width=25px><img src=images/skyblue/posting.png class=resicon  title='Posting' ".$postDt." ></td>";
                    }else{
                        $tab.="<td align=center width=25px></td>";
                        $tab.="<td align=center width=25px></td>";
                        $tab.="<td align=center width=25px><img src=images/skyblue/posted.png class=resicon  title='Posted' ></td>";
                    }
                    $tab.="<td align=center width=25px><img src=images/upload-2-xxl.png class=zImgBtn onclick=showupload('".$val['notransaksi']."') title=Upload></td>";
                    $tab.="<td align=center width=25px><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_rawatmesinht','".$val['notransaksi']."','','pabrik_slave_perbaikan_pdf',event)\">";
                    $tab.="</td></tr>";//,`komentarmainten`,`komentarproses`
                }
            }
			$tab.="</tbody>";
			
			$tab.="<tfoot>";
			## PAGING
			$tab.=createpaging($jlhbrs,$limit,$page,'17','loadData','getPage');
			$tab.="</tfoot>";
			
			$tab.="</table>";
            
			echo $tab;
            
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
	
	
	
	########### case insert detail
	case 'saveBarang':
    if($hargabarang=='')
        $hargabarang=0;
    
		$iBarang="insert into ".$dbname.".pabrik_rawatmesindt (`noreferensi`,`notransaksi`,`kodebarang`,`satuan`,`jumlah`,`keterangan`,`harga`)
		values ('".$nogudang."','".$nodok."','".$kodeBarang."','".$satuanBarang."','".$jumlahBarang."','".$keteranganBarang."','".$hargabarang."')";
                try
                {
                    $owlPDO->exec($iBarang);
                }
                catch (PDOException $e) 
                {
                    print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                    die(); 
                }
	break;
		
        
        
	#####LOAD DETAIL DATA	
	case 'loadDetailBarang':	
            
            $tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <td>".$_SESSION['lang']['nourut']."</td>
            <td>".$_SESSION['lang']['nodok']."</td>
            <td>".$_SESSION['lang']['kodebarang']."</td>
            <td>".$_SESSION['lang']['namabarang']."</td>
            <td>".$_SESSION['lang']['satuan']."</td>
            <td>".$_SESSION['lang']['jumlah']."</td>
            <td>".$_SESSION['lang']['keterangan']."</td>
			 <td>".$_SESSION['lang']['hargasatuan']."</td>
			  <td>".$_SESSION['lang']['total']."</td>
            <td>".$_SESSION['lang']['action']."</td></tr></thead>";
            $no=0;
            $iListBarang="select * from ".$dbname.".pabrik_rawatmesindt where notransaksi='".$nodok."' ";
            $nListBarang=$owlPDO->query($iListBarang) or die(print " Gagal: ".PDOException::getMessage());
            $nListBarang->setFetchMode(PDO::FETCH_ASSOC);
            while($dListBarang=$nListBarang->fetch())
            {
				@$tharga=$dListBarang['harga']*$dListBarang['jumlah'];
				@$gtharga+=$tharga;
                $whBrg="kodebarang='".$dListBarang['kodebarang']."'";
                    $no+=1;
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center>".$no."</td>";
                    $tab.="<td align=right>".$dListBarang['noreferensi']."</td>";
                    $tab.="<td align=right>".$dListBarang['kodebarang']."</td>";
                    $tab.="<td align=left>".getNamaBrg($dListBarang['kodebarang'])."</td>";
                    $tab.="<td align=left>".$dListBarang['satuan']."</td>";
                    $tab.="<td align=right>".$dListBarang['jumlah']."</td>";
                    $tab.="<td align=left>".$dListBarang['keterangan']."</td>";
					$tab.="<td align=right>".number_format($dListBarang['harga'],2)."</td>";
					$tab.="<td align=right>".number_format($tharga,2)."</td>";
                    $tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                            onclick=\"deleteBarang('".$dListBarang['notransaksi']."','".$dListBarang['kodebarang']."');\" ></td>";
            }
			 $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center colspan=8>".$_SESSION['lang']['total']."</td>";
					$tab.="<td align=right>".@number_format($gtharga,2)."</td>";
					$tab.="<td align=center></td>";
            $tab.="</table>";
            echo $tab;
	break;
        
        case 'deleteBarang':
                $iDelBarang="delete from ".$dbname.".pabrik_rawatmesindt where notransaksi='".$nodok."' and kodebarang='".$kodeBarang."' ";
                try
                {
                    $owlPDO->exec($iDelBarang);
                }
                catch (PDOException $e) 
                {
                    print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                    die(); 
                }			
        break;	
        
        /*
        ##pekerjaan
        case 'savePekerjaan':
            $strCount = "select nomor as nourut from " . $dbname . ".pabrik_rawatmesindt_pekerjaan where notransaksi='".$nodok."' order by nomor desc limit 1";
            $rData=fetchData($strCount);
            if($rData[0]['nourut']==0){
                $nomor=1;
            }else{
                $nomor=$rData[0]['nourut']+1;
            }

            $iKaryawan="insert into ".$dbname.".pabrik_rawatmesindt_pekerjaan (`notransaksi`,`nomor`,`rincian`,`kondisi`,`updateby`,`subkodemesin`)
            values ('".$nodok."','".$nomor."','".$rincian."','".$kondisi."','".$_SESSION['standard']['userid']."','".$sbMesin."')";
            try
            {
                $owlPDO->exec($iKaryawan);
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                die(); 
            }
	break;
	*/
	
	 ##pekerjaan
        case 'savePekerjaan':
            $str="insert into ".$dbname.".pabrik_rawatmesindt_pekerjaan (`notransaksi`,`nomor`,`rincian`,`kondisi`,`updateby`,`subkodemesin`)
            values ('".$nodok."','".$nomor."','".$rincian."','".$kondisi."','".$_SESSION['standard']['userid']."','".$sbMesin."')";
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
        
     
	/* 
	case 'loadDetailPekerjaan':
            
            $tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <td>".$_SESSION['lang']['nourut']."</td>
            <td>".$_SESSION['lang']['uraiankerusakan']."</td>
            <td>".$_SESSION['lang']['kondisi']."</td>
            <td>".$_SESSION['lang']['submesin']."</td>
            <td>".$_SESSION['lang']['action']."</td></tr></thead>";
            $no=0;
            $iListPekerjaan="select * from ".$dbname.".pabrik_rawatmesindt_pekerjaan where notransaksi='".$nodok."'"
                    . " order by nomor asc ";
            $nListPekerjaan=$owlPDO->query($iListPekerjaan) or die(print " Gagal: ".PDOException::getMessage());
            $nListPekerjaan->setFetchMode(PDO::FETCH_ASSOC);
            while($dListPekerjaan=$nListPekerjaan->fetch()){
                    $whrSbm="subkodemesin='".$dListPekerjaan['subkodemesin']."'";
                    $optSbKdMsn=makeOption($dbname,'pabrik_5submesin','subkodemesin,namasubmesin',$whrSbm);
                    $whr="kode_preven_maintenance='".$dListPekerjaan['rincian']."'";
                    $opt=makeOption($dbname,'pabrik_5preventive_maintenance','kode_preven_maintenance,preven_keterangan',$whr);
                    $no+=1;
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td align=right>".$dListPekerjaan['nomor']."</td>";
                    $tab.="<td align=left>".$opt[$dListPekerjaan['rincian']]."</td>";
                    $tab.="<td align=left>".$arrKondisi[$dListPekerjaan['kondisi']]."</td>";
                    $tab.="<td align=left>".$optSbKdMsn[$dListPekerjaan['subkodemesin']]."</td>";
                    $tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                            onclick=\"deletePekerjaan('".$dListPekerjaan['notransaksi']."','".$dListPekerjaan['nomor']."');\" ></td>";
            }
            $tab.="</table>";
            echo $tab;
	break;//
	*/
	
	case 'loadDetailPekerjaan'://  <td>".$_SESSION['lang']['submesin']."</td>
            
            $tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <td>".$_SESSION['lang']['nourut']."</td>
            <td>".$_SESSION['lang']['uraiankerusakan']."</td>
            <td>".$_SESSION['lang']['kondisi']."</td>
            <td>".$_SESSION['lang']['action']."</td></tr></thead>";
            $no=0;
            $iListPekerjaan="select * from ".$dbname.".pabrik_rawatmesindt_pekerjaan where notransaksi='".$nodok."'"
                    . " order by nomor asc ";
            $nListPekerjaan=$owlPDO->query($iListPekerjaan) or die(print " Gagal: ".PDOException::getMessage());
            $nListPekerjaan->setFetchMode(PDO::FETCH_ASSOC);
            while($dListPekerjaan=$nListPekerjaan->fetch()){
                    $whrSbm="subkodemesin='".$dListPekerjaan['subkodemesin']."'";
                    $optSbKdMsn=makeOption($dbname,'pabrik_5submesin','subkodemesin,namasubmesin',$whrSbm);
                    $no+=1;
                    $tab.="<tr class=rowcontent>";
                    $tab.="<td align=right>".$dListPekerjaan['nomor']."</td>";
                    $tab.="<td align=left>".$dListPekerjaan['rincian']."</td>";
                    $tab.="<td align=left>".$arrKondisi[$dListPekerjaan['kondisi']]."</td>";
                    // $tab.="<td align=left>".$optSbKdMsn[$dListPekerjaan['subkodemesin']]."</td>";
                    $tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' 
                            onclick=\"deletePekerjaan('".$dListPekerjaan['notransaksi']."','".$dListPekerjaan['nomor']."');\" ></td>";
            }
            $tab.="</table>";
            echo $tab;
	break;//
	
        
        case 'deletePekerjaan':
            $iDelPekerjaan="delete from ".$dbname.".pabrik_rawatmesindt_pekerjaan where notransaksi='".$nodok."' and nomor='".$nomor."' ";
            try
            {
                $owlPDO->exec($iDelPekerjaan);
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                die(); 
            }			
        break;
        
        #karyawan
        case 'saveKaryawan':
            $iKaryawan="insert into ".$dbname.".pabrik_rawatmesindt_karyawan (`notransaksi`,`karyawanid`,`updateby`)
            values ('".$nodok."','".$karyawan."','".$_SESSION['standard']['userid']."')";
            try
            {
                $owlPDO->exec($iKaryawan);
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                die(); 
            }
	break;
        
        case 'deleteKaryawan':
            $iDelPekerjaan="delete from ".$dbname.".pabrik_rawatmesindt_karyawan where notransaksi='".$nodok."' 
                and karyawanid='".$karyawan."' ";
            try
            {
                $owlPDO->exec($iDelPekerjaan);
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                die(); 
            }			
        break;
        
	case 'loadDetailKaryawan':
            
            $tab="<table cellpadding=1 cellspacing=1 border=0 class=sortable>
            <thead>
            <tr class=rowheader>
            <td>".$_SESSION['lang']['nourut']."</td>
            <td>".$_SESSION['lang']['nik']."</td>
            <td>".$_SESSION['lang']['namakaryawan']."</td>
            <td>".$_SESSION['lang']['action']."</td></tr></thead>";
            $no=0;
            $iListKaryawan="select * from ".$dbname.".pabrik_rawatmesindt_karyawan where notransaksi='".$nodok."' ";
            $nListKaryawan=$owlPDO->query($iListKaryawan) or die(print " Gagal: ".PDOException::getMessage());
            $nListKaryawan->setFetchMode(PDO::FETCH_ASSOC);
            while($dListKaryawan=$nListKaryawan->fetch()){
                $whKar="karyawanid='".$dListKaryawan['karyawanid']."'";
                $no+=1;
                $tab.="<tr class=rowcontent>";
                $tab.="<td align=center>".$no."</td>";
                $tab.="<td align=right>".getKary($dListKaryawan['karyawanid'],'nik')."</td>";
                $tab.="<td align=left>".getKary($dListKaryawan['karyawanid'])."</td>";
                $tab.="<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deleteKaryawan('".$dListKaryawan['notransaksi']."','".$dListKaryawan['karyawanid']."');\" ></td>";
                
            }
            $tab.="</table>";
            echo $tab;
	break;

	/*
    case'getSbMsn':
        $optDat.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sData="select subkodemesin,namasubmesin from ".$dbname.".pabrik_5submesin where kodemesin='".$kdmesin."'";
        $rData=fetchdata($sData);
        foreach($rData as $row=>$data){
            if($data['subkodemesin']==$sbMesin){
                $optDat.="<option value='".$data['subkodemesin']."' selected>".$data['namasubmesin']."</option>";
            }else{
                $optDat.="<option value='".$data['subkodemesin']."'>".$data['namasubmesin']."</option>";    
            }
            
        }
        $optactivity.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sData="select kode_preven_maintenance from ".$dbname.".pabrik_5preventive_maintenance where kodemesin='".$kdmesin."'";
        $rData=fetchdata($sData);
        foreach($rData as $row=>$data){
            if($data['kode_preven_maintenance']==$activity){
                $optactivity.="<option value='".$data['kode_preven_maintenance']."' selected>".$data['kode_preven_maintenance']."</option>";
            }else{
                $optactivity.="<option value='".$data['kode_preven_maintenance']."'>".$data['kode_preven_maintenance']."</option>";    
            }
        }

        echo $optDat . "####" . $optactivity;
    break;
	*/
	
	case'getSbMsn':
        $optDat.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sData="select subkodemesin,namasubmesin from ".$dbname.".pabrik_5submesin where kodemesin='".$kdmesin."'";
        $rData=fetchdata($sData);
        foreach($rData as $row=>$data){
            if($data['subkodemesin']==$sbMesin){
                $optDat.="<option value='".$data['subkodemesin']."' selected>".$data['namasubmesin']."</option>";
            }else{
                $optDat.="<option value='".$data['subkodemesin']."'>".$data['namasubmesin']."</option>";    
            }
            
        }
        echo $optDat;
    break;

    case'getactivity':
        $sData="select preven_keterangan from ".$dbname.".pabrik_5preventive_maintenance where kode_preven_maintenance='".$rincian."'";
        $rData=fetchdata($sData);
        foreach($rData as $row=>$data){
            $activity=$data['preven_keterangan'];
        }

        echo $activity ;
    break;
        
    case'postingDt':
        $supdate="update ".$dbname.".pabrik_rawatmesinht set statPost=1  where notransaksi='".$nodok."'";
        try{
            $owlPDO->exec($supdate);
        }
        catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;
}

// function seticonfile($tipe){
	// if($tipe=='.pdf'){$images = 'images/uploader/pdf.png';}
	// else if($tipe=='.jpeg'||$tipe=='.jpg'){$images = 'images/uploader/jpg.png';}
	// else if($tipe=='.gif'){$images = 'images/uploader/gif.png';}
	// else if($tipe=='.png'){$images = 'images/uploader/png.png';}
	// else if($tipe=='.xls'||$tipe=='.xlsx'){$images = 'images/uploader/excel.png';}
	// else if($tipe=='.doc'||$tipe=='.docx'){$images = 'images/uploader/word.png';}
	// else{$images = 'images/uploader/onebit_37.png';}
	
	// return $images;
// }


?>	