<?php

require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses = checkPostGet('proses', '');
$pages = checkPostGet('page','');

$pt = checkPostGet('pt', '');
$supp = checkPostGet('supp', '');
$alamat_sup = checkPostGet('alamat_sup', '');
$npwp_sup = checkPostGet('npwp_sup', '');
$bank_acc = checkPostGet('bank_acc', '');
$kontrak = checkPostGet('kontrak', '');
$kontrakcari = checkPostGet('kontrakcari', '');
$tgl = tanggalsystem(checkPostGet('tgl', ''));
$tgl1 = tanggalsystem(checkPostGet('tgl1', ''));
$tgl2 = tanggalsystem(checkPostGet('tgl2', ''));
$delivtime = checkPostGet('delivtime', '');
$tmpt_krm = checkPostGet('tmpt_krm', '');
$invc_krm = checkPostGet('invc_krm', '');
$term_pay = checkPostGet('term_pay', '');
$ppN = str_replace(',','',checkPostGet('ppN', ''));
$ppH = str_replace(',','',checkPostGet('ppH', ''));
$cttn = checkPostGet('cttn', '');
$status = checkPostGet('status', '');

$kdbrg = checkPostGet('kdbrg', '');
$qty = checkPostGet('qty', '');
$harga = checkPostGet('harga', '');
$daTtgl = checkPostGet('daTtgl', '');

$namafile=checkPostGet('namafile','');
$kriteriaefil=checkPostGet('kriteriaefil','');
$createtime=date('Y-m-d H:i:s');

