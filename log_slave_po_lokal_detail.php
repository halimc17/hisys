<?php
session_start();
require_once('master_validation.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

if($_POST['proses']=='createTable')
{
	# Get Data untuk detail PO
    $rnopp=$_POST['nopp'];
	//print_r($rnopp);
    $baris=$_POST['baris'];
    $kdbrg=$_POST['kdbrg'];
    $bara=0;
	$where=$where2="";
    foreach($rnopp as $row =>$Rslt)
    {
        for($a=0;$a<$row;$a++) {
            for($b=0;$b<$baris;$b++) {
                if($a!=$b) { 
                    if(isset($kdbrg[$a]) and isset($kdbrg[$b]) and $kdbrg[$a]==$kdbrg[$b]) {
						$cek+=1;
						$cekBrg2=$kdbrg[$a];
                    }
                }  
            }
        }
		if($row==0)
		{
			$where.=" nopp='".$Rslt."'";
			$where2.=" kodebarang='".$kdbrg[$row]."'";			
		}
		else
		{
			$where.=" or nopp='".$Rslt."'";
			$where2.=" or kodebarang='".$kdbrg[$row]."'";
		}   
    }
   
    
          
    //$query="select * from ".$dbname.".log_prapodt where (".$where.") ";
    $query="select * from ".$dbname.".log_prapodt where (".$where.") and (".$where2.")";
    $data = fetchData($query);
	
	//generate nopo
        $rnopp=$_POST['nopp'];
        $tgl=  date('Ymd');
        $bln = substr($tgl,4,2);
        $thn = substr($tgl,0,4);

        $where="";
        $where2="";
        foreach($rnopp as $row =>$Rslt)
        {
            $kdbrg=$_POST['kdbrg'];
            if($row==0)
            {
                $where.=" nopp='".$Rslt."'";
                $where2.=" kodebarang=$kdbrg[$row]";
            }
            else
            {
                $where.=" or nopp='".$Rslt."'";
                $where2.=" or kodebarang=$kdbrg[$row]";
            }
        }
        $sql="select * from ".$dbname.".log_prapodt where ($where) and ($where2)";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);

        $cond="";
        $i=0;
        while($res=$query->fetch())
        {

            $nopp=substr($res['nopp'],15,4);
             if($i==0)
            {
               // $cond.=" kodeorganisasi='".$nopp."'";
                 $cond.=" nopp='".$res['nopp']."'";
            }
            else
            {
                $cond.=" or nopp='".$res['nopp']."'";
            }
            $i++;
            $i++;

            //echo $nopp."#";
        }
        $sql2="select distinct kodeorg from ".$dbname.".log_prapoht where ($cond)";// exit("Error".$sql2);//echo $sql2;
		$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_ASSOC);
        $res2=$query2->fetch();
        $kd_induk=$res2['kodeorg'];
        $nopo="/".date('Y')."/PO/".$nopp."/".$kd_induk; 
        $ql="select `nopo` from ".$dbname.".`log_poht` where nopo like '%".$nopo."%' order by `nopo` desc limit 0,1";
		$qr=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
		$qr->setFetchMode(PDO::FETCH_OBJ);
        $rp=$qr->fetch();
        $awal=substr($rp->nopo,0,3);
        $awal=intval($awal);
        $cekbln=substr($rp->nopo,4,2);
        $cekthn=substr($rp->nopo,7,4);

        if(($bln!=$cekbln)&&($thn!=$cekthn))
        {
        //echo $awal; exit();
                $awal=1;
        }
        else
        {
                $awal++;
        }
        $counter=addZero($awal,3);
        $nopo=$counter."/".$bln."/".$thn."/PO/".$nopp."/".$kd_induk;
                $tglSkrng=date("Y-m-d");
        $sIns="insert into ".$dbname.".log_poht (nopo,tanggal,kodeorg,purchaser,lokalpusat,statuspo,tgledit,kurs,matauang) 
               values ('".$nopo."','".$tglSkrng."','".$res2['kodeorg']."','".$_SESSION['standard']['userid']."','0','2','".$tglSkrng."','1','IDR')";
		try{
			$owlPDO->exec($sIns); 
			foreach($_POST['kdbrg'] as $row =>$isi)
            {
				$sql="insert into ".$dbname.".log_podt (`nopo`,`kodebarang`,`nopp`)
                values ('".$nopo."','".$isi."','".$_POST['nopp'][$row]."')";
				try{
					$owlPDO->exec($sql); 
					$sUpdate="update ".$dbname.".log_prapodt set create_po=1 where nopp='".$_POST['nopp'][$row]."' and kodebarang='".$isi."'";
					try{
						$owlPDO->exec($sUpdate); 
					}catch(PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						die(); 
					}
				}catch(PDOException $e){
					print " Gagal  !: " . $e->getMessage() . "\n"; 
					die(); 
				}
            }
            echo $nopo."###";
            createTabDetail($Rslt,$data);
		}catch(PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n"; 
			die(); 
		}
}

