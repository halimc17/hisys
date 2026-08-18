<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');


$proses = checkPostGet('proses','');
$nmTable = checkPostGet('nmTable','');
$kode = checkPostGet('kode','');
$newkode = checkPostGet('newkode','');
$kdtimbangan = checkPostGet('kdtimbangan','');
$namatimbangan = checkPostGet('namatimbangan','');
$komoditi = checkPostGet('komoditi','');
$jenis = checkPostGet('jenis','');

if($nmTable=='mssipb'){
	if($proses!='uploadData2'){
		$proses="getDataSipb";
    }
}

switch($proses){
	case'preview':
		if($nmTable=='')
		{
			echo "Warning : Tipe harus dipilih.";
			exit();
		}
		
		if($nmTable=='Customer')
		{
			$arrCusErp = array();
			$str="select * from ".$dbname.".pmn_4customer";
			$res=fetchData($str);
			foreach($res as $key=>$val)
			{
				$arrCusErp[$val['kodecustomer']] = $val['namacustomer'];
			}
			
			$str="select * from ".$dbname.".temp_mastertimbangan where status='0' and tipe='".$nmTable."'";
			$res=fetchData($str);
			
			if(count($res)>0)
			{
				echo"<button class=mybutton onclick=uploadData('".count($res)."','".$nmTable."') id=btnUpload>".$_SESSION['lang']['startUpload']."</button>
				<div style='overflow:auto;height:350px;max-width:1220px'>
				<table class=sortable cellspacing=1 border=0>
					<thead>
					<tr class=rowheader>
						<td></td>
						<td colspan=2 style='text-align:center;font-weight:bold'>Timbangan</td>
						<td style='text-align:center;font-weight:bold' colspan=2>ERP</td>
					</tr>
					<tr class=rowheader>
						<td style='text-align:center'>No.</td>
						<td style='text-align:center'>".$_SESSION['lang']['kodecustomer']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['nama']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['kodecustomer']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['nama']."</td>
					</tr>
					</thead>
					<tbody id=ListData>";
					
				foreach($res as $key=>$val)
				{
					$optCusErp="<option value=''>".$_SESSION['lang']['new']."</option>";
					foreach($arrCusErp as $key2=>$val2)
					{
						if(strtolower($val2) == strtolower($val['nama']))
						{
							$optCusErp .= "<option value='".$key2."' selected>".$val2."</option>";
						}
						else
						{
							$optCusErp .= "<option value='".$key2."'>".$val2."</option>";
						}
					}
					$no+=1;
					echo"<tr class=rowcontent id=row_".$no." >
						<td >".$no."</td>
						<td id=kdtimbangan_".$no.">".$val['kode']."</td>
						<td id=tipeSup_".$no." style='display:none'></td>
						<td id=namatimbangan_".$no.">".$val['nama']."</td>
						<td>
							<label id='lblnewkodecust_".$no."'></label>
							<input type='text' id=newkodecust_".$no." placeholder='Automatic' maxlength=8 disabled>
						</td>
						<td>
							<select id=nmSupErp_".$no." onchange=\"showhidekodecust('".$no."')\">".$optCusErp."</select>
							<img id=nmSupErp_".$no." onclick=z.elSearch('nmSupErp_".$no."',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
							
							<input type='hidden' id=tipesynchronize_".$no." value='customer'>
							<input type='hidden' id=komoditi_".$no." value='".$val['komoditi']."'>
						</td>
					</tr>";
				}
			}
			else
			{
				echo" <table class=sortable cellspacing=1 border=0>
					<thead>
					<tr class=rowheader>
						<td></td>
						<td colspan=3 style='text-align:center;font-weight:bold'>Timbangan</td>
						<td style='text-align:center;font-weight:bold'>ERP</td>
					</tr>
					<tr class=rowheader>
						<td style='text-align:center'>No.</td>
						<td style='text-align:center'>".$_SESSION['lang']['kodesupplier']." / ".$_SESSION['lang']['transportir']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['tipe']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['nama']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['nama']."</td>
					</tr>
					</thead>
					<tbody>
					<tr class=rowcontent align=center>
						<td colspan=5>Data Not Found</td>
					</tr>";
			}
			echo"</tbody></table></div>";
		}
		else if($nmTable=='Supplier')
		{
			$arrSupErp = array();
			$arrTrpErp = array();
			$str="select * from ".$dbname.".log_5supplier";
			$res=fetchData($str);
			foreach($res as $key=>$val)
			{
				$arrTrpErp[$val['supplierid']] = $val['namasupplier'];					
				$arrSupErp[$val['supplierid']] = $val['namasupplier'];					
			}
			
			$str="select * from ".$dbname.".temp_mastertimbangan where status='0' and tipe='".$nmTable."'";
			$res=fetchData($str);
			
			if(count($res)>0)
			{
				echo"<button class=mybutton onclick=uploadData('".count($res)."','".$nmTable."') id=btnUpload>".$_SESSION['lang']['startUpload']."</button>
				<div style='overflow:auto;height:350px;max-width:1220px'>
				<table class=sortable cellspacing=1 border=0>
					<thead>
					<tr class=rowheader>
						<td></td>
						<td></td>
						<td colspan=2 style='text-align:center;font-weight:bold'>Timbangan</td>
						<td style='text-align:center;font-weight:bold' colspan=2>ERP</td>
					</tr>
					<tr class=rowheader>
						<td style='text-align:center'>No.</td>
						<td style='text-align:center'>".$_SESSION['lang']['tipe']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['kodecustomer']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['nama']."</td>
						<td style='text-align:center;display:none'>".$_SESSION['lang']['kodecustomer']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['nama']."</td>
					</tr>
					</thead>
					<tbody id=ListData>";
					
				foreach($res as $key=>$val)
				{
					$optSupErp="<option value=''></option>";
					foreach($arrSupErp as $key2=>$val2)
					{
						if(strtolower($val2) == strtolower($val['nama']))
						{
							$optSupErp .= "<option value='".$key2."' selected>".$val2."</option>";
						}
						else
						{
							$optSupErp .= "<option value='".$key2."'>".$val2."</option>";
						}
					}
					$optTrpErp="<option value=''></option>";
					foreach($arrTrpErp as $key2=>$val2)
					{
						if(strtolower($val2) == strtolower($val['nama']))
						{
							$optTrpErp .= "<option value='".$key2."' selected>".$val2."</option>";
						}
						else
						{
							$optTrpErp .= "<option value='".$key2."'>".$val2."</option>";
						}
					}
					$no+=1;
					echo"<tr class=rowcontent id=row_".$no." >
						<td >".$no."</td>
						<td id=jenis_".$no.">".$val['jenis']."</td>
						<td id=kdtimbangan_".$no.">".$val['kode']."</td>
						<td id=tipeSup_".$no." style='display:none'></td>
						<td id=namatimbangan_".$no.">".$val['nama']."</td>
						<td style='display:none'>
							<label id='lblnewkodecust_".$no."'></label>
							<input type='text' id=newkodecust_".$no." placeholder='Kode Customer' maxlength=8>
						</td>
						<td>
							<select id=nmSupErp_".$no." onchange=\"showhidekodecust('".$no."')\">".($val['jenis']=='TRANSPORTIR'?$optTrpErp:$optSupErp)."</select>
							<img id=nmSupErp_".$no." onclick=z.elSearch('nmSupErp_".$no."',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
							
							<input type='hidden' id=tipesynchronize_".$no." value='customer'>
							<input type='hidden' id=komoditi_".$no." value='40000003'>
						</td>
					</tr>";
				}
			}
			else
			{
				echo"<table class=sortable cellspacing=1 border=0>
					<thead>
					<tr class=rowheader>
						<td></td>
						<td></td>
						<td colspan=2 style='text-align:center;font-weight:bold'>Timbangan</td>
						<td style='text-align:center;font-weight:bold' colspan=2>ERP</td>
					</tr>
					<tr class=rowheader>
						<td style='text-align:center'>No.</td>
						<td style='text-align:center'>".$_SESSION['lang']['tipe']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['kodecustomer']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['nama']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['kodecustomer']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['nama']."</td>
					</tr>
					</thead>";
			}
			echo"</tbody></table></div>";
		}elseif($nmTable=='Transportir'){
			$arrSupErp = array();
			$arrTrpErp = array();
			$str="select * from ".$dbname.".log_5supplier";
			$res=fetchData($str);
			foreach($res as $key=>$val)
			{
				$arrTrpErp[$val['supplierid']] = $val['namasupplier'];					
				$arrSupErp[$val['supplierid']] = $val['namasupplier'];					
			}
			
			$str="select * from ".$dbname.".temp_mastertimbangan where status='0' and tipe='".$nmTable."'";
			$res=fetchData($str);
			
			if(count($res)>0)
			{
				echo"<button class=mybutton onclick=uploadData('".count($res)."','".$nmTable."') id=btnUpload>".$_SESSION['lang']['startUpload']."</button>
				<div style='overflow:auto;height:350px;max-width:1220px'>
				<table class=sortable cellspacing=1 border=0>
					<thead>
					<tr class=rowheader>
						<td></td>
						<td></td>
						<td colspan=2 style='text-align:center;font-weight:bold'>Timbangan</td>
						<td style='text-align:center;font-weight:bold' colspan=2>ERP</td>
					</tr>
					<tr class=rowheader>
						<td style='text-align:center'>No.</td>
						<td style='text-align:center'>".$_SESSION['lang']['tipe']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['kodecustomer']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['nama']."</td>
						<td style='text-align:center;display:none'>".$_SESSION['lang']['kodecustomer']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['nama']."</td>
					</tr>
					</thead>
					<tbody id=ListData>";
					
				foreach($res as $key=>$val)
				{
					$optSupErp="<option value=''></option>";
					foreach($arrSupErp as $key2=>$val2)
					{
						if(strtolower($val2) == strtolower($val['nama']))
						{
							$optSupErp .= "<option value='".$key2."' selected>".$val2."</option>";
						}
						else
						{
							$optSupErp .= "<option value='".$key2."'>".$val2."</option>";
						}
					}
					$optTrpErp="<option value=''></option>";
					foreach($arrTrpErp as $key2=>$val2)
					{
						if(strtolower($val2) == strtolower($val['nama']))
						{
							$optTrpErp .= "<option value='".$key2."' selected>".$val2."</option>";
						}
						else
						{
							$optTrpErp .= "<option value='".$key2."'>".$val2."</option>";
						}
					}
					$no+=1;
					echo"<tr class=rowcontent id=row_".$no." >
						<td >".$no."</td>
						<td id=jenis_".$no.">".$val['jenis']."</td>
						<td id=kdtimbangan_".$no.">".$val['kode']."</td>
						<td id=tipeSup_".$no." style='display:none'></td>
						<td id=namatimbangan_".$no.">".$val['nama']."</td>
						<td style='display:none'>
							<label id='lblnewkodecust_".$no."'></label>
							<input type='text' id=newkodecust_".$no." placeholder='Kode Customer' maxlength=8>
						</td>
						<td>
							<select id=nmSupErp_".$no." onchange=\"showhidekodecust('".$no."')\">".($val['jenis']=='TRANSPORTIR'?$optTrpErp:$optSupErp)."</select>
							<img id=nmSupErp_".$no." onclick=z.elSearch('nmSupErp_".$no."',event) class='zImgBtn' src='images/skyblue/zoom.png' style='position:relative;top:2px;'>
							
							<input type='hidden' id=tipesynchronize_".$no." value='customer'>
							<input type='hidden' id=komoditi_".$no." value='40000003'>
						</td>
					</tr>";
				}
			}
			else
			{
				echo"<table class=sortable cellspacing=1 border=0>
					<thead>
					<tr class=rowheader>
						<td></td>
						<td></td>
						<td colspan=2 style='text-align:center;font-weight:bold'>Timbangan</td>
						<td style='text-align:center;font-weight:bold' colspan=2>ERP</td>
					</tr>
					<tr class=rowheader>
						<td style='text-align:center'>No.</td>
						<td style='text-align:center'>".$_SESSION['lang']['tipe']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['kodecustomer']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['nama']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['kodecustomer']."</td>
						<td style='text-align:center'>".$_SESSION['lang']['nama']."</td>
					</tr>
					</thead>";
			}
			echo"</tbody></table></div>";
		}
		else
		{
			echo $_SESSION['lang']['datanotfound'];
		}
	break;
	
	case'uploadData':
		$stat = '1';
		if($nmTable=='Customer')
		{
			$expkomoditi = explode("####",$komoditi);
			
			if($kode=='')
			{
				$newkode = generateCustCode($namatimbangan);
				
				$str = "insert into ".$dbname.".pmn_4customer (kodecustomer, namacustomer) values('".$newkode."','".$namatimbangan."')";
				try{
					$owlPDO->exec($str);
				}catch (PDOException $e){
					$stat = '0';
					print "Gagal! : ".$e->getMessage()."\n";die(); 
				}	
				
				foreach($expkomoditi as $key=>$val){
					$str="select inisial from ".$dbname.".log_5masterbarang where kodebarang='".$val."'";
					$res=fetchData($str);
					$kodekomoditi = $res[0]['inisial'];
					
					if(kodekomoditi!=''){
						$str = "insert into ".$dbname.".pmn_4komoditi (kodecustomer, kodebarang, kodekomoditi) values('".$newkode."','".$val."','".$kodekomoditi."')";
						try{
							$owlPDO->exec($str);
						}catch (PDOException $e){
							$stat = '0';
							print "Gagal! : ".$e->getMessage()."\n";die(); 
						}
					}
				}
				
				$str = "insert into ".$dbname.".pmn_4customerwb (kodecustomer, kodetimbangan,uploadby,status) values('".$newkode."','".$kdtimbangan."','".$_SESSION['standard']['userid']."','1')";
			}
			else
			{
				foreach($expkomoditi as $key=>$val){
					$str="select inisial from ".$dbname.".log_5masterbarang where kodebarang='".$val."'";
					$res=fetchData($str);
					$kodekomoditi = $res[0]['inisial'];
					
					if(kodekomoditi!=''){
						$str="select * from ".$dbname.".pmn_4komoditi where kodecustomer='".kode."' and kodebarang='".$val."'";
						$res=fetchData($str);
						$countkomoditi = count($res);
						
						if($countkomoditi==0){
							$str = "insert into ".$dbname.".pmn_4komoditi (kodecustomer, kodebarang, kodekomoditi) values('".$newkode."','".$val."','".$kodekomoditi."')";
							try{
								$owlPDO->exec($str);
							}catch (PDOException $e){
								$stat = '0';
								print "Gagal! : ".$e->getMessage()."\n";die(); 
							}
						}
					}
				}
				$str="update ".$dbname.".pmn_4customerwb set kodetimbangan='".$kdtimbangan."' where kodecustomer='".$kode."'";
			}
			
			try
			{
				$owlPDO->exec($str); 
				
				$str="update ".$dbname.".temp_mastertimbangan set status='1' where kode='".$kdtimbangan."'";
				
				try
				{
					$owlPDO->exec($str);
				}
				catch (PDOException $e) 
				{
					$stat = '0';
					print "Gagal! : ".$e->getMessage()."\n";die(); 
				}
			}
			catch (PDOException $e) 
			{
				$stat = '0';
				print "Gagal! : ".$e->getMessage()."\n";die(); 
			}
		}
		else if($nmTable=='Supplier')
		{
			
			if($kode!='')
			{
				$str = "insert into ".$dbname.".log_5suptimbangan (supplierid, kodetimbangan, updateby, status) values('".$kode."','".$kdtimbangan."','".$_SESSION['standard']['userid']."','1')";
				
				try
				{
					$owlPDO->exec($str);
	
					
					$str="update ".$dbname.".temp_mastertimbangan set status='1' where kode='".$kdtimbangan."'";
					
					try
					{
						$owlPDO->exec($str);
					}
					catch (PDOException $e) 
					{
						$stat = '0';
						print "Gagal! : ".$e->getMessage()."\n";die(); 
					}
				}
				catch (PDOException $e) 
				{
					$stat = '0';
					print "Gagal! : ".$e->getMessage()."\n";die(); 
				}
			}
		}
		else
		{
			
		}
	
		echo $stat;
	break;
	

	case'getDataLokasi':
	//echo"warning:Masuk";
	$sql="select * from ".$dbname.".setup_remotetimbangan where id='".$idRemote."'";
            $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
            $query->setFetchMode(PDO::FETCH_ASSOC);
            $res=$query->fetch();  
            
	echo $res['ip']."###".$res['port']."###".$res['dbname']."###".$res['username']."###".$res['password'];
	break;
	
   
        case'getDataSipb':
        
        $arr="##dbnm##prt##pswrd##ipAdd##usrName##lksiServer##nmTable";
        
        
        try {
		   $owlPDO = new PDO('mysql:host='.$ipAdd.';dbname='.$dbnm, $usrName, $pswrd, array(PDO::ATTR_PERSISTENT => true));
           // $owlPDO = new PDO('mysql:host='.$ipAdd.':'.$prt, $usrName, $pswrd, array(PDO::ATTR_PERSISTENT => true));
           $owlPDO->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
         }
         catch (PDOException $e) {
                print " Gagal, could not connect\n";	
                print "Error!: " . $e->getMessage() . "<br/>";
            die();
         }
        $sGetDt="select * from ".$dbnm.".".$nmTable." where uploadStatus=0";
        $qGetDt=$owlPDO->query($sGetDt) or die(print " Gagal: ".PDOException::getMessage());
        $row=owlBaris($qGetDt);
        $tab.="<button class=mybutton onclick=uploadData2('".$row."','".$arr."') id=btnUpload>".$_SESSION['lang']['startUpload']."</button>";
        $tab.="<table cellpadding=1 cellspacing=1 border=0 class=sortable><thead><tr>";
        $tab.="<td>No.</td>";
        $tab.="<td>No. Kontrak</td>";
        $tab.="<td>No. SIPB</td>";
        $tab.="<td>Tanggal</td>";
        $tab.="<td>Kodebarang</td>";
        $tab.="<td>Kode Transporter</td>";
        $tab.="<td>Nama Transporter</td>";
        $tab.="<td>Keterangan</td>";
        $tab.="</tr></thead><tbody>";
            $qGetDt->setFetchMode(PDO::FETCH_ASSOC);
            while($rGetDt=$qGetDt->fetch())
            {
                $sNm="select TRPNAME from ".$dbnm.".msvendortrp where TRPCODE='".$rGetDt['TRPCODE']."'";
                $qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
                $qNm->setFetchMode(PDO::FETCH_ASSOC);
                $rNm=$qNm->fetch();
                $no+=1;
                $tab.="<tr class=rowcontent id=row_".$no."><td>".$no."</td>";
                $tab.="<td id=kontrak_".$no.">".$rGetDt['CTRNO']."</td>";
                $tab.="<td id=sipb_".$no.">".$rGetDt['SIPBNO']."</td>";
                $tab.="<td id=tgl_sipb_".$no.">".$rGetDt['SIPBDATE']."</td>";
                $tab.="<td id=kdbrg_".$no.">".$rGetDt['PRODUCTCODE']."</td>";
                $tab.="<td id=trpcod_".$no.">".$rGetDt['TRPCODE']."</td>";
                $tab.="<td id=trp_nm_".$no.">".$rNm['TRPNAME']."</td>";
                $tab.="<td id=ket_".$no.">".$rGetDt['DESCRIPTION']."</td></tr>";
            }
            $tab.="</tbody></table>";
            echo $tab;
        break;
        
        
        
        case'uploadData2':
            
        $sCek="select * from ".$dbname.".pabrik_mssipb where CTRNO='".$kntrk."' and SIPBNO='".$nosipb."'";           
        $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
        $rCek=owlBaris($qCek);
		
        if($rCek<1){
			$sMsk="insert into ".$dbname.".pabrik_mssipb (CTRNO,SIPBNO,SIPBDATE,PRODUCTCODE,TRPCODE,DESCRIPTION)";
            $sMsk.="values ('".$kntrk."','".$nosipb."','".$tglsibp."','".$kdbrg."','".$trpcode."','".$ketdt."')";
            try{
				$owlPDO->exec($sMsk);
				
				//Check Supplier (log_5supplier)
				$sDck="select kodetimbangan from ".$dbname.".log_5supplier where kodetimbangan='".$trpcode."'";
				$qDck=$owlPDO->query($sDck) or die(print " Gagal: ".PDOException::getMessage());
				$rDck=owlBaris($qDck);
				if($rDck<1){
					//Create Supplier ID
					$sNo="select supplierid,kodekelompok from ".$dbname.".log_5supplier where kodekelompok like 'S%' order by `supplierid` desc limit 1";
					$qNo=$owlPDO->query($sNo) or die(print " Gagal: ".PDOException::getMessage());
					$qNo->setFetchMode(PDO::FETCH_ASSOC);
					$rNo=$qNo->fetch();
					$no=substr($rNo['supplierid'],4,6);
					$rNo['kodekelompok']="S003";
					$supplierId=intval($no);
					$supplierId+=1;
					$supplierId=$rNo['kodekelompok'].$supplierId;
       
					$sIns="INSERT INTO ".$dbname.".`log_5supplier` (`supplierid`,`namasupplier`,`kodekelompok`,`kodetimbangan`) 
					VALUES ('".$supplierId."','".$trpname."','".$rNo['kodekelompok']."','".$trpcode."')";
					try{
						$owlPDO->exec($sIns);
						try{
							$owlPDO = new PDO('mysql:host='.$ipAdd.';dbname='.$dbnm, $usrName, $pswrd, array(PDO::ATTR_PERSISTENT => true));
							// $owlPDO = new PDO('mysql:host='.$ipAdd.':'.$prt, $usrName, $pswrd, array(PDO::ATTR_PERSISTENT => true));
							$owlPDO->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
						}catch (PDOException $e) {
							print " Gagal, could not connect\n";	
							print "Error!: " . $e->getMessage() . "<br/>";
							die();
						}
						
						$supd="update ".$dbnm.".".$nmTable." set uploadStatus=1 where CTRNO='".$kntrk."' and SIPBNO='".$nosipb."'";
						try{
							$owlPDO->exec($supd);
						}catch (PDOException $e){
							print " Gagal  !: " . $e->getMessage() . "\n"; 
							$stat=1;
							echo $stat;
							die(); 
						}
					}catch (PDOException $e){
						print " Gagal  !: " . $e->getMessage() . "\n"; 
						$stat=0;
						echo $stat;
						die(); 
					}
				}else{
					try{
						$owlPDO = new PDO('mysql:host='.$ipAdd.';dbname='.$dbnm, $usrName, $pswrd, array(PDO::ATTR_PERSISTENT => true));
						// $owlPDO = new PDO('mysql:host='.$ipAdd.':'.$prt, $usrName, $pswrd, array(PDO::ATTR_PERSISTENT => true));
						$owlPDO->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
					}catch (PDOException $e) {
						print " Gagal, could not connect\n";	
                        print "Error!: " . $e->getMessage() . "<br/>";
                        die();
					}
                    
					$supd="update ".$dbnm.".".$nmTable." set uploadStatus=1 where CTRNO='".$kntrk."' and SIPBNO='".$nosipb."'";
                    try{
						$owlPDO->exec($supd);
					}catch (PDOException $e) {
						print " Gagal  !: " . $e->getMessage() . "\n"; 
                        $stat=1;
                        echo $stat;
                        die(); 
					}
				}
			}catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
				$stat=0;
				echo $stat;
				die(); 
			}
		}else{
			try {
			    $owlPDO = new PDO('mysql:host='.$ipAdd.';dbname='.$dbnm, $usrName, $pswrd, array(PDO::ATTR_PERSISTENT => true));
                // $owlPDO = new PDO('mysql:host='.$ipAdd.':'.$prt, $usrName, $pswrd, array(PDO::ATTR_PERSISTENT => true));
                $owlPDO->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
			}catch (PDOException $e) {
				print " Gagal, could not connect\n";	
                print "Error!: " . $e->getMessage() . "<br/>";
				die();
			}
			
			$supd="update ".$dbnm.".".$nmTable." set uploadStatus=1 where CTRNO='".$kntrk."' and SIPBNO='".$nosipb."'";
            try{
				$owlPDO->exec($supd); 
			}catch (PDOException $e) {
				print " Gagal  !: " . $e->getMessage() . "\n"; 
                $stat=1;
                echo $stat;
                die(); 
            }
        }
        break;
		
	default:
	break;
}

?>