<?php
session_start();
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

if($_POST['proses']=='createTable') {
    # Get Data
    $query = selectQuery($dbname,'log_prapodt',"*","`nopp`='".$_POST['id']."'"); 
    $data = fetchData($query);
    	
	// Cek Header
	$queryH = selectQuery($dbname,'log_prapoht',"*","`nopp`='".$_POST['id']."'"); 
    $dataH = fetchData($queryH);
	
	if(empty($dataH)) {
        // Get PT
        $qPt = selectQuery($dbname,'organisasi',"induk","`kodeorganisasi`='".$_POST['rkd_bag']."'");
        $resPt = fetchData($qPt);
	    
        $nopp=$_POST['id'];
		//$tgl=tanggalsystem($_POST['rtgl_pp']);
		$tgl=date('Y-m-d');
                $kodeorg=$resPt[0]['induk'];
		$id_user=$_SESSION['standard']['userid'];
                
		$ins="insert into ".$dbname.".log_prapoht (`nopp`,`kodeorg`,`tanggal`,`dibuat`)".
			" values ('".$nopp."','".$kodeorg."','".$tgl."','".$id_user."')";
		try{
			$owlPDO->exec($ins); 
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
	}
    
    # Create Detail Table
    createTabDetail($_POST['id'],$data);
} else {
	$data = $_POST;
	$tglsdt=tanggalsystem($data['tgl_sdt']);
	$rtglpp=tanggalsystem(isset($data['rtgl_pp']) ? $data['rtgl_pp'] : "0000-00-00");
    unset($data['proses']);
	
    switch($_POST['proses']) {
        case 'detail_add' :
            # Check Valid Data
			$starttime=strtotime($data['rtgl_pp']);//time();// tanggal sekarang
			$endtime=strtotime($data['tgl_sdt']);//tanggal pembuatan dokumen
			$timediff = $endtime-$starttime;
			$days=intval($timediff/86400);
			
            if(($data['kd_brg']=='') or ($data['jmlhDiminta']=='') or ($tglsdt<$rtglpp) or $days<7) {
                echo "Error : ".$_SESSION['lang']['pp7hari'];
                exit;
            }
			$optBarang = makeOption($dbname,'log_5masterbarang','kodebarang,satuan',"kodebarang='".$data['kd_brg']."'");
			$ql="select * from ".$dbname.".log_prapoht where `nopp`='".$data['kode']."'" ;
			
			$qry=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
			$qry->setFetchMode(PDO::FETCH_ASSOC);
			$res=$qry->fetch();
			if($res['nopp']!='')
			{
                # Make Query
                $data['tgl_sdt']=tanggalsystem($data['tgl_sdt']);
                $query="insert into ".$dbname.".log_prapodt (`nopp`,`kodebarang`,`kd_anggran`,`jumlah`,`tgl_sdt`,`keterangan`,`kodevhc`,`satuanpp`,`jumlahpp`,`updateby`)
					values ('".$data['kode']."','".$data['kd_brg']."','".$data['kd_angrn']."','".$data['jmlhDiminta']."',
						'".$data['tgl_sdt']."','".$data['ket']."','".$data['kd_vhc']."','".$optBarang[$data['kd_brg']]."','".$data['jmlhDiminta']."','".$_SESSION['standard']['userid']."')";
                
				# Insert Data
				try{
					$owlPDO->exec($query); 
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
            } else {	
				$nopp=$data['kode'];
				$tgl=tanggalsystem($data['rtgl_pp']);
				$kodeorg=$data['rkd_bag'];
				$id_user=$_POST['user_id'];
				
				$sorg="select alokasi from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";
				$qorg=$owlPDO->query($sorg) or die(print " Gagal: ".PDOException::getMessage());
				$qorg->setFetchMode(PDO::FETCH_ASSOC);
				$rorg=$qorg->fetch();
				
				$kd_org=$rorg['alokasi'];
				$data['tgl_sdt']=tanggalsystem($data['tgl_sdt']);
				$query="insert into ".$dbname.".log_prapodt (`nopp`,`kodebarang`,`kd_anggran`,`jumlah`,`tgl_sdt`,`keterangan`,`kodevhc`,`jumlahpp`) values ('".$data['kode']."','".$data['kd_brg']."','".$data['kd_angrn']."','".$data['jmlhDiminta']."','".$data['tgl_sdt']."','".$data['ket']."','".$data['kd_vhc']."','".$data['jmlhDiminta']."')";
				
				# Insert Data
				try{
					$owlPDO->exec($query); 
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
            }
            break;
		
        case 'detail_edit' :
            # Check Valid Data
            if(($data['nopp']=='') or ($data['kd_brg']=='') or ($data['jmlhDiminta']=='')) {
                echo "Error : Data should not be empty";
                exit;
            }

            # Rearrange Data
			$data['tgl_sdt']=tanggalsystem($data['tgl_sdt']);

            # Create Condition
            $where = "`nopp`='".$data['nopp']."'";
            $where .= " and `kodebarang`='".$data['kd_brg']."'";

            # Make Query
			$query = "update ".$dbname.".`log_prapodt` set kodebarang='".$data['kd_brg']."',kd_anggran='".$data['kd_angrn']."',jumlah='".$data['jmlhDiminta']."',jumlahpp='".$data['jmlhDiminta']."',tgl_sdt='".$data['tgl_sdt']."', keterangan='".$data['ket']."',kodevhc='".$data['kd_vhc']."' where `nopp`='".$data['nopp']."' and `kodebarang`='".$data['oldKdbrg']."'";
			
			# Update Data
            try{
				$owlPDO->exec($query); 
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
			echo $query; 
			exit();
		break;

        case 'detail_delete' :
            $data = $_POST;

            # Rearrange Data
            $tmpTgl = tanggalsystem($data['tgl_sdt']);
            $data['tgl_sdt'] = $tmpTgl;


            # Create Condition
            $where = "`nopp`='".$data['nopp']."'";
            $where .= " and `kodebarang`='".$data['kd_brg']."'";

            # Create Query
            $query = "delete from `".$dbname."`.`log_prapodt` where ".$where;
            
			# Delete
            try{
				$owlPDO->exec($query); 
			}catch(PDOException $e){
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				die(); 
			}
        break;
        
		default :
        break;
    }
}

function createTabDetail($id,$data) {
	global $dbname;
	global $key;
	global $owlPDO;
    
	$table = "<b>".$_SESSION['lang']['nopp']."</b> : ".makeElement("detail_kode",'text',$id,array('disabled'=>'disabled','style'=>'width:150px'));
    $table .= "<table id='ppDetailTable'>";
    
	# Header
    $table .= "<thead>";
    $table .= "<tr>";
    $table .= "<td>".$_SESSION['lang']['kodebarang']."</td>";
    $table .= "<td>".$_SESSION['lang']['namabarang']."</td>";
    $table .= "<td>".$_SESSION['lang']['satuan']."</td>";
    $table .= "<td style='display:none'>".$_SESSION['lang']['kodeanggaran']."</td>";
    $table .= "<td>".$_SESSION['lang']['kodevhc']."</td>";
    $table .= "<td>".$_SESSION['lang']['jmlhDiminta']."</td>";
    $table .= "<td>".$_SESSION['lang']['tanggalSdt']."</td>";
    $table .= "<td>".$_SESSION['lang']['keterangan']."</td>";
//    $table .= "<td>"."<a href=# onclick=addNewRow(detailBody,true)><img src='images\newfile.png'></a>"."</td>";
    $table .= "<td colspan=3>Action</td>";
    $table .= "</tr>";
    $table .= "</thead>";

    # Data
    $table .= "<tbody id='detailBody'>";

    $i=0;
	
    #======= Display Data =======
    if($data!=array()) {
        foreach($data as $key=>$row) {
			$ql="select * from ".$dbname.".`log_5masterbarang` where `kodebarang`='".$row['kodebarang']."'"; //echo $ql;
			$qry=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
			$qry->setFetchMode(PDO::FETCH_ASSOC);
			$res=$qry->fetch();
			
            $tmpTgl = tanggalnormal($row['tgl_sdt']);
            $row['tgl_sdt'] = $tmpTgl;
            $table .= "<tr id='detail_tr_".$key."' class='rowcontent'>";
            $table .= "<td onclick=\"searchBrg('".$_SESSION['lang']['findBrg']."','<fieldset>Find<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findBrg()>Find</button></fieldset><div id=container></div><input type=hidden id=nomor name=nomor value=".$key.">',event)\";>".makeElement("kd_brg_".$key."",'txt',$row['kodebarang'],array('style'=>'width:120px','disabled'=>'disabled','class=myinputtext'))."<input type=hidden id=oldKdbrg_".$key." name=oldKdbrg_".$key." value='".$row['kodebarang']."'></td>";
            $table .= "<td onclick=\"searchBrg('".$_SESSION['lang']['findBrg']."','<fieldset>Find<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findBrg()>Find</button></fieldset><div id=container></div><input type=hidden id=nomor name=nomor value=".$key.">',event)\";>".makeElement("nm_brg_".$key."",'txt',$res['namabarang'],array('style'=>'width:120px','disabled'=>'disabled','class=myinputtext'))."<img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' class=resicon title=".$_SESSION['lang']['find']." onclick=\"searchBrg('".$_SESSION['lang']['findBrg']."','<fieldset>Find<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findBrg()>Find</button></fieldset><div id=container></div><input type=hidden id=nomor name=nomor value=".$key.">',event)\";></td>";
			
            $table .= "<td onclick=\"searchBrg('".$_SESSION['lang']['findBrg']."','<fieldset>Find<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findBrg()>Find</button></fieldset><div id=container></div><input type=hidden id=nomor name=nomor value=".$key.">',event)\";>".makeElement("sat_".$key."",'txt',$res['satuan'],array('style'=>'width:70px','disabled'=>'disabled','class=myinputtext'))."</td>";
            
            $table .= "<td style='display:none'>".makeElement("kd_angrn_".$key."",'txt','',array('style'=>'width:70px','disabled'=>'disabled','class=myinputtext'))."<img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' class=resicon title=".$_SESSION['lang']['find']." onclick=\"searchAngrn('".$_SESSION['lang']['findAngrn']."','<fieldset>Find<input type=text class=myinputtext id=no_angrn><button class=mybutton onclick=findAngrn()>Find</button></fieldset><div id=container></div><input type=hidden id=nomor name=nomor value=".$key.">',event)\";></td>";
            $table .= "<td>".makeElement("kd_vhc_".$key."",'txt',$row['kodevhc'],array('style'=>'width:70px','disabled'=>'disabled','class=myinputtext'))."<img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' 'disabled=disabled' class='resicon' title=".$_SESSION['lang']['find']." onclick=\"searchVhc('".$_SESSION['lang']['findvhc']."','<fieldset>Find<input type=text class=myinputtext id=no_vhc><button class=mybutton onclick=findVhc()>Find</button></fieldset><div id=container></div><input type=hidden id=nomor name=nomor value=".$key.">','".$key."',event)\";></td>";
            $table .= "<td>".makeElement("jmlhDiminta_".$key."",'textnum',$row['jumlah'],array('style'=>'width:70px','onkeypress'=>'return angka_doang(event)','class=myinputtext'))."</td>";	
            $table .= "<td>".makeElement("tgl_sdt_".$key."",'txt',$row['tgl_sdt'],array('style'=>'width:70px','onkeypress'=>'return tanpa_kutip(event)','onmousemove'=>'setCalendar(this.id)','readonly'=>'readonly','class=myinputtext'))."</td>";
            $table .= "<td>".makeElement("ket_".$key."",'txt',$row['keterangan'],array('style'=>'width:130px','class=myinputtext','onkeypress'=>'return tanpa_kutip(event)'))."</td>";
            $table .= "<td><img id='detail_edit_".$key."' title='Edit' class=zImgBtn onclick=\"editDetail('".$key."')\" src='images/save.png'/>";
            $table .= "&nbsp;<img id='detail_delete_".$key."' title='Hapus' class=zImgBtn onclick=\"deleteDetail('".$key."')\" src='images/delete_32.png'/></td>";
			$table .= "</tr>";
            $i = $key;
        }
		$i++;
    }

    #======= New Row ===========
    $table .= "<tr id='detail_tr_".$i."' class='rowcontent'>";
	$table .= "<td>".makeElement("kd_brg_".$i."",'txt','',array('style'=>'width:120px','disabled'=>'disabled','class=myinputtext'))."</td>";
	$table .= "<td>".makeElement("nm_brg_".$i."",'txt','',array('style'=>'width:120px','disabled'=>'disabled','class=myinputtext'))."<img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' class=resicon title=".$_SESSION['lang']['find']." onclick=\"searchBrg('".$_SESSION['lang']['findBrg']."','<fieldset>Find<input type=text class=myinputtext id=no_brg><button class=mybutton onclick=findBrg()>Find</button></fieldset><input type=hidden id=nomor name=nomor value=".$i."><div id=container></div>',event)\";><input type=hidden id=oldKdbrg_".$i." name=oldKdbrg_".$i.">"."</td>";
	$table .= "<td>".makeElement("sat_".$i."",'txt','',array('style'=>'width:70px','disabled'=>'disabled','class=myinputtext'))."</td>";
	$table .= "<td style='display:none'>".makeElement("kd_angrn_".$i."",'txt','',array('style'=>'width:70px','disabled'=>'disabled','class=myinputtext'))."<img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' class=resicon title=".$_SESSION['lang']['find']." onclick=\"searchAngrn('".$_SESSION['lang']['findAngrn']."','<fieldset>Find<input type=text class=myinputtext id=no_angrn><button class=mybutton onclick=findAngrn()>Find</button></fieldset><input type=hidden id=nomor name=nomor value=".$i."><div id=container></div>',event)\";></td>";
	$table .= "<td>".makeElement("kd_vhc_".$i."",'txt','',array('style'=>'width:70px','disabled'=>'disabled','class=myinputtext'))."<img src='images/onebit_02.png' style='position:relative;top:3px;left:3px;' class=resicon title=".$_SESSION['lang']['find']." onclick=\"searchVhc('".$_SESSION['lang']['findvhc']."','<fieldset>Find<input type=text class=myinputtext id=no_vhc><button class=mybutton onclick=findVhc()>Find</button></fieldset><input type=hidden id=nomor name=nomor value=".$i."><div id=container></div>','".$i."',event)\";></td>";
   
	$table .= "<td>".makeElement("jmlhDiminta_".$i."",'textnum','',array('style'=>'width:70px','onkeypress'=>'return angka_doang(event)','class=myinputtext'))."</td>";	
	$table .= "<td>".makeElement("tgl_sdt_".$i."",'txt','',array('style'=>'width:70px','onkeypress'=>'return tanpa_kutip(event)','onmousemove'=>'setCalendar(this.id)','readonly'=>'readonly','class=myinputtext'))."</td>";
	$table .= "<td>".makeElement("ket_".$i."",'txt','',array('style'=>'width:130px','class=myinputtext','onkeypress'=>'return tanpa_kutip(event)'))."</td>";
	$table .= makeElement("nopp_".$i."",'hidden',$id,array('style'=>'width:70px','onkeypress'=>'return tanpa_kutip(event)'))."</td>";


    # Add, Container Delete
    $table .= "<td><img id='detail_add_".$i."' title='Simpan' class=zImgBtn onclick=\"addDetail('".$i."')\" src='images/save.png'/>";
    $table .= "&nbsp;<img id='detail_delete_".$i."' /></td>";
    $table .= "</tr>";

    $table .= "</tbody>";
    $table .= "</table>";
    echo $table;
}
?>