switch($proses){
	case'getsatuan':
		$str="select satuan from ".$dbname.".log_5masterbarang where kodebarang='".$kdbrg."' order by kodebarang asc";
		$res=fetchdata($str);
		$satuan=$res[0]['satuan'];
		
		echo $satuan."#####".$kdbrg;
    break;

    case'LoadData':
		$tab="";
        $limit = 20;
        $page = 0;
        if(isset($pages)){
			$page=$pages;
			if($page<0) 
				$page=0;
        }
		$offset=@($page*$limit);
		$no=@(($page*$limit));
		
		$where="";
		if($kontrakcari!=''){
			$where.=" and nokontrak like '%".$kontrakcari."%'";
		}
		if($daTtgl!=''){
			$thn=substr($daTtgl,6,4);
			$bln=substr($daTtgl,3,2);
			$tgl=substr($daTtgl,0,2);
			$where.=" and createtime like '%".$thn.'-'.$bln.'-'.$tgl."%'";
		}
		

        $str = "select count(*) as jmlhrow from ".$dbname.".log_kontrakpayung where 1=1 ".$where." order by `createtime` desc";
		$res=fetchdata($str);
		$jlhbrs = $res[0]['jmlhrow'];
		
		if($jlhbrs <= 0){
			$tab.="<tr class=rowcontent><td colspan='13' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$no = 0;
			
			$str = "select * from ".$dbname.".log_kontrakpayung where 1=1 ".$where." order by createtime desc limit ".$offset.",".$limit."";
			$res=fetchdata($str);
			foreach($res as $val){
				$no++;
				
				$optbrg=makeOption($dbname,"log_5masterbarang","kodebarang,namabarang","kodebarang='".$val['kodebarang']."'");
				$optpt=makeOption($dbname,"organisasi","kodeorganisasi,namaorganisasi","kodeorganisasi='".$val['pt']."'");
				$optsupp=makeOption($dbname,"log_5supplier","supplierid,namasupplier","supplierid='".$val['supplierid']."'");
				$optstatus= array('1' =>'Aktif' ,'0' =>'Tidak Aktif' );
				
				$tab.="<tr class=rowcontent id='tr_".$no."'>
					<td align=center>".$no."</td>
					
					<td>".$val['nokontrak']."</td>
					<td style='min-width:80px;text-align:center'>".tanggalnormal($val['createtime'])."</td>
					<td style='min-width:80px;text-align:center'>".tanggalnormal($val['tanggalawal'])."</td>
					<td style='min-width:80px;text-align:center'>".tanggalnormal($val['tanggalakhir'])."</td>
					<td>".$optsupp[$val['supplierid']]."</td>
					<td style='text-align:center'>".$optstatus[$val['status']]."</td>
					<td>".$val['ket']."</td>";
					
				if($val['posting']==0){
					$tab.="<td style='text-align:center'>
						<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$val['nokontrak']."','".$val['pt']."');\">&nbsp;
						<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldata('".$val['nokontrak']."','".$val['pt']."');\">&nbsp;
						<img src=images/".$_SESSION['theme']."/posting.png class=resicon  title='posting' onclick=\"postingData('".$val['nokontrak']."','".$val['pt']."');\"></td>";
                }else{
					$tab.="<td style='text-align:center'>
						<img src=images/skyblue/posted.png class=resicon  title='posting'>
					</td>";
				}
				$tab.="<td style='text-align:center'>
					<img src='images/skyblue/zoom.png' class='zImgBtn' onclick=\"viewdetail('".$val['nokontrak']."','html',event)\" title='Lihat Detail'>
				</td>";
				
				$tab.="</tr>";
			}
			
			## PAGING
			$tab.=createpaging($jlhbrs,$limit,$page,'10','loadData','getPage');
			$tab.="</table>";
		}
		
		echo $tab;
	break;
	
	case'getdatasupplier':
		## GET ALAMAT
		$optalamat="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select id_alamat,alamat,kota from ".$dbname.".log_5supalamat where supplierid='".$supp."' and status='1'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($alamat_sup==$val['id_alamat']){
				$optalamat.="<option value='".$val['id_alamat']."' selected>".$val['alamat'].", ".$val['kota']."</option>";				
			}else{
				$optalamat.="<option value='".$val['id_alamat']."'>".$val['alamat'].", ".$val['kota']."</option>";				
			}
		}
		
		## GET NPWP
		$optnpwp="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select npwp from ".$dbname.".log_5supnpwp where supplierid='".$supp."' and active='1'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($npwp_sup==$val['npwp']){
				$optnpwp.="<option value='".$val['npwp']."' selected>".$val['npwp']."</option>";				
			}else{
				$optnpwp.="<option value='".$val['npwp']."'>".$val['npwp']."</option>";				
			}
		}
		
		## GET BANK
		$optbank="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select rekening,bank,an from ".$dbname.".log_5rekbank where supplierid='".$supp."' and isactive='1'";
		$res=fetchdata($str);
		foreach($res as $val){
			if($bank_acc==$val['rekening']){
				$optbank.="<option value='".$val['rekening']."' selected>".$val['rekening']."-".$val['bank']."-".$val['an']."</option>";				
			}else{
				$optbank.="<option value='".$val['rekening']."'>".$val['rekening']."-".$val['bank']."-".$val['an']."</option>";				
			}
		}
		
		echo $optalamat."###".$optnpwp."###".$optbank;
	break;

    case'insert':
		try {
			$owlPDO->beginTransaction();
			
			if($supp==''){
				throw new PDOException($_SESSION['lang']['namasupplier']." harus dipilih");
			}
			if($alamat_sup==''){
				throw new PDOException($_SESSION['lang']['alamat']." ".$_SESSION['lang']['supplier']." harus dipilih");
			}
			if($npwp_sup==''){
				throw new PDOException($_SESSION['lang']['npwp']." ".$_SESSION['lang']['supplier']." harus dipilih");
			}
			if($bank_acc==''){
				throw new PDOException($_SESSION['lang']['norekeningbank']." harus dipilih");
			}
			if($kontrak==''){
				throw new PDOException($_SESSION['lang']['kontrak']." harus diisi");
			}
			if($delivtime==''){
				throw new PDOException($_SESSION['lang']['waktupenyerahan']." harus dipilih");
			}
			if($kontrak==''){
				throw new PDOException($_SESSION['lang']['almt_kirim']." ".$_SESSION['lang']['barang']." harus dipilih");
			}
			if($kontrak==''){
				throw new PDOException($_SESSION['lang']['almt_kirim']." ".$_SESSION['lang']['invoice']." harus dipilih");
			}
			if($kontrak==''){
				throw new PDOException($_SESSION['lang']['syaratPem']." harus dipilih");
			}
			
			if($_SESSION['ktrkpayung']==array()){
				throw new PDOException("List item barang masih kosong");
			}
			
			## CEK NO KONTRAK
			$str="select nokontrak,createtime from ".$dbname.".log_kontrakpayung where nokontrak='".$kontrak."'";
			$res=fetchdata($str);
			$countitem=count($res);
			if($countitem>0){
				throw new PDOException("No. Kontrak sudah pernah diinput sebelumnya.");
			}else{
				$str="insert into ".$dbname.".log_kontrakpayung (nokontrak,tanggal,supplierid,supplier_alamat,supplier_npwp,supplier_rekening,pt,tanggalawal,tanggalakhir,waktu_kirim,id_franco_brg,id_franco_inv,top,ppn,pph,ket,posting,status,createdby,createtime,updateby,updatetime) values ('".$kontrak."','".$tgl."','".$supp."','".$alamat_sup."','".$npwp_sup."','".$bank_acc."','','".$tgl1."','".$tgl2."','".$delivtime."','".$tmpt_krm."','".$invc_krm."','".$term_pay."','".$ppN."','".$ppH."','".$cttn."','0','".$status."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
				$owlPDO->exec($str); 
					
				foreach($_SESSION['ktrkpayung'] as $key=>$val){
					$str="insert into ".$dbname.".log_kontrakpayungdt (nokontrak,kodebarang,kuantitas,harga,satuan) values ('".$kontrak."','".$val['kdbrg']."','".$val['qty']."','".$val['harga']."','".$val['satuan']."')";
					$owlPDO->exec($str);
				}
				
				$createtime=date("Y-m-d H:i:s");
				foreach($_SESSION['ktrkpayungimg'] as $key=>$val){
					$str="insert into ".$dbname.".listfilekontrakpayung(nokontrak,namafile,formaticon,kriteriaefil,status,createdby,createdtime) values('".$kontrak."','".$val['namafile']."','".$val['filetype']."','','1','".$_SESSION['standard']['userid']."','".$createtime."')";
					$owlPDO->exec($str);
				}
			}
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning :\n" . addslashes($e->getMessage());
		}
	break;

    case'showData':
		$_SESSION['ktrkpayung']=array();
		$_SESSION['ktrkpayungimg']=array();
		
		$str="select * from ".$dbname.".log_kontrakpayungdt where nokontrak='".$kontrak."'";
		$res=fetchdata($str);
		foreach($res as $val){
			$newdata = array(
				'kdbrg'=>$val['kodebarang'],
				'satuan'=>$val['satuan'],
				'qty'=>$val['kuantitas'],
				'harga'=>$val['harga']
			);
			array_push($_SESSION['ktrkpayung'],$newdata);
		}
		
		$str="select * from ".$dbname.".listfilekontrakpayung where nokontrak='".$kontrak."'";
		$res=fetchdata($str);
		foreach($res as $val){
			$newdata = array(
				'namafile'=>$val['namafile'],
				'filetype'=>$val['filetype']
			);
			array_push($_SESSION['ktrkpayungimg'],$newdata);
		}
	
		$data=array();
        $str="select * from ".$dbname.".log_kontrakpayung where nokontrak='".$kontrak."' and pt='".$pt."'";
		$res=fetchdata($str);
		$data[0]['nokontrak']=$res[0]['nokontrak'];
		$data[0]['supp']=$res[0]['supplierid'];
		$data[0]['alamat_sup']=$res[0]['supplier_alamat'];
		$data[0]['npwp_sup']=$res[0]['supplier_npwp'];
		$data[0]['bank_acc']=$res[0]['supplier_rekening'];
		$data[0]['tgl']=tanggalnormal($res[0]['tanggal']);
		$data[0]['tgl1']=tanggalnormal($res[0]['tanggalawal']);
		$data[0]['tgl2']=tanggalnormal($res[0]['tanggalakhir']);
		$data[0]['delivtime']=$res[0]['waktu_kirim'];
		$data[0]['tmpt_krm']=$res[0]['id_franco_brg'];
		$data[0]['invc_krm']=$res[0]['id_franco_inv'];
		$data[0]['term_pay']=$res[0]['top'];
		$data[0]['ppN']=$res[0]['ppn'];
		$data[0]['ppH']=$res[0]['pph'];
		$data[0]['cttn']=$res[0]['ket'];
		$data[0]['status']=$res[0]['status'];
		
		echo json_encode($data);
	break;
		
    case'update':
		try {
			$owlPDO->beginTransaction();
			
			if($supp==''){
				throw new PDOException($_SESSION['lang']['namasupplier']." harus dipilih");
			}
			if($alamat_sup==''){
				throw new PDOException($_SESSION['lang']['alamat']." ".$_SESSION['lang']['supplier']." harus dipilih");
			}
			if($npwp_sup==''){
				throw new PDOException($_SESSION['lang']['npwp']." ".$_SESSION['lang']['supplier']." harus dipilih");
			}
			if($bank_acc==''){
				throw new PDOException($_SESSION['lang']['norekeningbank']." harus dipilih");
			}
			if($kontrak==''){
				throw new PDOException($_SESSION['lang']['kontrak']." harus diisi");
			}
			if($delivtime==''){
				throw new PDOException($_SESSION['lang']['waktupenyerahan']." harus dipilih");
			}
			if($kontrak==''){
				throw new PDOException($_SESSION['lang']['almt_kirim']." ".$_SESSION['lang']['barang']." harus dipilih");
			}
			if($kontrak==''){
				throw new PDOException($_SESSION['lang']['almt_kirim']." ".$_SESSION['lang']['invoice']." harus dipilih");
			}
			if($kontrak==''){
				throw new PDOException($_SESSION['lang']['syaratPem']." harus dipilih");
			}
			
			if($_SESSION['ktrkpayung']==array()){
				throw new PDOException("List item barang masih kosong");
			}
			
			$str = "update ".$dbname.".log_kontrakpayung set tanggal='".$tgl."', supplierid='".$supp."', supplier_alamat='".$alamat_sup."', supplier_npwp='".$npwp_sup."', supplier_rekening='".$bank_acc."', tanggalawal='".$tgl1."', tanggalakhir='".$tgl2."', waktu_kirim='".$delivtime."', id_franco_brg='".$tmpt_krm."', id_franco_inv='".$invc_krm."', top='".$term_pay."', ppn='".$ppN."', pph='".$ppH."', ket='".$cttn."', status='".$status."', updateby='".$_SESSION['standard']['userid']."', updatetime='".date('Y-m-d H:i:s')."' where  nokontrak='".$kontrak."'";
			$owlPDO->exec($str);
			
			$str="delete from ".$dbname.".log_kontrakpayungdt where nokontrak='".$kontrak."'";
			$owlPDO->exec($str);
			
			foreach($_SESSION['ktrkpayung'] as $key=>$val){
				$str="insert into ".$dbname.".log_kontrakpayungdt (nokontrak,kodebarang,kuantitas,harga,satuan) values ('".$kontrak."','".$val['kdbrg']."','".$val['qty']."','".$val['harga']."','".$val['satuan']."')";
				$owlPDO->exec($str);
			}
			
			$str="delete from ".$dbname.".listfilekontrakpayung where nokontrak='".$kontrak."'";
			$owlPDO->exec($str);
			
			$createtime=date("Y-m-d H:i:s");
			foreach($_SESSION['ktrkpayungimg'] as $key=>$val){
				$str="insert into ".$dbname.".listfilekontrakpayung(nokontrak,namafile,formaticon,kriteriaefil,status,createdby,createdtime) values('".$kontrak."','".$val['namafile']."','".$val['filetype']."','','1','".$_SESSION['standard']['userid']."','".$createtime."')";
				$owlPDO->exec($str);
			}
			
			$owlPDO->commit();
		}catch(PDOException $e){
			$owlPDO->rollback();
			echo "Warning :\n" . addslashes($e->getMessage());
		}
        break;

    case'delData':
        $sDel = "delete from " . $dbname . ".log_kontrakpayung where  nokontrak='" . $kontrak . "' and pt='" . $pt . "'";
        try{
			$owlPDO->exec($sDel); 
		}
		catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
		}
        break;

    case'CekData':
       
        break;

    case 'submitfile':
		$tgl = date("YmdHis");
		$data = $_POST;
		
		if($data['fileupload']!=''){
			if($_FILES['file']['error']==0){
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $newfilename."_".$tgl."".$filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];		
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
					if($_FILES['file']['size'] <= 512000){
						$newdata = array(
							'namafile'=>$filename,
							'filetype'=>$filetype
						);
						
						if($_SESSION['ktrkpayungimg'] != array()){
							foreach($_SESSION['ktrkpayungimg'] as $key=>$row)
							{
								if($row['namafile'] == $filename)
								{
									exit("Warning : Item ini sudah pernah diinput sebelumnya.");
								}
							}
							array_push($_SESSION['ktrkpayungimg'],$newdata);
						}else{
							array_push($_SESSION['ktrkpayungimg'],$newdata);
						}
						move_uploaded_file($file_tmpname,"filegis/$filename");
					}else{
						exit("warning : Ukuran file upload maksimal 512kb");
					}
				}else{
					exit("Warning : Format file upload harus .jpg atau .jpeg");
				}
			}
		}
    break;

    case 'loadfiles':
		$tab="";
		$no=0;
		foreach($_SESSION['ktrkpayungimg'] as $key=>$val){
			$no++;
			$tab.="<tr class='rowcontent'>";
			$tab.="<td style='text-align:right'>".$no."</td>";
			$tab.="<td><a href='filegis/".$val['namafile']."' download>".$val['namafile']."</a></td>";
			$tab.="<td style='text-align:center'>
				<img title='Delete' class=resicon onclick=\"deletefile('".$val['namafile']."')\" src='images/delete_32.png'/
			</td>";
			$tab.="</tr>";
		}
        echo $tab;
    break;

	case'deletefile':
        foreach($_SESSION['ktrkpayungimg'] as $key=>$row){
			if($row['namafile'] == $namafile)
			{
				// $path = "filegis/".$namafile;
				// unlink($path);
				unset($_SESSION['ktrkpayungimg'][$key]);

				if ($kode!='') {
					$str="delete from ".$dbname.".listfileupload where namafile='".$namafile."' and notransaksi='".$kode."'";
					try{$owlPDO->exec($str); }
					catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}
				

			}
		}
    break;

     case'postingData':

       $sUpd = "update " . $dbname . ".log_kontrakpayung set  posting='1' where  nokontrak='" . $kontrak . "' and pt='" . $pt . "'";
       
        try{
            $owlPDO->exec($sUpd); 
        }
        catch (PDOException $e){
            echo "DB Error : " . $e->getMessage();
        }

        
    break;
	
	case'additem':
		$kdbrg = checkPostGet('kdbrg', '');
		$satuan = checkPostGet('satuan', '');
		$qty = str_replace(',','',checkPostGet('qty', ''));
		$harga = str_replace(',','',checkPostGet('harga', ''));
		
		$newdata = array(
			'kdbrg'=>$kdbrg,
			'satuan'=>$satuan,
			'qty'=>($qty==''?'0':$qty),
			'harga'=>$harga
		);
		
		if($_SESSION['ktrkpayung'] != array()){
			foreach($_SESSION['ktrkpayung'] as $key=>$val){
				if($val['kdbrg'] == $kdbrg){
					exit("Gagal : Item ini sudah pernah diinput sebelumnya.");
				}
			}
			array_push($_SESSION['ktrkpayung'],$newdata);
		}else{
			array_push($_SESSION['ktrkpayung'],$newdata);
		}
	break;
	
	case'loaditemkontrak':
		$tab="";
		$no=0;
		foreach($_SESSION['ktrkpayung'] as $key=>$val){
			$optnmbarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kdbrg']."'");
			$no++;
			$tab.="<tr class='rowcontent'>";
			$tab.="<td style='text-align:center'>".$no."</td>";
			$tab.="<td>".$val['kdbrg']."</td>";
			$tab.="<td>".$optnmbarang[$val['kdbrg']]."</td>";
			$tab.="<td>".$val['satuan']."</td>";
			$tab.="<td style='text-align:center'>".hidezerodecimal($val['qty'],2)."</td>";
			$tab.="<td style='text-align:right'>".hidezerodecimal($val['harga'],0)."</td>";
			$tab.="<td style='text-align:center'>
				<img title='Delete' class=resicon onclick=\"deleteitemkontrak('".$val['kdbrg']."')\" src='images/delete_32.png'/
			</td>";
			$tab.="</tr>";
		}
		echo $tab;
	break;
	
	case'deleteitemkontrak':
		$kdbrg = checkPostGet('kdbrg', '');
		foreach($_SESSION['ktrkpayung'] as $key=>$val){
			if($val['kdbrg'] == $kdbrg){
				unset($_SESSION['ktrkpayung'][$key]);
			}
		}
	break;
	
	case'cancelSave':
		$_SESSION['ktrkpayung']=array();
	break;
	
	case'viewdetail':
		$tab="";
		$nokontrak = checkPostGet('nokontrak', '');
		
		$str="select * from ".$dbname.".log_kontrakpayung where nokontrak='".$nokontrak."'";
		$res=fetchdata($str);
		$supplierid=$res[0]['supplierid'];
		$supplier_alamat=$res[0]['supplier_alamat'];
		$supplier_npwp=$res[0]['supplier_npwp'];
		$supplier_rekening=$res[0]['supplier_rekening'];
		$createtime=tanggalnormal($res[0]['createtime']);
		$tanggalawal=tanggalnormal($res[0]['tanggalawal']);
		$tanggalakhir=tanggalnormal($res[0]['tanggalakhir']);
		$waktu_kirim=$res[0]['waktu_kirim'];
		$id_franco_brg=$res[0]['id_franco_brg'];
		$id_franco_inv=$res[0]['id_franco_inv'];
		$top=$res[0]['top'];
		$ppn=$res[0]['ppn'];
		$pph=$res[0]['pph'];
		
		$ket=$res[0]['ket'];
		$status=($res[0]['status']=='1'?'Aktif':'Non-Aktif');
		
		## GET ALAMAT SUPPLIER
		$str="select alamat,kota from ".$dbname.".log_5supalamat where id_alamat='".$supplier_alamat."'";
		$res=fetchdata($str);
		$vsupplier_alamat=$res[0]['alamat'].", ".$res[0]['kota'];
		
		## GET REKENING SUPPLIER
		$str="select rekening,bank,an from ".$dbname.".log_5rekbank where rekening='".$supplier_rekening."'";
		$res=fetchdata($str);
		$vsupplier_rekening=$res[0]['rekening']."-".$res[0]['bank']."-".$res[0]['an'];
		
		## GET WAKTU PENYERAHAN
		$str="select nama from ".$dbname.".log_5delivtime where kode='".$waktu_kirim."'";
		$res=fetchdata($str);
		$vwaktu_kirim=$res[0]['nama'];

		## GET LOKASI PENGIRIMAN BARANG DAN TAGIHAN
		$str="select franco_name from ".$dbname.".setup_franco where id_franco='".$id_franco_brg."'";
		$res=fetchdata($str);
		$vid_franco_brg=$res[0]['franco_name'];
		
		$str="select franco_name from ".$dbname.".setup_franco where id_franco='".$id_franco_inv."'";
		$res=fetchdata($str);
		$vid_franco_inv=$res[0]['franco_name'];

		## GET SYARAT BAYAR
		$str="select jenis,keterangan from ".$dbname.".log_5syaratbayar where kode='".$top."'";
		$res=fetchdata($str);
		$vtop=$res[0]['keterangan']." (".$res[0]['jenis'].")";
		
		$optsup = makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$supplierid."'");
		
		$tab.="<table cellpadding=3>
			<tr>
				<td>No. ".$_SESSION['lang']['kontrak']."</td>
				<td>:</td>
				<td>".$nokontrak."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['tanggal']." Buat</td>
				<td>:</td>
				<td>".$createtime."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['namasupplier']."</td>
				<td>:</td>
				<td>".$optsup[$supplierid]."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['alamat']." ".$_SESSION['lang']['supplier']."</td>
				<td>:</td>
				<td>".$vsupplier_alamat."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['npwp']." ".$_SESSION['lang']['supplier']."</td>
				<td>:</td>
				<td>".$supplier_npwp."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['norekeningbank']."</td>
				<td>:</td>
				<td>".$vsupplier_rekening."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['periode']." Kontrak</td>
				<td>:</td>
				<td>".$tanggalawal." s/d ".$tanggalakhir."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['waktupenyerahan']."</td>
				<td>:</td>
				<td>".$vwaktu_kirim."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['almt_kirim']." ".$_SESSION['lang']['barang']."</td>
				<td>:</td>
				<td>".$vid_franco_brg."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['almt_kirim']." ".$_SESSION['lang']['invoice']."</td>
				<td>:</td>
				<td>".$vid_franco_inv."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['syaratPem']."</td>
				<td>:</td>
				<td>".$vtop."</td>
			</tr>
			<tr>
				<td>PPn (%)</td>
				<td>:</td>
				<td>".hidezerodecimal($ppn,2)."</td>
			</tr>
			<tr>
				<td>PPh (%)</td>
				<td>:</td>
				<td>".hidezerodecimal($pph,2)."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['note']."</td>
				<td>:</td>
				<td>".$ket."</td>
			</tr>
			<tr>
				<td>".$_SESSION['lang']['status']."</td>
				<td>:</td>
				<td>".$status."</td>
			</tr>
		</table>
		<br>";
		
		## GET ITEM BARANG
		$str="select * from ".$dbname.".log_kontrakpayungdt where nokontrak='".$nokontrak."' order by kodebarang asc";
		$res=fetchdata($str);
		$arritem=$res;
		
		## GET FILE UPLOAD
		$str="select * from ".$dbname.".listfilekontrakpayung where nokontrak='".$nokontrak."' order by namafile asc";
		$res=fetchdata($str);
		$arrfile=$res;
		
		$tab.="<fieldset style='float:left'>
			<legend>List Item Barang</legend>
			<table cellspacing='1' cellpadding='3' border='0' class='sortable'>
				<thead>
				<tr class='rowheader'>
					<td>".$_SESSION['lang']['nourut']."</td>
					<td>".$_SESSION['lang']['kodebarang']."</td>
					<td>".$_SESSION['lang']['namabarang']."</td>
					<td>".$_SESSION['lang']['satuan']."</td>
					<td>".$_SESSION['lang']['kuantitas']."</td>
					<td>".$_SESSION['lang']['hargasatuan']."</td>
				</tr>
				</thead>
				<tbody>";
				if(count($arritem) > 0){
					foreach($arritem as $val){
						$optnmbarang = makeOption($dbname,'log_5masterbarang','kodebarang,namabarang',"kodebarang='".$val['kodebarang']."'");
						$no++;
						$tab.="<tr class='rowcontent'>";
						$tab.="<td style='text-align:center'>".$no."</td>";
						$tab.="<td>".$val['kodebarang']."</td>";
						$tab.="<td>".$optnmbarang[$val['kodebarang']]."</td>";
						$tab.="<td>".$val['satuan']."</td>";
						$tab.="<td style='text-align:center'>".hidezerodecimal($val['kuantitas'],2)."</td>";
						$tab.="<td style='text-align:right'>".hidezerodecimal($val['harga'],0)."</td>";
						$tab.="</tr>";
					}
				}else{
					$tab.="<tr class='rowcontent'><td colspan=6 style='text-align:center;color:red'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
				}
				$tab.="</tbody>
			</table>
			</fieldset>
			<br><br>
			<fieldset style='float:left'>
			<legend>List File Upload</legend>
			<table class=sortable cellspacing=1 border=0>
				<thead> 
				<tr>
					<td align=center>".$_SESSION['lang']['nourut']."</td>
					<td align=center>".$_SESSION['lang']['namafile']."</td>
				</tr>
				</thead>
				<tbody>";
				$no=0;
				if(count($arrfile) > 0){
					foreach($arrfile as $val){
						$no++;
						$tab.="<tr class='rowcontent'>";
						$tab.="<td style='text-align:right'>".$no."</td>";
						$tab.="<td><a href='filegis/".$val['namafile']."' download>".$val['namafile']."</a></td>";
						$tab.="</tr>";
					}
				}else{
					$tab.="<tr class='rowcontent'><td colspan=2 style='text-align:center;color:red'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
				}
				$tab.="</tbody>
			</table>
			</fieldset>";
		
		echo $tab;
	break;
}
?>