if($_POST['proses']=='edit_po')
{
	$query="select * from ".$dbname.".log_podt where nopo='".$_POST['nopo']."'"; //echo $query;exit();
	$data = fetchData($query);
 	createTabEditDetail($_POST['nopo'],$data);
}
if($_POST['proses']=='listPp')
{
    $kode_pt=$_POST['kodept'];
    $user_id=$_POST['id_user'];
    if($user_id!=$_SESSION['standard']['userid']);
    {
        $user_id=$_SESSION['standard']['userid'];
    }
  
    if($_SESSION['empl']['kodejabatan']=='5')
    {
	$sql2="select * from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where (kodept='".$_POST['kodept']."' and 
        lokalpusat='1' and status!='3') and (selisih>0 or selisih is null) order by tgl_sdt";
    }
    else
    {
        $sql2="select * from  ".$dbname.".log_sudahpo_vsrealisasi_vw  where (kodept='".$_POST['kodept']."' and 
               purchaser='".$user_id."' and lokalpusat='1' and status!='3') and (selisih>0 or selisih is null)
               order by tgl_sdt";
    }
	$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
	$query2->setFetchMode(PDO::FETCH_OBJ);
	$num_rows2=owlBaris($query2);
	
	$no=0;
	while($res2=$query2->fetch())
	{
		$no+=1;
		$sbrg="select * from ".$dbname.".log_5masterbarang where kodebarang='".$res2->kodebarang."'";
		$qbrg=$owlPDO->query($sbrg) or die(print " Gagal: ".PDOException::getMessage());
		$qbrg->setFetchMode(PDO::FETCH_OBJ);
		$rbrg=$qbrg->fetch();
		
		$strSat="select satuanpp,tglAlokasi from ".$dbname.".log_prapodt where nopp='".$res2->nopp."' and kodebarang='".$res2->kodebarang."'";
		$qrySat=$owlPDO->query($strSat) or die(print " Gagal: ".PDOException::getMessage());
		$qrySat->setFetchMode(PDO::FETCH_OBJ);
		$resSat=$qrySat->fetch();

		$sJmlhPsn="select sum(jumlahpesan) as jmlhPesan from ".$dbname.".log_podt where nopp='".$res2->nopp."' and kodebarang='".$res2->kodebarang."'";
		$qJmlhPsn=$owlPDO->query($sJmlhPsn) or die(print " Gagal: ".PDOException::getMessage());
		$qJmlhPsn->setFetchMode(PDO::FETCH_ASSOC);
		$rJmlhPsn=$qJmlhPsn->fetch();

		echo"
		<tr class=rowcontent id=tr_".$no.">
			   <td align=center onclick=\"checkIt(".$no.",".$num_rows2.",'".$res2->nopp."')\" >".$no."</td>
			   <td onclick=\"checkIt(".$no.",".$num_rows2.",'".$res2->nopp."')\" >".$resSat->tglAlokasi."</td>
			   <td id=\"nopp_x".$no."\" onclick=\"checkIt(".$no.",".$num_rows2.",'".$res2->nopp."')\" >".$res2->nopp."&nbsp;<input type='hidden' id='hiddennopp".$no."' value=".$res2->nopp."></td>
			   <td id=kdbrg_".$no.">".$rbrg->kodebarang."</td>
			   <td>".$rbrg->namabarang."</td>
			   <td>".$resSat->satuanpp."</td>
			   <td align=right>".number_format($res2->realisasi,2)."</td>
				<td align=center>".tanggalnormal($res2->tgl_sdt)."</td>";
		if(($res2->selisih=='')||is_null($res2->selisih)||$res2->selisih==0){
			echo "<td align=right>".number_format($res2->realisasi,2)."</td>";
		} elseif($res2->selisih!=$res2->realisasi) {
			$blm_pesan=$res2->selisih;
			echo "<td align=right>".number_format($blm_pesan,2)."</td>";
		} else {
			echo "<td align=right>".number_format($res2->selisih,2)."</td>";
		}
		if(($res2->jlpesan=='')||(is_null($res2->jlpesan)))
		{$jlpesan=0;}
		else
		{$jlpesan=$rJmlhPsn['jmlhPesan'];}
		//$res2->jlpesan==''?0:$res2->jlpesan
		echo"<td  align=right>".number_format($jlpesan,2)."</td>";
		echo "<td align=center><input type=checkbox id=plh_pp_".$no." name=plh_pp_".$no." onclick=\"checkIt(".$no.",".$num_rows2.",'".$res2->nopp."')\" /></td>
		 </tr>";
    }
		echo"<tr><td colspan=11 align=center>
		<button name=process class=mybutton id=process onclick=process()>". $_SESSION['lang']['proses']."</button>
		<button name=cancel class=mybutton id=cancel onclick=displayList()>". $_SESSION['lang']['cancel']."</button>
		</td></tr>";
}
if($_POST['proses']=='detail_delete')
{
	$data = $_POST;
			
	# Create Condition
	$where = "`nopo`='".$data['nopo']."'";
	$where .= " and `kodebarang`='".$data['kd_brg']."'";
	$where .= " and `nopp`='".$data['nopp']."'";
	$sCekGdng="select distinct nopo from ".$dbname.".log_transaksi_vw where nopo='".$data['nopo']."' and kodebarang='".$data['kd_brg']."'";
	$qCekGdng=$owlPDO->query($sCekGdng) or die(print " Gagal: ".PDOException::getMessage());
	$rCekGdng=owlBaris($qCekGdng);
	if($rCekGdng>0)
	{
		exit("Error: Nopo : ".$data['nopo']." Sudah diterima di gudang tidak dapat di hapus");
	}
	
	# Create Query
	$query = "delete from `".$dbname."`.`log_podt` where ".$where;
	try{
		$owlPDO->exec($query); 
	}catch(PDOException $e){
		print " Gagal  !: " . $e->getMessage() . "\n"; 
		die(); 
	}
}
function createTabDetail($id,$data) {
	global $conn;
	global $dbname;
	global $owlPDO;
	
 //   $table .= "<table id='ppDetailTable'>";
    # Header
    $table = "<thead class=rowheader>";
    $table .= "<tr>";
    $table .= "<td align=center>".$_SESSION['lang']['nopp']."</td>";
    $table .= "<td align=center  width=50px>".$_SESSION['lang']['kodebarang']."</td>";
    $table .= "<td align=center>".$_SESSION['lang']['namabarang']."</td>";
    $table .= "<td align=center>".$_SESSION['lang']['spesifikasi']."</td>";
    $table .= "<td align=center>".$_SESSION['lang']['jmlh_brg_blm_po']."</td>";
    $table .= "<td align=center  width=50px>".$_SESSION['lang']['jmlhPesan']."</td>";
    $table .= "<td align=center>".$_SESSION['lang']['satuan']."</td>";
    $table .= "<td style='display:none'>".$_SESSION['lang']['ongkoskirim']."/Brg</td>";
    $table .= "<td align=center  width=100px>".$_SESSION['lang']['hargasatuan']."</td>";
    $table .= "<td align=center>".$_SESSION['lang']['subtotal']."</td>";
    $table .= "<td align=center>Action</td>";
    $table .= "</tr>";
    $table .= "</thead>";

    # Data
    $table .= "<tbody id='detailBody'>";

   // $i=0;

    #======= Display Data =======
    if($data!=array()) {
        foreach($data as $key=>$row) {
			//get satuan dan nama barang di log_5masterbarang
            $ql="select satuan,namabarang from ".$dbname.".`log_5masterbarang` where `kodebarang`='".$row['kodebarang']."'";
			$qry=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
			$qry->setFetchMode(PDO::FETCH_ASSOC);
            $res=$qry->fetch();
            
			
			//get satuan konversi di log_5stkonversi
          /*  $where=" kodebarang='".$row['kodebarang']."' and darisatuan='".$res['satuan']."'";
            $optSatuan=makeOption( $dbname,'log_5stkonversi','satuankonversi',$where,1);
			array_push($optSatuan,$res['satuan']);*/
			$sSat="select satuan from ".$dbname.".log_5masterbarang where kodebarang='".$row['kodebarang']."'";
			$qSat=$owlPDO->query($sSat) or die(print " Gagal: ".PDOException::getMessage());
			$qSat->setFetchMode(PDO::FETCH_ASSOC);
			$rSat=$qSat->fetch();
			$optSatuan="<option value=".$rSat['satuan'].">".$rSat['satuan']."</option>";
		  	$where=" kodebarang='".$row['kodebarang']."' and darisatuan='".$res['satuan']."'";
			
			$sSknv="select satuankonversi from ".$dbname.".log_5stkonversi where ".$where."";
			$qSknv=$owlPDO->query($sSknv) or die(print " Gagal: ".PDOException::getMessage());
			$qSknv->setFetchMode(PDO::FETCH_ASSOC);
			while($rSknv=$qSknv->fetch())
			{
				$optSatuan.="<option value=".$rSknv['satuankonversi'].">".$rSknv['satuankonversi']."</option>";
			}
			
			
			/*$columnw=array(1=>'IDR',0=>'USD');
	  		$optTest=makeOption('','',$columnw,'',3);*/
			$optTest=makeOption( $dbname,'setup_matauang','kode,kodeiso');
			
			//$optSatuan="<option value='".$res['satuan']."'>".$res['satuan']."</option>".$optSatuan;
			$sqjmlh="select selisih,jlpesan,realisasi from ".$dbname.".log_sudahpo_vsrealisasi_vw where nopp='".$row['nopp']."' and kodebarang='".$row['kodebarang']."'";
			$qujmlh=$owlPDO->query($sqjmlh) or die(print " Gagal: ".PDOException::getMessage());
			$qujmlh->setFetchMode(PDO::FETCH_ASSOC);
			$resjmlh=$qujmlh->fetch();
			if($resjmlh['selisih']==''){
			$row['realisasi']=$row['realisasi'];
			} elseif($resjmlh['selisih']!=$resjmlh['realisasi']) {
				$row['realisasi']=$resjmlh['selisih'];
			}
			
            $table .= "<tr id='detail_tr_".$key."' class='rowcontent'>";
            $table .= "<td id='dtNopp_".$key."'>".makeElement("rnopp_".$key."",'txt',$row['nopp'],
                array('style'=>'width:120px','disabled'=>'disabled'))."</td>";
            $table .= "<td id='dtKdbrg_".$key."'>".makeElement("rkdbrg_".$key."",'txt',$row['kodebarang'],
                array('style'=>'width:60px','disabled'=>'disabled'))."</td>";
            $table .= "<td>".makeElement("nm_brg_".$key."",'txt',$res['namabarang'],
                array('style'=>'width:300px','disabled'=>'disabled'))."</td>";
	    $table .="<td><input class=myinputtext style=width:200px id=\"spek_brg_".$key."\" cols=\"25\"></td>";

            $table .= "<td>".makeElement("realisasi_".$key."",'txt',$row['realisasi'],
                array('style'=>'width:70px','onkeypress'=>'return angka_doang(event)','disabled'=>'disabled','class=myinputtext'))."</td>";				
            $table .= "<td>".makeElement("jmlhDiminta_".$key."",'textnum','',
                array('style'=>'width:70px','onkeypress'=>'return angka_doang(event)','onblur'=>"display_number('".$key."')",'onkeyup'=>"calculate('".$key."')"))."</td>";
            $table.="<td><select id=sat_".$key." style='width:70px'><option value=".$row['satuanpp'].">".$row['satuanpp']."</option></td>";
            $table .= "<td style='display:none'>".makeElement("ongkos_angkut_".$key."",'textnum','',
			    array('style'=>'width:80px','disabled'=>'disabled'))."</td>";            
            $table .= "<td>".makeElement("harga_satuan_".$key."",'textnum','',
                array('style'=>'width:100px','onkeypress'=>'return angka_doang(event)',
				'onkeyup'=>"calculate('".$key."')",'onblur'=>"periksa_isi(this)",'onblur'=>"display_number('".$key."')",'onfocus'=>"normal_number('".$key."')"))."<br>".makeElement("hidden_harga_satuan_".$key."",'hidden',number_format($row['hargasbldiskon'],2,'.',','),
                            array('style'=>'width:100px;style:none'))."</td>";
            $table .= "<td>".makeElement("total_".$key."",'textnum','',
                array('style'=>'width:100px','onkeypress'=>'return angka_doang(event)','disabled'=>'disabled'))."<input type=hidden id=subTotal_".$key." /></td>";
          //  $table .= "<td><img id='detail_save_".$key."' title='Save' class=zImgBtn onclick=\"editDetail('".$key."')\" src='images/save.png'/>";
            $table .= "<td align=center><img id='detail_delete_".$key."' title='Hapus' class=zImgBtn onclick=\"deleteDetail('".$key."')\" src='images/delete_32.png'/></td>";
		 	$table .= "</tr>";
            $i = $key;
        }
        $i++;
    }
	
            $table.="<tr><td>&nbsp;</td>
                <td colspan=7 align=right>". $_SESSION['lang']['subtotal']."</td>
            <td><input type=text id=total_harga_po name=total_harga_po disabled  class=myinputtextnumber  style=width:100px /></td>
        </tr>
       <tr>
            <td >&nbsp;</td>
            <td colspan=7 align=right>".$_SESSION['lang']['diskon']."</td>
            <td><input type=text  id=angDiskon name=angDiskon class=myinputtextnumber style=width:100px onkeyup=calculate_angDiskon()  onkeypress=return angka_doang(event) onblur=\"getZero()\"  /></td>
        </tr>
		    <tr>
            <td >&nbsp;</td>
            <td colspan=7 align=right>Diskon (%)</td>
            <td><input type=text  id=diskon name=diskon class=myinputtextnumber style=width:100px onkeyup=calculate_diskon() maxlength=5 onkeypress=\"return angka_doang(event)\" onblur=\"getZero()\" /> </td>
        </tr>
		<tr>
            <td>&nbsp;</td>
            <td style='vertical-align:top;' colspan=7 align=right>PBBKB</td>
            <td><input type=text id='pbbkb' name='pbbkb' value=0 class='myinputtextnumber' style='width:100px' onkeyup='calculatePbbkb()' onkeypress='return angka_doang(event)' onblur=\"getZero()\" /></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td style='vertical-align:top;' colspan=7 align=right>PPn (%)</td>
            <td><input type=text id=ppN name=ppN  class=myinputtextnumber style=width:100px onkeyup=\"calculatePpn()\"  maxlength=5  onkeypress=\"return angka_doang(event)\" onblur=\"getZero()\" /><input type=hidden id=ppn name=ppn class=myinputtext onkeypress=return angka_doang(event) style=width:100px onblur=getZero() /><br><span id=hslPPn> </span> </td>
			<td style='vertical-align:top;'><input type=checkbox title='Check=>Include PPn \n Uncheck=>Excelude Ppn' id=chkPpn name=chkPpn onclick=\"checkChkPpn()\" /></td>
        </tr>
		<tr>
            <td>&nbsp;</td>
            <td style='vertical-align:top;' colspan=7 align=right>PPh (%)</td>
            <td><input type=text id=ppH name=ppH  class='myinputtextnumber' style='width:100px' onkeyup=\"calculatePph()\"  maxlength='5'  onkeypress='return angka_doang(event)' onblur=\"getZero()\" />  <input type='hidden' id='pph' name='pph' class='myinputtext' onkeypress='return angka_doang(event)' style='width:100px' onblur=\"getZero()\" /><br><span id='hslPPh'> </span> </td>
        </tr>
		<tr>
            <td>&nbsp;</td>
            <td style='vertical-align:top;' colspan=7 align=right>Add Cost</td>
            <td><input type=text id='addcost' onkeyup=grandTotal(); value=0 name='addcost'  class='myinputtextnumber' style='width:100px'  maxlength='12'  onblur=\"getZero()\" /></td>
        </tr>
         <tr>
            <td>&nbsp;</td>
            <td colspan=7 align=right>".$_SESSION['lang']['grnd_total']."</td>
            <td><input type=text id=grand_total name=grand_total disabled  class=myinputtextnumber style=width:100px  /></td>
        </tr><input type=hidden id=sub_total name=sub_total ><input type=hidden id=nilai_diskon name=nilai_diskon  />";
    $table .= "</tbody>";
    $table .= "</table> <br />";

    echo $table;
}

