<?php
    require_once('master_validation.php');
    require_once('config/connection.php');
    require_once('lib/nangkoelib.php');
    require_once('lib/zLib.php');
?>	

<?php
	$method    = checkPostGet('method', '');
	$pagejs    = checkPostGet('page','');
	$tanggalsch= checkPostGet('tanggalsch', '');
	$kodeorgsch= checkPostGet('kodeorgsch', '');
	$barangsch = checkPostGet('barangsch', '');
	$tanggal   = tanggalsystemn(checkPostGet('tanggal', ''));
	$kodeorg   = checkPostGet('kodeorg','');
	$barang    = checkPostGet('barang','');
	$mode      = checkPostGet('mode','');
?>

<?php
switch ($method) {
    // case 'getStatus':
	// 	$str    = "SELECT * FROM " . $dbname . ".pabrik_stokforsale WHERE notransaksi ='".$notransaksii."' ";
	// 	$res    =$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	// 	$res->setFetchMode(PDO::FETCH_ASSOC);
	// 	$bar=$res->fetch();
	// 	echo $bar['notransaksi']."###".$bar['status'];
    // break;
    // case 'cekdata':
    //     $str        = "SELECT * FROM " . $dbname . ".pabrik_stokforsale WHERE kodeorg ='".$kd."' AND kodeorgtujuan ='".$kdtujuan."'";
    //     $res        = fetchData($str);
    //     if(count($res)>0){
    //         exit("Warning : Data dengan " . $_SESSION['lang']['kodeorganisasi'] . " : <b>".$kd."</b> dan " . $_SESSION['lang']['kodeorganisasi'] . " " . $_SESSION['lang']['tujuan'] . " : <b>".$kdtujuan."</b>  sudah ada !");}
    // break;
    // case 'getkodetangki':
	// 	$str = "select kodetangki,kodeorg,keterangan from ".$dbname.".pabrik_5tangki where kodeorg = '".$millcode."'";
	// 	$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
	// 	$res->setFetchMode(PDO::FETCH_ASSOC);
	// 	$optpilihtangki="<option value='' hidden>" . $_SESSION['lang']['pilihdata'] . "</option>";
	// 	foreach ($res as $bar) {
	// 		$optpilihtangki.="<option value=" . $bar['kodetangki'] . ">" . $bar['kodetangki'] . " - " . $bar['keterangan'] . "</option>";
	// 	}

	// 	echo $optpilihtangki;
    // break;
    
    // case 'getedit':
    //     $str = "select * from " . $dbname . ".pabrik_5interpolasi where nourut ='".$nourut."' ";
	// 	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	// 	$res->setFetchMode(PDO::FETCH_ASSOC);
    //     $bar=$res->fetch();
    //     $str1 = "select kodetangki,kodeorg,keterangan from ".$dbname.".pabrik_5tangki where kodeorg='".$bar['millcode']."'";
	// 	$res1 = $owlPDO->query($str1) or die(print " Gagal: " . PDOException::getMessage());
	// 	$res1->setFetchMode(PDO::FETCH_ASSOC);
	// 	$optpilihtangki="<option value='' hidden>" . $_SESSION['lang']['pilihdata'] . "</option>";
	// 	foreach ($res1 as $bar1) {
	// 		if ($bar['kodetangki'] == $bar1['kodetangki']) {
	// 			$optpilihtangki.="<option value=" . $bar1['kodetangki'] . " selected>" . $bar1['kodetangki'] . " - " . $bar1['keterangan'] . "</option>";
	// 		} else { 
	// 			$optpilihtangki.="<option value=" . $bar1['kodetangki'] . ">" . $bar1['kodetangki'] . " - " . $bar1['keterangan'] . "</option>";
    //         }
            
	// 	}

	// 	echo $nourut."###".$bar['millcode']."###".$optpilihtangki."###".$bar['tinggi']."###".$bar['milimeter']."###".$bar['liter'];
	// break;
    case'loadData':
        $tab        ="";
        $footer     ="";
		$limit      = 10;
        $page       = 0;
        $colspan    = 7;

		if (isset($pagejs)) {
			$page   = $pagejs;
			if ($page < 0)
				$page = 0;
        }
        
		$offset     = floatval($page) * $limit;
		$maxdisplay =(floatval($page) * $limit);
        $no         =((floatval($page) * $limit));
        
        if($tanggalsch!='' || $kodeorgsch!='' || $barangsch!=''){ 
            $where.="and tanggal LIKE '%".$tanggalsch."%' and kodeorg LIKE '%".$kodeorgsch."%' and barang like '%".$barangsch."%' ";
        }

        // $str        = "SELECT COUNT(*) AS jmlhrow FROM ".$dbname.".pabrik_stokforsale WHERE 1=1 ".$where." and kodeorg='".$_SESSION['empl']['lokasitugas']."' GROUP BY kodeorg,tanggal,barang ORDER BY createtime DESC";
        $str        = "SELECT tanggal, kodeorg, barang FROM ".$dbname.".pabrik_stokforsale WHERE 1=1 ".$where." and kodeorg='".$_SESSION['empl']['lokasitugas']."' GROUP BY tanggal, kodeorg, barang ORDER BY createtime DESC";
        $res        = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs     = owlBaris($res);
		// $res        = fetchdata($str);
		// $jlhbrs     = @$res[0]['jmlhrow'];
        $totrows    = ceil($jlhbrs / $limit);
        
        if($totrows == 0){
            $totrows = 1;
        }
                
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++){
            $sel    = ($page==$er-1)? 'selected': '';
            $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        
        $frompage   = ((floatval($page)*$limit)+1);
        if(((floatval($page)+1)*$limit) > $jlhbrs){
            $topage = $jlhbrs;
        }else{
            $topage = ((floatval($page)+1)*$limit);
        }

        if($jlhbrs < 1){
                $tab.="<tr class=rowcontent>
                            <td style='text-align:center' colspan=".$colspan.">" . $_SESSION['lang']['errdatanotexist'] . "</td>
                        </tr>";
        }else{
            // $iList  = "SELECT * FROM " . $dbname . ".pabrik_stokforsale WHERE 1=1 ".$where." and kodeorg='".$_SESSION['empl']['lokasitugas']."' GROUP BY kodeorg,tanggal,barang ORDER BY kodeorg LIMIT ".$offset.",".$limit." "; echo $iList;
            $iList  = "SELECT * FROM " . $dbname . ".pabrik_stokforsale WHERE 1=1 ".$where." and kodeorg='".$_SESSION['empl']['lokasitugas']."' GROUP BY tanggal, kodeorg, barang ORDER BY tanggal desc, kodeorg, barang LIMIT ".$offset.",".$limit." ";
            $hasil  = fetchdata($iList);
            
            foreach ($hasil as $dList){
                $optOrg         = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$dList['kodeorg']."'");
                $no+=1;
                $tab.="<tr class=rowcontent>";
                    $tab.="<td align=center>" . $no . "</td>";
                    $tab.="<td align=left>".$dList['kodeorg']."</td>";
                    $tab.="<td align=left>".$dList['barang']."</td>";
                    $tab.="<td align=left>".$dList['tanggal']."</td>";
                    $tab.="<td align=left>".getNamaKaryawan($dList['createby'])."</td>";
                    $tab.="<td align=center>
                                <img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"fillField('" . tanggalnormal($dList['tanggal']) . "','" . $dList['kodeorg'] . "','" . $dList['barang'] . "');\">
                            </td>";
                    $tab.="<td align=center>
                                <img src=images/application/application_delete.png class=zImgBtn  caption='Delete' onclick=\"del('" . $dList['kodeorg'] . "','" . tanggalnormal($dList['tanggal']) . "','".$dList['barang']."');\">
                            </td>";
                $tab.="</tr>
                    </tbody>";
            }
            $footer .= "<tr>
                            <td colspan=".$colspan." align=center>
                                ".$frompage." to ".$topage." Of ".  $jlhbrs."
                            </td>
                        </tr>";
            $footer .= "<tr>
                            <td colspan=".$colspan." align=center>";
                            if($page!=0){
                                $footer .= "<button class=mybutton onclick=loadData(" . (floatval($page) - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>";
                            }
                $footer  .= "<select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>";
                            if((floatval($page)+1) != $totrows){
                                $footer .="<button class=mybutton onclick=loadData(" . (floatval($page) + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>";
                            }
            $footer .=     "</td>
                        </tr>";
        }
        echo $tab."####".$footer;
    break;

    case 'preview':
        $qry="SELECT * FROM ".$dbname.".pabrik_stokforsale WHERE kodeorg = '$kodeorg' and tanggal='".$tanggal."' and barang='$barang'";
        $hsl=fetchdata($qry);
        if(count($hsl) > 0){
            $err= "Data pada Pabrik ".$kodeorg." di tanggal ".tanggalnormal($tanggal)." sudah ada, silahkan klik Ubah dihalaman depan !";
        }
        echo $tanggal."#".$kodeorg."#".$err."#".$barang;
    break;
    
    case 'loaddetail':
        $tab="";
        if ($barang=='CPO') {
            if($mode == 'update'){
                $tab.="<fieldset style=float:left><legend>" . $_SESSION['lang']['detail'] . "</legend>";
                $tab.="<table class=sortable cellspacing=1 border=0>";
                    $tab.="<thead>";
                        $tab.="<tr class=rowheader>";
                            $tab.="<th align=center rowspan=2>" . $_SESSION['lang']['tangki'] . "</th>";
                            $tab.="<th align=center colspan=2>" . $_SESSION['lang']['under5persen'] . "</th>";
                            $tab.="<th align=center rowspan=2>" . $_SESSION['lang']['upper5persen'] . "</th>";
                        $tab.="</tr>";
                        $tab.="<tr class=rowheader>";
                            $tab.="<th align=center>" . $_SESSION['lang']['sudahkontrak'] . " (TON)</th>";
                            $tab.="<th align=center>" . $_SESSION['lang']['siapjual'] . " (TON)</th>";
                        $tab.="</tr>";
                    $tab.="</thead>";
                    $tab.="<tbody>";
                    $str    = "SELECT * FROM ".$dbname.".pabrik_stokforsale WHERE kodeorg = '$kodeorg' AND tanggal='".$tanggal."' and kodetangki != 'INP'";
                    $res = fetchData($str);
                    $no=0;
                    foreach ($res as $key => $rab) {
                        $no++;
                        $tab.="<tr class=rowcontent id=row".$no.">";
                            $tab.="<td id=tangki".$no.">".$rab['kodetangki']."</td>";
                            $tab.="<td><input onclick=\"this.select();\" type=text maxlength=10 class=myinputtextnumber id=alreadycontract".$no." onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\"  value=".($rab['alreadycontract'])."></td>";
                            $tab.="<td>
                                        <input type=hidden maxlength=10 class=myinputtextnumber id=readyforsaleasli".$no." onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\" value=".($rab['readyforsale']).">        
                                        <input onclick=\"this.select();\" type=text maxlength=10 class=myinputtextnumber id=readyforsale".$no." onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\" value=".($rab['readyforsale']).">
                                    </td>";
                            $tab.="<td id=upper5persen".$no." align=right>".($rab['upper5persen'])."</td>";
                        $tab.="</tr>";
                    }
                    $tab.="<tr class=rowcontent id=rowx>";
                        $str    = "SELECT inprocessac,inprocessrfs,kodetangki FROM ".$dbname.".pabrik_stokforsale WHERE kodeorg = '$kodeorg' and tanggal='".$tanggal."' and kodetangki ='INP'";
                        $hsl=fetchdata($str)[0];
                        if($hsl['kodetangki'] == 'INP'){
                            $label = 'IN PROCESS';
                        }
                        $tab.="<td id=inprocess>".$label."</td>";
                        $tab.="<td><input onclick=\"this.select();\" type=text maxlength=10 class=myinputtextnumber id=inprocessac onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\"  value=".$hsl['inprocessac']."></td>";
                        $tab.="<td><input onclick=\"this.select();\" type=text maxlength=10 class=myinputtextnumber id=inprocessrfs onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\" value=".$hsl['inprocessrfs']."></td>";
                        $tab.="<td></td>";
                    $tab.="</tr>";
                    $tab.="<tr>";
                        $tab.="<td align=center colspan=4>
                                    <button id=tombolsave class=mybutton onclick=simpan(".$no.")>".$_SESSION['lang']['save']."</button>
                                    <button id=batal class=mybutton onclick=displayList()>".$_SESSION['lang']['selesai']."</button>
                                </td>
                            </tr>";	
                    $tab.="</tbody>";
                $tab.="</table>";
            }else{
                $tab.="<fieldset style=float:left><legend>" . $_SESSION['lang']['detail'] . "</legend>";
                $tab.="<table class=sortable cellspacing=1 border=0>";
                    $tab.="<thead>";
                        $tab.="<tr class=rowheader>";
                            $tab.="<th align=center rowspan=2>" . $_SESSION['lang']['tangki'] . "</th>";
                            $tab.="<th align=center colspan=2>" . $_SESSION['lang']['under5persen'] . "</th>";
                            $tab.="<th align=center rowspan=2>" . $_SESSION['lang']['upper5persen'] . "</th>";
                        $tab.="</tr>";
                        $tab.="<tr class=rowheader>";
                            $tab.="<th align=center>" . $_SESSION['lang']['sudahkontrak'] . " (TON)</th>";
                            $tab.="<th align=center>" . $_SESSION['lang']['siapjual'] . " (TON)</th>";
                        $tab.="</tr>";
                    $tab.="</thead>";
                    $tab.="<tbody>";
                    $str    = "SELECT kodetangki FROM ".$dbname.".pabrik_5tangki WHERE kodeorg = '$kodeorg' and komoditi = '$barang' ";
                    $res    = fetchData($str);
                    $no=0;
                    foreach ($res as $key => $rab) {
                        $no++;
                        $tab.="<tr class=rowcontent id=row".$no.">";
                            $tab.="<td id=tangki".$no.">".$rab['kodetangki']."</td>";
                            $tab.="<td><input onclick=\"this.select();\" type=text maxlength=10 class=myinputtextnumber id=alreadycontract".$no." onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\" value=0></td>";
                            $str0="SELECT kuantitas AS stok FROM pabrik_masukkeluartangki WHERE kodeorg='".$kodeorg."' and kodetangki = '".$rab['kodetangki']."' AND tanggal = '$tanggal' AND cpoffa < '5'";
                            @$res0=fetchdata($str0)[0];
                            $tab.="<td>
                                        <input type=hidden maxlength=10 class=myinputtextnumber id=readyforsaleasli".$no." onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\" value=".($res0['stok']/1000).">        
                                        <input onclick=\"this.select();\" type=text maxlength=10 class=myinputtextnumber id=readyforsale".$no." onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\" value=".(($res0['stok']/1000)).">
                                    </td>";
                            $str1="SELECT kuantitas AS stok FROM pabrik_masukkeluartangki WHERE kodeorg='".$kodeorg."' and kodetangki = '".$rab['kodetangki']."' AND tanggal = '$tanggal' AND cpoffa >= '5'";
                            @$res1=fetchdata($str1)[0];
                            $tab.="<td id=upper5persen".$no." align=right>".@(($res1['stok']/1000))."</td>";
                        $tab.="</tr>";
                    }
                    $tab.="<tr class=rowcontent id=rowx>";
                        $tab.="<td id=inprocess>IN PROCESS</td>";
                        $tab.="<td><input type=text maxlength=10 class=myinputtextnumber id=inprocessac onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\" onclick=\"this.select();\" value='0'></td>";
                        $tab.="<td><input type=text maxlength=10 class=myinputtextnumber id=inprocessrfs onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\" onclick=\"this.select();\" value='0'></td>";
                        $tab.="<td></td>";
                    $tab.="</tr>";
                    $tab.="<tr>";
                        $tab.="<td align=center colspan=4>
                                    <button id=tombolsave class=mybutton onclick=simpan(".$no.")>".$_SESSION['lang']['save']."</button>
                                </td>
                            </tr>";	
                    $tab.="</tbody>";
                $tab.="</table>";
            }
        }else{
            if($mode != 'update'){
                $tab.="<fieldset style=float:left><legend>" . $_SESSION['lang']['detail'] . "</legend>";
                $tab.="<table class=sortable cellspacing=1 border=0>";
                $tab.="<thead>";
                $tab.="<tr class=rowheader>";
                $tab.="<th align=center rowspan=2>" . $_SESSION['lang']['tangki'] . "</th>";
                $tab.="<th align=center colspan=2>" . $_SESSION['lang']['stock'] . "</th>";
                $tab.="</tr>";
                $tab.="<tr class=rowheader>";
                $tab.="<th align=center>SOLD (TON)</th>";
                $tab.="<th align=center>AFS (TON)</th>";
                $tab.="</tr>";
                $tab.="</thead>";
                $tab.="<tbody>";
                
                $str="SELECT kodetangki FROM ".$dbname.".pabrik_5tangki WHERE kodeorg = '$kodeorg' and komoditi = '$barang' ";
                $res=fetchData($str);
                $no=0;
                foreach ($res as $key => $val) {
                    $no++;
                    $tab.="<tr class=rowcontent id=row".$no.">";
                    $tab.="<td id=tangki".$no.">".$val['kodetangki']."</td>";
                    $tab.="<td><input onclick=\"this.select();\" type=text maxlength=10 class=myinputtextnumber id=sold".$no." onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\" value=0></td>";

                    $strx="SELECT kernelquantity AS stok FROM pabrik_masukkeluartangki WHERE kodeorg='".$kodeorg."' and kodetangki='".$val['kodetangki']."' AND tanggal='$tanggal'";
                    @$resx=fetchdata($strx);

                    $tab.="<td><input onclick=\"this.select();\" type=text maxlength=10 class=myinputtextnumber id=readyforsale".$no." onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\" value=".((@$resx[0]['stok']/1000))."></td>";
                  
                    $tab.="</tr>";
                }
            }else{
                $tab.="<fieldset style=float:left><legend>" . $_SESSION['lang']['detail'] . "</legend>";
                $tab.="<table class=sortable cellspacing=1 border=0>";
                $tab.="<thead>";
                $tab.="<tr class=rowheader>";
                $tab.="<th align=center rowspan=2>" . $_SESSION['lang']['tangki'] . "</th>";
                $tab.="<th align=center colspan=2>" . $_SESSION['lang']['stock'] . "</th>";
                $tab.="</tr>";
                $tab.="<tr class=rowheader>";
                $tab.="<th align=center>AFS (TON)</th>";
                $tab.="<th align=center>RFS (TON)</th>";
                $tab.="</tr>";
                $tab.="</thead>";
                $tab.="<tbody>";

                $str="SELECT * FROM ".$dbname.".pabrik_stokforsale WHERE kodeorg = '$kodeorg' and barang = '$barang' and tanggal='$tanggal' ";
                $res=fetchData($str);
                $no=0;
                foreach ($res as $key => $val) {
                    $no++;
                    $tab.="<tr class=rowcontent id=row".$no.">";
                    $tab.="<td id=tangki".$no.">".$val['kodetangki']."</td>";
                    $tab.="<td><input onclick=\"this.select();\" type=text maxlength=10 class=myinputtextnumber id=sold".$no." onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\" value=".$val['sold']."></td>";

                    $tab.="<td><input onclick=\"this.select();\" type=text maxlength=10 class=myinputtextnumber id=readyforsale".$no." onkeydown=upperCaseF(this) onkeypress=\"return angka_doang(event);\" value=".$val['readyforsale']."></td>";
                  
                    $tab.="</tr>";
                }

            }
            $tab.="<tr>";
            $tab.="<td align=center colspan=4><button id=tombolsave class=mybutton onclick=simpan(".$no.")>".$_SESSION['lang']['save']."</button></td>";
            $tab.="</tr></tbody>";
        }
        echo $tab;
    break;
    case 'insert':
        $met 	        = checkPostGet('met','');
        $perulangan 	= checkPostGet('perulangan','');
        $kodetangki 	= checkPostGet('kodetangki','');
        $barang         = checkPostGet('barang','');
		$alreadycontract= checkPostGet('alreadycontract','');
		$readyforsale 	= checkPostGet('readyforsale','');
        $sold           = checkPostGet('sold','');
		$upper5persen 	= checkPostGet('upper5persen','');
		$inprocess 	    = checkPostGet('inprocess','');
		$inprocessac 	= checkPostGet('inprocessac','');
		$inprocessrfs 	= checkPostGet('inprocessrfs','');
		
		$alreadycontract=str_replace(',', '',$alreadycontract);
		$readyforsale   =str_replace(',', '',$readyforsale);
		$sold           =str_replace(',', '',$sold);
		$upper5persen   =str_replace(',', '',$upper5persen);
		$inprocess      =str_replace(',', '',$inprocess);
		$inprocessac    =str_replace(',', '',$inprocessac);
		$inprocessrfs   =str_replace(',', '',$inprocessrfs);
		
		
        if($perulangan=='akhir'){
            if($inprocess == 'IN PROCESS'){
                $tangkiinp = 'INP';
            }
            if($met == 'update'){
                $data = array(
                    'kodetangki'=>$tangkiinp,
                    'barang'=>$barang,
                    'inprocessac'=>$inprocessac,
                    'inprocessrfs'=>$inprocessrfs,
                    'updateby' => $_SESSION['standard']['userid']
                );
                $where = "kodeorg='".$kodeorg."' and kodetangki='INP' and tanggal='".$tanggal."'";
                $str = updateQuery($dbname,'pabrik_stokforsale',$data,$where);
                try {$owlPDO->exec($str);} catch (PDOException $e) {echo " Gagal," . addslashes($e->getMessage());}
            }else{
                $data = array(
                    'kodeorg'=>$kodeorg,
                    'tanggal'=>$tanggal,
                    'kodetangki'=>$tangkiinp,
                    'barang'=>$barang,
                    'inprocessac'=>$inprocessac,
                    'inprocessrfs'=>$inprocessrfs,
                    'createby' => $_SESSION['standard']['userid'],
                    'createtime' => date('Y-m-d H:i:s')
                );
                $cols = array();
                foreach($data as $key=>$row) {
                    $cols[] = $key;
                }
                $str = insertQuery($dbname,'pabrik_stokforsale',$data,$cols); 

                try {$owlPDO->exec($str);} catch (PDOException $e) {echo " Gagal," . addslashes($e->getMessage());}
            }
            echo $tanggal."##".$kodeorg;
        }else{
            //hapus dulu
            $str = "DELETE FROM ".$dbname.".pabrik_stokforsale WHERE kodeorg='".$kodeorg."' and kodetangki='".$kodetangki."' and tanggal='".$tanggal."' and barang='".$barang."'";
            try {$owlPDO->exec($str);} catch (PDOException $e) {echo " Gagal," . addslashes($e->getMessage());}
            //insert lagi
            $data = array(
                'kodeorg'=>$kodeorg,
                'kodetangki'=>$kodetangki,
                'barang'=>$barang,
                'tanggal'=>$tanggal,
                'upper5persen'=>$upper5persen,
                'alreadycontract'=>$alreadycontract,
                'readyforsale'=>$readyforsale,
                'sold'=>$sold,
                'createby' => $_SESSION['standard']['userid'],
                'createtime' => date('Y-m-d H:i:s')
            );
            $cols = array();
            foreach($data as $key=>$row) {
                $cols[] = $key;
            }
            $str = insertQuery($dbname,'pabrik_stokforsale',$data,$cols); 

            try {$owlPDO->exec($str);} catch (PDOException $e) {echo " Gagal," . addslashes($e->getMessage());}
        }
    break;

    case 'delete':
        // $str    = "SELECT kodetangki FROM ".$dbname.".pabrik_5tangki WHERE kodeorg = '$kodeorg' and komoditi = 'CPO' ";
        // $res    = fetchData($str);
        // foreach ($res as $val) {
            $ha = "";
			$ha     = "DELETE FROM " . $dbname . ".pabrik_stokforsale WHERE kodeorg='".$kodeorg."' AND tanggal='".$tanggal."' and barang='".$barang."'";
			// exit("error".trim($ha));
            try {
                $owlPDO->exec($ha);
            } catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        // }
    break;
    default:
}
?>