function createTabEditDetail($id,$data) {
    global $conn;
	global $dbname;
	global $owlPDO;
	
  //  $table .= "<table id='ppDetailTable'>";
    # Header
    $table = "<thead>";
    $table .= "<tr class=rowheader>";
    $table .= "<td align=center>".$_SESSION['lang']['nopp']."</td>";
    $table .= "<td align=center width=50px>".$_SESSION['lang']['kodebarang']."</td>";
    $table .= "<td align=center>".$_SESSION['lang']['namabarang']."</td>";
    $table .= "<td align=center>".$_SESSION['lang']['spesifikasi']."</td>";
    $table .= "<td align=center>".$_SESSION['lang']['jmlh_brg_blm_po']."</td>";
    $table .= "<td align=center width=50px>".$_SESSION['lang']['jmlhPesan']."</td>";
    $table .= "<td align=center>".$_SESSION['lang']['satuan']."</td>";
    $table .= "<td style='display:none'>".$_SESSION['lang']['ongkoskirim']."/Brg</td>";
    $table .= "<td align=center>".$_SESSION['lang']['hargasatuan']."</td>";
    $table .= "<td align=center width=100px>".$_SESSION['lang']['subtotal']."</td>";
    $table .= "<td align=center>Action</td>";
    $table .= "</tr>";
    $table .= "</thead>";

    # Data
    $table .= "<tbody id='detailBody'>";

   $i=0;
   $jmlhPesan=0;
    #======= Display Data =======
    if($data!=array()) {
        foreach($data as $key=>$row) {
           //get satuan dan nama barang di log_5masterbarang
            $ql="select satuan,namabarang from ".$dbname.".`log_5masterbarang` where `kodebarang`='".$row['kodebarang']."'";
			$qry=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
			$qry->setFetchMode(PDO::FETCH_ASSOC);
            $res=$qry->fetch();
            
			
			//get satuan konversi di log_5stkonversi
			$sSat="select satuan from ".$dbname.".log_5masterbarang where kodebarang='".$row['kodebarang']."'";
			$qSat=$owlPDO->query($sSat) or die(print " Gagal: ".PDOException::getMessage());
			$qSat->setFetchMode(PDO::FETCH_ASSOC);
			$rSat=$qSat->fetch();
			$optSatuan="<option value=".$rSat['satuan'].">".$rSat['satuan']."</option>";
		  	$where=" kodebarang='".$row['kodebarang']."' and darisatuan='".$res['satuan']."'";
			
			$sSknv="select satuankonversi from ".$dbname.".log_5stkonversi where ".$where."";
			$qSknv=$owlPDO->query($sSknv) or die(print " Gagal: ".PDOException::getMessage());
			$qSknv->setFetchMode(PDO::FETCH_ASSOC);
			while($rSknv=$qSknv->fetch())
			{
				$optSatuan.="<option value=".$rSknv['satuankonversi'].">".$rSknv['satuankonversi']."</option>";
			}
			

			$optTest=makeOption( $dbname,'setup_matauang','kode,kodeiso');
			$sqpp="select * from  ".$dbname.".log_sudahpo_vsrealisasi_vw where nopp='".$row['nopp']."' and kodebarang='".$row['kodebarang']."'";
			$qpp=$owlPDO->query($sqpp) or die(print " Gagal: ".PDOException::getMessage());
			$qpp->setFetchMode(PDO::FETCH_ASSOC);
			$rpp=$qpp->fetch();
     		        $sub_tot=($row['jumlahpesan']*$row['hargasbldiskon'])+$row['ongkangkut'];
                        $sub_tot_nor=$row['jumlahpesan']*$row['hargasbldiskon'];
			
			
			$sjmlh="select sum(jumlahpesan) as jumlahPesan from ".$dbname.".log_podt where kodebarang='".$row['kodebarang']."' and nopp='".$row['nopp']."'";
			$qjmlh=$owlPDO->query($sjmlh) or die(print " Gagal: ".PDOException::getMessage());
			$qjmlh->setFetchMode(PDO::FETCH_ASSOC);
			$resjmlh=$qjmlh->fetch();
			
			$sEdit="select jumlahpesan from ".$dbname.".log_podt where nopo='".$id."' and kodebarang='".$row['kodebarang']."' and nopp='".$row['nopp']."'";
			$qEdit=$owlPDO->query($sEdit) or die(print " Gagal: ".PDOException::getMessage());
			$qEdit->setFetchMode(PDO::FETCH_ASSOC);
			$rEdit=$qEdit->fetch();
			$tmpil=($rpp['realisasi']-$resjmlh['jumlahPesan'])+$rEdit['jumlahpesan'];
			//$r=$rpp['realisasi']-$resjmlh['jumlahPesan'];
			//echo "warning:".$tmpil."____".$r."___".$rEdit['jumlahpesan'];exit();
                        if($row['harganormal']==0)
                        {
                           $row['harganormal']=$row['hargasatuan'];
                        }
                        $sMtUang="select matauang from ".$dbname.".log_poht where nopo = '".$id."' ";
						$qMtUang=$owlPDO->query($sMtUang) or die(print " Gagal: ".PDOException::getMessage());
						$qMtUang->setFetchMode(PDO::FETCH_ASSOC);
                        $rMtUang=$qMtUang->fetch();
			
			$strSat="select satuanpp from ".$dbname.".log_prapodt where nopp='".$row['nopp']."' and kodebarang='".$row['kodebarang']."'";
			$qrySat=$owlPDO->query($strSat) or die(print " Gagal: ".PDOException::getMessage());
			$qrySat->setFetchMode(PDO::FETCH_OBJ);
			$resSat=$qrySat->fetch();			
						
            $table .= "<tr id='detail_tr_".$key."' class='rowcontent'>";
            $table .= "<td id='dtNopp_".$key."'>".makeElement("rnopp_".$key."",'txt',$row['nopp'],
                array('style'=>'width:120px','disabled'=>'disabled'))."</td>";
            $table .= "<td id='dtKdbrg_".$key."'>".makeElement("rkdbrg_".$key."",'txt',$row['kodebarang'],
                array('style'=>'width:60px','disabled'=>'disabled'))."</td>";
            $table .= "<td>".makeElement("nm_brg_".$key."",'txt',$res['namabarang'],
                array('style'=>'width:300px','disabled'=>'disabled'))."</td>";
	    $table.="<td><input class=myinputtext style=width:200px id=\"spek_brg_".$key."\" cols=\"25\" value='".$row['catatan']."'></td>";	
            $table .= "<td>".makeElement("realisasi_".$key."",'txt',$tmpil,
                array('style'=>'width:70px','onkeypress'=>'return angka_doang(event)','disabled'=>'disabled','class=myinputtext'))."</td>";
            $table .= "<td>".makeElement("jmlhDiminta_".$key."",'textnum',$row['jumlahpesan'],
                array('style'=>'width:70px','onkeypress'=>'return angka_doang(event)','onblur'=>"display_number('".$key."')",'onkeyup'=>"calculate('".$key."')"))."</td>";
            $table.="<td><select id=sat_".$key." style='width:70px'><option value=".$resSat->satuanpp.">".$resSat->satuanpp."</option></td>";
            
            if($rMtUang['matauang']=='IDR'){
                $table .= "<td style='display:none'>".makeElement("ongkos_angkut_".$key."",'textnum',number_format($row['ongkangkut'],2,'.',','),
			    array('style'=>'width:80px','disabled'=>'disabled'))."</td>";
                $table .= "<td>".makeElement("harga_satuan_".$key."",'textnum',number_format($row['hargasbldiskon'],2,'.',','),
                            array('style'=>'width:100px','onkeypress'=>'return angka_doang(event)','onkeyup'=>"calculate('".$key."')",'onblur'=>"periksa_isi(this)",'onblur'=>"display_number('".$key."')",'onfocus'=>"normal_number('".$key."')"))."<br>".makeElement("hidden_harga_satuan_".$key."",'hidden',number_format($row['hargasbldiskon'],2,'.',','),
                            array('style'=>'width:100px;style:none'))."</td>";
                $table .= "<td>".makeElement("total_".$key."",'textnum',number_format($sub_tot,2,'.',','),
                            array('style'=>'width:100px','onkeypress'=>'return angka_doang(event)','disabled'=>'disabled'))."<input type=hidden id=subTotal_".$key." value=".$sub_tot_nor." /></td>";
            }
            else{
                $table .= "<td style='display:none'>".makeElement("ongkos_angkut_".$key."",'textnum',$row['ongkangkut'],
			    array('style'=>'width:80px','disabled'=>'disabled'))."</td>";
                $table .= "<td>".makeElement("harga_satuan_".$key."",'textnum',$row['hargasbldiskon'],
                            array('style'=>'width:100px','onkeypress'=>'return angka_doang(event)','onkeyup'=>"calculate('".$key."')",'onblur'=>"periksa_isi(this)",'onblur'=>"display_number('".$key."')",'onfocus'=>"normal_number('".$key."')"))."<br>".makeElement("hidden_harga_satuan_".$key."",'hidden',number_format($row['hargasbldiskon'],2,'.',','),
                            array('style'=>'width:100px;display:none'))."</td>";
                $table .= "<td>".makeElement("total_".$key."",'textnum',$sub_tot,
                            array('style'=>'width:100px','onkeypress'=>'return angka_doang(event)','disabled'=>'disabled'))."<input type=hidden id=subTotal_".$key." value=".$sub_tot_nor." /></td>";
            }
            
            
            $table .= "<td align=center><img id='detail_delete_".$key."' title='Hapus' class=zImgBtn onclick=\"deleteDetail('".$key."')\" src='images/delete_32.png'/></td>";
            $table .= "</tr>";
            $i = $key;
        }
        $i++;
    }
	
            $table.="<tr><td>&nbsp;</td>
            <td colspan=7 align=right>". $_SESSION['lang']['subtotal']."</td>
            <td><input type=text id=total_harga_po name=total_harga_po disabled  class=myinputtextnumber  style=width:100px /></td>
        </tr>
        <tr>
            <td >&nbsp;</td>
            <td colspan=7 align=right>".$_SESSION['lang']['diskon']."</td>
            <td><input type='text'  id='angDiskon' name='angDiskon' class='myinputtextnumber' style='width:100px' onkeyup='calculate_angDiskon()' onkeypress='return angka_doang(event)' onblur=\"getZero()\" /></td>
        </tr>
		    <tr>
            <td >&nbsp;</td>
            <td colspan=7 align=right>Diskon (%)</td>
            <td><input type='text'  id='diskon' name='diskon' class='myinputtextnumber' style='width:100px' onkeyup='calculate_diskon()' maxlength='5' onkeypress='return angka_doang(event)' onblur=\"getZero()\" /> </td>
        </tr>
		<tr>
            <td>&nbsp;</td>
            <td style='vertical-align:top;' colspan=7 align=right>PBBKB</td>
            <td><input type=text id='pbbkb' name='pbbkb' value=0 class='myinputtextnumber' style='width:100px' onkeyup='calculatePbbkb()' onkeypress='return angka_doang(event)' onblur=\"getZero()\" /></td>
        </tr>
		<tr>
            <td>&nbsp;</td>
            <td style='vertical-align:top;' colspan=7 align=right>PPn (%)</td>
            <td><input type=text id=ppN name=ppN  class=myinputtextnumber style=width:100px onkeyup=\"calculatePpn()\"  maxlength=5  onkeypress=\"return angka_doang(event)\" onblur=\"getZero()\" /><input type=hidden id=ppn name=ppn class=myinputtext onkeypress=return angka_doang(event) style=width:100px onblur=getZero() /><br><span id=hslPPn> </span> </td>
			<td style='vertical-align:top;'><input type=checkbox title='Check=>Include PPn \n Uncheck=>Excelude Ppn' id=chkPpn name=chkPpn onclick=\"checkChkPpn()\" /></td>
        </tr>
		<tr>
            <td>&nbsp;</td>
            <td style='vertical-align:top;' colspan=7 align=right>PPh (%)</td>
            <td><input type=text id=ppH name=ppH  class='myinputtextnumber' style='width:100px' onkeyup=\"calculatePph()\"  maxlength='5'  onkeypress='return angka_doang(event)' onblur=\"getZero()\" />  <input type='hidden' id='pph' name='pph' class='myinputtext' onkeypress='return angka_doang(event)' style='width:100px' onblur=\"getZero()\" /><br><span id='hslPPh'> </span> </td>
        </tr>
		<tr>
            <td>&nbsp;</td>
            <td style='vertical-align:top;' colspan=7 align=right>Add Cost</td>
            <td><input type=text id='addcost' onkeyup=grandTotal(); value=0 name='addcost'  class='myinputtextnumber' style='width:100px'  maxlength='12'  onblur=\"getZero()\" /></td>
        </tr>
         <tr>
            <td>&nbsp;</td>
            <td colspan=7 align=right>".$_SESSION['lang']['grnd_total']."</td>
            <td><input type=text id='grand_total' name='grand_total' disabled  class='myinputtextnumber' style=width:100px /></td>
        </tr><input type=hidden id='sub_total' name='sub_total' ><input type=hidden id='nilai_diskon' name='nilai_diskon'  />";
    $table .= "</tbody>";
 //   $table .= "</table> <br />";
	$sPoht="select tanggalkirim,lokasipengiriman,syaratbayar,uraian,purchaser from ".$dbname.".log_poht where nopo='".$id."' ";
	$qPoht=$owlPDO->query($sPoht) or die(print " Gagal: ".PDOException::getMessage());
	$qPoht->setFetchMode(PDO::FETCH_ASSOC);
	$rPoht=$qPoht->fetch();
	//echo"warning:".$sPoht;exit();
	
	
	$snmkary="select namakaryawan from ".$dbname.".datakaryawan where karyawanid='".$rPoht['purchaser']."'";
	$qnmkary=$owlPDO->query($snmkary) or die(print " Gagal: ".PDOException::getMessage());
	$qnmkary->setFetchMode(PDO::FETCH_ASSOC);
	$rnmkary=$qnmkary->fetch();
	
	echo $table."###".$rPoht['syaratbayar']."###".$rPoht['uraian']."###".$rPoht['uraian'];
	
    
}
?>