<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
include_once('lib/zFunction.php');
$tglevaluasi = tanggalsystem(checkPostGet('tglevaluasi', ''));
$method = checkPostGet('method', '');
$noid = checkPostGet('noid', '');
$unit = checkPostGet('unit', '');
$numrow = checkPostGet('numrow', '');
$kepada = checkPostGet('kepada', '');
$karyawan = checkPostGet('karyawan', '');
$kriteria = checkPostGet('kriteria', '');
$penilaian = checkPostGet('penilaian', '');
$nilai = checkPostGet('nilai', '');
$kekuatan = checkPostGet('kekuatan', '');
$perbaikan = checkPostGet('perbaikan', '');
$catatan = checkPostGet('catatan', '');
$rekomendasi = checkPostGet('rekomendasi', '');
$ttd1 = checkPostGet('ttd1', '');
$ttd2 = checkPostGet('ttd2', '');
$ttd3 = checkPostGet('ttd3', '');
$karyawancr = checkPostGet('karyawancr', '');
$tglevaluasi = tanggalsystem(checkPostGet('tglevaluasi', ''));
$tglevaluasicr = tanggalsystem(checkPostGet('tglevaluasicr', ''));
$tgleva = checkPostGet('tgleva', '');
$optrekomen=array("1"=>"Pengangkatan","2"=>"Kontrak Diperpanjang","3"=>"Kontrak Diperbarui","5"=>"Pemutusan Hubungan Kerja");
switch ($method) {
	case 'getjenispen':
		# code...
		$optjenispen="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".sdm_5kriteriapenilaian where kode='".$kriteria."'";
		$qtr=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$qtr->setFetchMode(PDO::FETCH_OBJ);
		while ($rtr=$qtr->fetch()) {
			# code...\
				$optjenispen.="<option value='".$rtr->idjenispenilaian."'>".$rtr->penilaian."</option>";
		}	
		echo $optjenispen;
	break;
    case 'getkar':
        $str = " select a.karyawanid,a.namakaryawan,a.lokasitugas,a.statuskaryawan from " . $dbname . ".datakaryawan a
		left join sdm_5tipekaryawan b on a.tipekaryawan = b.id
        where lokasitugas='".$unit."' and tanggalkeluar = '0000-00-00' order by namakaryawan";
        $optkar = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            if($karyawan==$bar->karyawanid){
                $optkar.="<option value='" . $bar->karyawanid . "' selected>" . $bar->namakaryawan . " | " .$bar->statuskaryawan. " | " . $bar->lokasitugas . "</option>";              
            }else{
                $optkar.="<option value='" . $bar->karyawanid . "'>" . $bar->namakaryawan . " | " .$bar->statuskaryawan. " | " . $bar->lokasitugas . "</option>";
            }
        }   
        echo $optkar;
    break;
    case'detail':
        $str="select * from ".$dbname.".sdm_evaluasiht where tanggalevaluasi='".$tglevaluasi."' and karyawanid='".$karyawan."' limit 1";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar = $res->fetch();
			$kekuatan=$bar->kekuatan;
			$perbaikan=$bar->perbaikandiperlukan;
			$catatan=$bar->catatan;
			$rekomendasi=$bar->rekomendasi;
			$ttd1=$bar->ttd1;
			$ttd2=$bar->ttd2;
			$ttd3=$bar->ttd3;
		
        //exit('warning : masuk'.$unit." ".$karyawan." ".$tglevaluasi);
        //get rekomendasi
        $rekomen= getEnum($dbname,'sdm_evaluasiht','rekomendasi');
        $opts="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        foreach($rekomen as $key=>$val){
        if($val=='1')
        $caption='Diangkat';
        else if($val=='2')
        $caption='Kontrak Diperpanjang';
        else if($val=='3')
        $caption='Kontrak Diperbarui';
        else if($val=='5')
        $caption='Pemutusan Tenaga Kerja';
            if($rekomendasi==$val){
                $opts.="<option value='".$val."' selected>".$caption."</option>";
            }else{
                $opts.="<option value='".$val."'>".$caption."</option>";    
            }
        }
        //get karyawan manager/div head
        $optmanager="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $smanager="select karyawanid, namakaryawan, nik, lokasitugas from ".$dbname.".datakaryawan where 
        (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."') and tipekaryawan ='0' and kodejabatan not in ('111') and lokasitugas='".$_SESSION['empl']['lokasitugas']."' order by namakaryawan asc";
        $qmanager=$owlPDO->query($smanager) or die(print " Gagal: ".PDOException::getMessage());
        $qmanager->setFetchMode(PDO::FETCH_ASSOC);
        while($rmanager=$qmanager->fetch())
        {
            if($ttd1==$rmanager['karyawanid']){
                $optmanager.="<option value=".$rmanager['karyawanid']." selected>".$rmanager['namakaryawan']." - ".$rmanager['nik']." - ".$rmanager['lokasitugas']."</option>";
            }else{
                $optmanager.="<option value=".$rmanager['karyawanid'].">".$rmanager['namakaryawan']." - ".$rmanager['nik']." - ".$rmanager['lokasitugas']."</option>";
            }
        }
        //get karyawan HC
        $optHC="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sHC="select karyawanid, namakaryawan from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."')
         and bagian like 'HR%'  order by namakaryawan asc";
        $qHC=$owlPDO->query($sHC) or die(print " Gagal: ".PDOException::getMessage());
        $qHC->setFetchMode(PDO::FETCH_ASSOC);
        while($rHC=$qHC->fetch())
        {
            if($ttd2==$rHC['karyawanid']){
                $optHC.="<option value=".$rHC['karyawanid']." selected>".$rHC['namakaryawan']."</option>";
            }else if($ttd2!=$rHC['karyawanid']){
                $optHC.="<option value=".$rHC['karyawanid'].">".$rHC['namakaryawan']."</option>";
            }
        }
        //get karyawan HRD
        $optHRD="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $sHRD="select karyawanid, namakaryawan from ".$dbname.".datakaryawan where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '".date("Y-m-d")."')
         and bagian in ('HRD','HHRD','HRA')  order by namakaryawan asc";
        $qHRD=$owlPDO->query($sHRD) or die(print " Gagal: ".PDOException::getMessage());
        $qHRD->setFetchMode(PDO::FETCH_ASSOC);
        while($rHRD=$qHRD->fetch())
        {
            if($ttd3==$rHRD['karyawanid']){
                $optHRD.="<option value=".$rHRD['karyawanid']." selected>".$rHRD['namakaryawan']."</option>";
            }else{
                $optHRD.="<option value=".$rHRD['karyawanid'].">".$rHRD['namakaryawan']."</option>";
            }
        }
        //get Kriteria Penilaian
        $optsp = '';
        $str = "select * from " . $dbname . ".sdm_5jeniskriteria order by kode";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $optsp = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        while ($bar = $res->fetch()) {
            $optsp.="<option value='" . $bar->kode . "'>" . $bar->kriteria . "</option>";
        }
        OPEN_BOX();
        echo"
        <fieldset style='float:left;'>
        <legend>" . $_SESSION['lang']['penilaian'] . "</legend>
        <table border=0><tr><td valign=top>
        <table border=0 cellpadding=1 cellspacing=1 class=sortable>
			<thead>
			<tr class=rowheader>    
				<td align=center>".$_SESSION['lang']['kriteria']."  ".$_SESSION['lang']['penilaian']."</td>
				<td align=center>" . $_SESSION['lang']['nilai'] . " [N]</td>
				<td align=center >" . @$_SESSION['lang']['keterangan'] . "</td>				
			</tr>
			</thead>";
        $no=0;
        $no2=0;
        $str = "select * from " . $dbname . ".sdm_5jeniskriteria order by kode asc";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $optsp = "<option value=''>" . $_SESSION['lang']['pilihdata'] . "</option>";
        while ($bar = $res->fetch()) {
			$data['kriteria'] = $bar->kriteria;
			//echo $data['kriteria'];
			echo "
				<tr class=rowcontent>   
					<td colspan=3><b>".$bar->kode.". ".$bar->kriteria."</b></td>
				</tr>";
					//$no2=0;
					$str="select * from ".$dbname.".sdm_5kriteriapenilaian where kode='".$bar->kode."' order by kode asc";
					$qtr=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
					$qtr->setFetchMode(PDO::FETCH_OBJ);
					while ($rtr=$qtr->fetch()) {
							$sdt="select * from ".$dbname.".sdm_nilai_penilaian order by nilai desc";
							$rdt=$owlPDO->query($sdt) or die(print " Gagal: ".PDOException::getMessage());
							$rdt->setFetchMode(PDO::FETCH_OBJ);
							//$bdt = $rdt->fetch();
							//exit("error :".$sdt);
							$optnilai='';
							while ($bdt = $rdt->fetch()) {
									$optnilai.="<option value=".$bdt->kodenil." >".$bdt->nama."</option>";
								}
								echo "
								<tr id='row_".$rtr->idjenispenilaian."' class=rowcontent>
								<td>&nbsp&nbsp&nbsp&nbsp".$rtr->idjenispenilaian.". ".$rtr->penilaian."</td>
								<td><select for='nilai' id=nilai_".$no2." style=\"width:70px;\">".$optnilai."</select>
									<input hidden for='kode' id=kdnil_".$no2." value='".$rtr->idjenispenilaian."'></td>
								<td><input style=width:300px for='kom' type=text class=myinputtext id=kom_".$no2." ></td>
								</tr>";
							$no2+=1;
					}
        }
        echo "
		</table><input type=hidden id=totrows value='".$no2."' /></td>
		<tr><td colspan=50><hr></td></tr><tr>
        <td  valign=top>
        <table border=0 width=97% cellspacing=0 valign=top>
                <tr>
                    <td>Feed Back Management/Atasan :</td>
                </tr>
				<tr>                    
                    <td><textarea id='kekuatan' colspan='50' style='width:100%' rowspan='2' >".$kekuatan."</textarea></td>
                </tr>
                <tr>
                    <td hidden>Perbaikan Diperlukan</td>
                    <td hidden> : </td>
                    <td hidden><textarea id='perbaikan' colspan='50' rowspan='2' >".$perbaikan."</textarea></td>
                </tr>
                <tr>
                    <td hidden>".$_SESSION['lang']['catatan']."</td>
                    <td hidden> : </td>
                    <td hidden><textarea id='catatan' colspan='50' rowspan='2' >".$catatan."</textarea></td>
                </tr>
                <tr>
                    <td hidden>".$_SESSION['lang']['rekomendasi']."</td>
                    <td hidden> : </td>
                    <td hidden><!-- <select style=width:180px  id=rekomendasi >".$opts."</select> --> 
								<input type=hidden id=rekomendasi value='xxx' />
					</td>
                </tr>
                <tr>
                    <td hidden>".$_SESSION['lang']['menyetujui']."</td>
                    <td hidden> : </td>
                    <td hidden><!--<select style=width:180px  id=ttd1 >".$optmanager."</select>-->
								<input type=hidden id=ttd1 value='xxx' />
					</td>             
                </tr>
                <tr>
                    <td hidden>HC & GA Head</td>
                    <td hidden> : </td>
                    <td hidden><!--<select style=width:180px  id=ttd2 >".$optHC."</select>-->
								<input type=hidden id=ttd2 value='xxx' />
					</td>             
                </tr>
                <tr>
                    <td hidden>HR Officer</td>
                    <td hidden> : </td>
                    <td hidden><!--<select style=width:180px  id=ttd3 >".$optHRD."</select>-->
								<input type=hidden id=ttd3 value='xxx' />
					</td>             
                </tr>
                    <input type=hidden value='insertht' id=methodht>
                    <input type=hidden value='".$karyawan."' id=karyawanid>
                    <input type=hidden value='".$tglevaluasi."' id=tgleva>
                <tr>
                </tr>
            </table></td>
			</tr>
			</tr>
            <tr><td colspan=3 align=center><button class=mybutton onclick=saveht()>".$_SESSION['lang']['save']."</button></td></tr>
            </table>
        </fieldset>";
		if ($ttd1!='') {
            $method='updateht';
        }else{
            $method='insertht';
        }
        echo "
        <fieldset style='width:350px'>
            <legend>".$_SESSION['lang']['keterangan']."</legend>
            <ul>
                <li><b>Skala Penilaian</b></li>
                <ul type='none'>
                    <li>1 = Kurang</li>
                    <li>2 = Cukup</li>
                    <li>3 = Baik</li>
                    <li>4 = Istimewa</li>                    
                </ul>
            </ul><br>
            <ul>
                <li><b>Kriteria Rekomendasi Atasan Langsung : </b></li>
                <ul type='none'>
                    <li>1. Untuk Sikap Kerja (Attitude) minimal 3.</li>
                    <li>2. Untuk Penguasaan Pekerjaan (Job Mistery) minimal 3.</li>
                    <li>3. Untuk People Managament minimal 3.</li>
                </ul>
            </ul>
        </fieldset>";
        CLOSE_BOX();
    break;
    case 'insert':
        #cek data
        $str="select * from ".$dbname.".sdm_evaluasiht where tanggalevaluasi='".$tgleva."' and karyawanid='".$karyawan."'";
        $res=fetchdata($str);
        $jlhbrs=count($res);
            if($jlhbrs==0){
                $strht="insert into ".$dbname.".sdm_evaluasiht (karyawanid,tanggalevaluasi,unit,updateby) values('".$karyawan."','".$tgleva."','".$unit."','".$_SESSION['standard']['userid']."')";
                try{ 
                    $owlPDO->exec($strht); 
                }catch (PDOException $e){
                    echo " Gagal , ".addslashes($e->getMessage());
                }
            }
        $str="select * from ".$dbname.".sdm_evaluasidt where tanggalevaluasi='".$tgleva."' and karyawanid='".$karyawan."' and idjenispenilaian='".$penilaian."'";
        $res=fetchdata($str);
        $jlhbrs=count($res);
            if($jlhbrs==0){
                $strdt="insert into ".$dbname.".sdm_evaluasidt (karyawanid,tanggalevaluasi,idjenispenilaian,nilai) values('".$karyawan."','".$tgleva."','".$penilaian."','".$nilai."')";
                try{ 
                $owlPDO->exec($strdt); 
                }
                catch (PDOException $e){
                    echo " Gagal , ".addslashes($e->getMessage());
                }
            }else if($jlhbrs>0){
                $strdt="update ".$dbname.".sdm_evaluasidt set nilai='".$nilai."' where karyawanid='".$karyawan."' and tanggalevaluasi='".$tgleva."'and idjenispenilaian='".$penilaian."'";
                try{ 
                $owlPDO->exec($strdt); 
                }
                catch (PDOException $e){
                    echo " Gagal , ".addslashes($e->getMessage());
                }
            }
    break;
    case'loaddatadetail':
        echo"
        <table border=0 cellpadding=1 cellspacing=1 class=sortable>
        <thead>
        <tr class=rowheader>    
            <td align=center style=\"width:110px;\">".$_SESSION['lang']['kriteria']."</td>
            <td align=center style=\"width:320px;\">" . $_SESSION['lang']['jenis'] . " ".$_SESSION['lang']['penilaian']."</td>
            <td align=center style=\"width:25px;\">" . $_SESSION['lang']['nilai'] . "</td>
            <td align=center style=\"width:25px;\">" . $_SESSION['lang']['action'] . "</td>
        </tr>
        </thead>";
        $no = 0;
        $str="select * from ".$dbname.".sdm_evaluasidt where tanggalevaluasi='".$tglevaluasi."' and karyawanid='".$karyawan."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $no+=1;
            $whr1="idjenispenilaian='".$bar->idjenispenilaian."'";
            $optjenispenilaian=makeOption($dbname,'sdm_5kriteriapenilaian','idjenispenilaian,penilaian',$whr1);
            $skrit="select * from ".$dbname.".sdm_5jeniskriteria where kode='".substr($bar->idjenispenilaian,0,2)."'";
            $rkrit = $owlPDO->query($skrit) or die(print " Gagal: " . PDOException::getMessage());
            $rkrit->setFetchMode(PDO::FETCH_OBJ);
            $bkrit = $rkrit->fetch();
            echo"<tr class=rowcontent>   
                <td>".$bkrit->kriteria."</td>
                <td>".$optjenispenilaian[$bar->idjenispenilaian]."</td>
                <td align=center>".$bar->nilai."</td>
                <td align=center>
                    <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldt('" . tanggalnormal($bar->tanggalevaluasi). "','".$bar->karyawanid."','".$bar->idjenispenilaian."');\" >
                </td>
                </tr>";
        }
        echo "</table>";
    break;
    case 'loadData':
        //exit("Masuk");
        $footd = "";
        $where = "";
        $where = "updateby ='".$_SESSION['standard']['userid']."'";
        if ($tglevaluasicr != '') {
            $where.=" and tanggalevaluasi='" . $tglevaluasicr . "' ";
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
        $str="select * from ".$dbname.".sdm_evaluasiht where  ".$where;
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=8 align=center>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            //$optSt=makeOption($dbname,'sdm_5jenissp','kode,keterangan');
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".sdm_evaluasiht where ".$where." order by tanggalevaluasi desc limit ".$offset.",".$limit."";
            //echo $str;
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $whrKar1="karyawanid='".$bar->karyawanid."'";
                $optkaryawan=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar1);
                #pembuat
                $whrKar2="karyawanid='".$bar->updateby."'";
                $optpembuat=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
                if ($bar->status==0){
                    $status="Belum diajukan";
                }else if ($bar->status==9){
                    $status="Menunggu Persetujuan";
                }else if ($bar->status==1){
                    $status="Disetujui";
                }else{
                   $status="Ditolak";
                }
                $no+=1;
                $tab.="<tr class=rowcontent id=tr_".$no.">
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$bar->unit."</td>
                    <td>".tanggalnormal($bar->tanggalevaluasi)."</td>
                    <td>".$optkaryawan[$bar->karyawanid]."</td>
                    <td>".@$optrekomen[$bar->rekomendasi]."</td>
                    <td>".$status."</td>
                    <td>".$optpembuat[$bar->updateby]."</td>
                    <td align=right>";
                    if($bar->status==0 || $bar->status==3){
                        $tab.="<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editdt('".$bar->unit."','" . tanggalnormal($bar->tanggalevaluasi). "','".$bar->karyawanid."')\">&nbsp;";
                        $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldata('" . tanggalnormal($bar->tanggalevaluasi). "','".$bar->karyawanid."');\" >&nbsp;";
                        $tab.="<img src=images/skyblue/submit.jpg class=resicon  title='ajukan' onclick=\"form_ajukan('" . tanggalnormal($bar->tanggalevaluasi). "','".$bar->karyawanid."','".$bar->unit."','".$bar->noid."','".$no."');\" >";
                    }
                    if($bar->status==1){
                        $tab.="&nbsp<img src=images/pdf.jpg class=resicon title='".$_SESSION['lang']['pdf']."' onclick=\"previewep('" . $bar->tanggalevaluasi. "','".$bar->karyawanid."',event);\">";
                    }
                $tab.="&nbsp<img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View Detail' onclick=\"viewdetail('" . tanggalnormal($bar->tanggalevaluasi). "','".$bar->karyawanid."',event);\" >";
                $tab.="</td>";
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
                <tr><td colspan=8 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;
    case'insertht':
        $str="select tanggalmasuk,tanggalpengangkatan from ".$dbname.".datakaryawan where karyawanid='".$karyawan."'";
        $qtr = $owlPDO->query($str) or die (print "Gagal : ".PDOException::getMessage());
        $qtr->setFetchMode(PDO::FETCH_OBJ);
        $rtr=$qtr->fetch();
        $tglmsk=explode("-",$rtr->tanggalmasuk);
        $tglkeluar=explode("-",$rtr->tanggalpengangkatan);
        $slisihthn=$tglkeluar[0]-$tglmsk[0];
        //exit('warning : '.$tglmsk[0]."/".$tglkeluar[0]."/".$slisihthn);
        if ($slisihthn>=2) {
            exit('Warning : Karyawan ini telah 2 kali diperbarui kontrak');
        }
		//print_r($_POST);
        for($arDt=0;$arDt<$_POST['totRow'];$arDt++){
            if ($_POST['arrNilai'][$arDt]>6){
                exit('Warning : Nilai tidak boleh dari 5.');
            }
        }
        $str="insert into ".$dbname.".sdm_evaluasiht (karyawanid,tanggalevaluasi,unit,kekuatan, perbaikandiperlukan, catatan, rekomendasi, ttd, ttd1, ttd2, ttd3,updateby) 
        values('".$karyawan."','".$tgleva."','".$unit."','".$kekuatan."','".$perbaikan."','".$catatan."','".$rekomendasi."','".$_SESSION['standard']['userid']."','".$ttd1."','".intval($ttd2)."','".$ttd3."','".$_SESSION['standard']['userid']."')";
        try{ 
        $owlPDO->exec($str); 
            $sDet="insert into ".$dbname.".sdm_evaluasidt values ";
            for($arDt=0;$arDt<$_POST['totRow'];$arDt++){
                if($arDt==0){
                    $sDet.=" ('".$karyawan."','".$tgleva."','".$_POST['kdNilai'][$arDt]."','".$_POST['arrNilai'][$arDt]."','".$_POST['kom'][$arDt]."')";
                }else{
                    $sDet.=",('".$karyawan."','".$tgleva."','".$_POST['kdNilai'][$arDt]."','".$_POST['arrNilai'][$arDt]."','".$_POST['kom'][$arDt]."')";
                }
            }
            try{ 
                $owlPDO->exec($sDet); 
            }
            catch (PDOException $e){
            echo " Gagal ".addslashes($e->getMessage()."__".$sDet);
            }
        }
        catch (PDOException $e){
            echo " Gagal ".addslashes($e->getMessage());
        }
    break;
    case'updateht':
        // for($arDt=0;$arDt<$_POST['totRow'];$arDt++){
            // if ($_POST['arrNilai'][$arDt]>5){
                // exit('Warning : Nilai tidak boleh dari 5.');
            // }
        // }
        $str="update " . $dbname . ".sdm_evaluasiht set kekuatan='".$kekuatan."', perbaikandiperlukan='".$perbaikan."', catatan='".$catatan."', rekomendasi='".$rekomendasi."', ttd1='".$ttd1."', ttd2='".$ttd2."', ttd3='".$ttd3."' where tanggalevaluasi='".$tgleva."' and karyawanid='".$karyawan."'";
        try{ 
        $owlPDO->exec($str);
            for($arDt=0;$arDt<$_POST['totRow'];$arDt++){
                $sDet="update ".$dbname.".sdm_evaluasidt set nilai='".$_POST['arrNilai'][$arDt]."', kom='".$_POST['kom'][$arDt]."' where tanggalevaluasi='".$tgleva."' and karyawanid='".$karyawan."' and idjenispenilaian='".$_POST['kdNilai'][$arDt]."'";
                try{ 
                    $owlPDO->exec($sDet); 
                }
                catch (PDOException $e){
                echo " Gagal ".addslashes($e->getMessage()."__".$sDet);
                }
            }
        }
        catch (PDOException $e){
            echo " Gagal ".addslashes($e->getMessage());
        }
    break;
    case'delete':
        $strht = "delete from " . $dbname . ".sdm_evaluasiht where tanggalevaluasi='" . $tglevaluasi . "' and karyawanid='".$karyawan."'";
        try {
            $owlPDO->exec($strht);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }
    break;
    case 'viewdetail':
        //get data spdt dan spht
        $str="SELECT * from ".$dbname.".sdm_evaluasiht where tanggalevaluasi='".$tglevaluasi."' and karyawanid='".$karyawan."'";
        //echo $str;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar=$res->fetch();
            #karyawan
            $whrKar1="karyawanid='".$bar->karyawanid."'";
            $optkaryawan=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar1);
            #ttd
            $whrKar2="karyawanid='".$bar->ttd."'";
            $optttd=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
            #ttd 1
            $whrKar3="karyawanid='".$bar->ttd1."'";
            $optttd1=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar3); 
            #ttd 2
            $whrKar4="karyawanid='".$bar->ttd2."'";
            $optttd2=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar4);
            #ttd 3
            $whrKar5="karyawanid='".$bar->ttd3."'";
            $optttd3=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar5);     
            if ($bar->status==0){
                $status="Belum diajukan";
            }else if ($bar->status==9){
                $status="Menunggu Persetujuan";
            }else if ($bar->status==1){
                $status="Disetujui";
            }else{
               $status="Ditolak";
            }
            $tab="<legend><b>KPI - Key Performance Indicator</b></legend><br>";
            $tab.="<table align=left border=0 width=100%>
            <tr>
                <td>" . $_SESSION['lang']['unit'] . "</td>
                <td> : </td>
                <td>".$bar->unit."</td>
            </tr>
            <tr>
                <td>Tanggal Evaluasi</td>
                <td> : </td>
                <td>".tanggalnormal($bar->tanggalevaluasi)."</td>
            </tr>
            <tr>
                <td>" . $_SESSION['lang']['namakaryawan'] . "</td>
                <td> : </td>
                <td>".$optkaryawan[$bar->karyawanid]."</td>
            </tr>
            <tr>
                <td>Feed Back Management/Atasan</td>
                <td> : </td>
                <td>".$bar->kekuatan."</td>
            </tr>
            <tr style=display:none>
                <td>Perbaikan Diperlukan</td>
                <td> : </td>
                <td>".$bar->perbaikandiperlukan."</td>
            </tr>
            <tr style=display:none>
                <td>Catatan</td>
                <td> : </td>
                <td>".$bar->catatan."</td>
            </tr>
            <tr style=display:none>
                <td>Rekomendasi</td>
                <td> : </td>
                <td>".@$optrekomen[$bar->rekomendasi]."</td>
            </tr>
            <tr style=display:none>
                <td>Mengajukan</td>
                <td> : </td>
                <td>".@$optttd[$bar->ttd]."</td>
            </tr>
            <tr style=display:none>
                <td>Menyetujui</td>
                <td> : </td>
                <td>".@$optttd1[$bar->ttd1]."</td>
            </tr>
            <tr style=display:none>
                <td>HC & GA Head</td>
                <td> : </td>
                <td>".@$optttd2[$bar->ttd2]."</td>
            </tr>
            <tr style=display:none>
                <td>HC Officer</td>
                <td> : </td>
                <td>".@$optttd3[$bar->ttd3]."</td>
            </tr>
            <tr>
                <td>" . $_SESSION['lang']['status'] . "</td>
                <td> : </td>
                <td>".$status."</td>
            </tr>";
            if($bar->spersetujuan==2){
                $tab.="<tr>
                        <td>Alasan Penolakan </td>
                        <td> : </td>
                        <td>".$bar->alasanpenolakan."</td>
                       </tr>";
            }
        $tab.="<tr colspan=3>
                <td>&nbsp;</td>
            </tr>
            <tr colspan=3>
                <td><b>Kriteria Penilaian</b></td>
            </tr>
            <tr >
                <td colspan=3>
                <table border=0 cellpadding=1 width=100% cellspacing=1 class=sortable>
                <thead>
                <tr class=rowheader>    
                    <td align=center>".$_SESSION['lang']['nourut']."</td>
                    <td align=center>".$_SESSION['lang']['kriteria']."</td>
                    <td align=center>" . $_SESSION['lang']['jenis'] . " ".$_SESSION['lang']['penilaian']."</td>
                    <td align=center>" . $_SESSION['lang']['nilai'] . "</td>
                    <td align=center>" . $_SESSION['lang']['keterangan'] . "</td>
                </tr>
                </thead>";
				$nmnilai=makeOption($dbname,'sdm_nilai_penilaian','kodenil,nama');
                $no = 0;
                $str="select * from ".$dbname.".sdm_evaluasidt where tanggalevaluasi='".$tglevaluasi."' and karyawanid='".$karyawan."'";
                $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while ($bar = $res->fetch()) {
                    $no+=1;
                    $whr1="idjenispenilaian='".$bar->idjenispenilaian."'";
                    $optjenispenilaian=makeOption($dbname,'sdm_5kriteriapenilaian','idjenispenilaian,penilaian',$whr1);
                    $skrit="select * from ".$dbname.".sdm_5jeniskriteria where kode='".substr($bar->idjenispenilaian,0,2)."'";
                    $rkrit = $owlPDO->query($skrit) or die(print " Gagal: " . PDOException::getMessage());
                    $rkrit->setFetchMode(PDO::FETCH_OBJ);
                    $bkrit = $rkrit->fetch();
                    $tab.="<tr class=rowcontent>   
                        <td>".$no."</td>
                        <td>".@$bkrit->kriteria."</td>
                        <td>".$optjenispenilaian[$bar->idjenispenilaian]."</td>
                        <td align=center>".$nmnilai[$bar->nilai]."</td>
                        <td>".$bar->kom."</td>
                        </tr>";
                }
                $tab.="</table>
            </td>
            </tr>
            </table>";
        echo $tab;
    break;
    case'deldt':
        $str = "delete from " . $dbname . ".sdm_evaluasidt where tanggalevaluasi='" . $tglevaluasi . "' and karyawanid='".$karyawan."' and idjenispenilaian='".$penilaian."'";
        try {
            $owlPDO->exec($str);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
   /*  case'ajukan':
        $str = "update " . $dbname . ".sdm_evaluasiht set status='2' where tanggalevaluasi='".$tglevaluasi."' and karyawanid='".$karyawan."'";        
        try{ 
        $owlPDO->exec($str); 
        }
        catch (PDOException $e){
            echo " Gagal ".addslashes($e->getMessage());
        }
        /* $str="select * from ".$dbname.".sdm_evaluasiht where tanggalevaluasi='".$tglevaluasi."' and karyawanid='".$karyawan."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar = $res->fetch();
        $kodeorgstr=substr($_SESSION['empl']['lokasitugas'],0,2);
        //get nama  dan kode organisasi
        $snamaorg = "select namaorganisasi,kodeorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$kodeorgstr."%' and tipe='PT'";
        $qnamaorg = $owlPDO->query($snamaorg) or die (print "Gagal : ".PDOException::getMessage());
        $qnamaorg->setFetchMode(PDO::FETCH_OBJ);
        $rnamaorg=$qnamaorg->fetch();
        $namaorg=$rnamaorg->namaorganisasi;
        if ($bar->ttd1!=''){
            $to = getUserEmail($bar->ttd1);
            $namakaryawan = getNamaKaryawan($karyawan);
            $namapengaju = getNamaKaryawan($_SESSION['standard']['userid']);
            $subject = "[Notifikasi]Pengajuan Evaluasi Masa Percobaan a/n " . $namakaryawan;
            $body = "<html>
                        <head>
                         <body>
                           <dd>Dengan Hormat,</dd><br>
                           <br>
                           Pada hari ini, tanggal " . date('d-m-Y') . " karyawan a/n  ".$namapengaju." mengajukan Evaluasi Masa Percobaan kepada $namakaryawan <br>
                           <br>
                           <br>
                           Untuk melihat detail dan melakukan persetujuan silahkan lakukan di menu SDM->Transaksi->Persetujuan Penilaian Karyawan
                           <br>
                           <br>
                           Regards,<br>
                           Owl-Plantation System.
                         </body>
                        </head>
                     </html>";
            $kirim = kirimEmail($to, '', $subject, $body);
        } */
    //break; 
	
	case 'getkaryawan':
		//get karyawan
		$optkar = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$wh=" and tanggalkeluar = '0000-00-00'";
		if (substr($_SESSION['empl']['lokasitugas'], 2, 2) == 'HO') {
			$str = " select karyawanid,namakaryawan,lokasitugas,statuskaryawan from " . $dbname . ".datakaryawan
			where 1=1 ".$wh." order by namakaryawan";// Rubah ke Tipe karyawan PB keatas //  
		}else{
			$str = " select karyawanid,namakaryawan,lokasitugas,statuskaryawan from " . $dbname . ".datakaryawan
			where lokasitugas='" . $_SESSION['empl']['lokasitugas'] . "' ".$wh." order by namakaryawan";
		}
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$optkar.="<option value='" . $bar->karyawanid . "'>" . $bar->namakaryawan . " | " .$bar->statuskaryawan. " | " . $bar->lokasitugas . "</option>";
		}
	break;
	case'editdtnilai':
		$str="select * from ".$dbname.".sdm_evaluasidt where karyawanid='".$karyawan."' and tanggalevaluasi='".$tglevaluasi."'"; //exit('error'.$str);
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			@$karyawan.=$bar['karyawanid'];
			@$tanggalevaluasi.=$bar['tanggalevaluasi'];
			@$idjenispenilaian.=$bar['idjenispenilaian'];
			@$nilai.=$bar['nilai'];
			@$kom.=$bar['kom'];
			$d['idjenispenilaian'][] = $bar['idjenispenilaian'];
			$d['nilai'][] 			 = $bar['nilai'];
			$d['kom'][] 			 = $bar['kom'];
		}
		//echo $karyawan."####".$tanggalevaluasi."####".$idjenispenilaian."####".$nilai."####".$kom;
		echo json_encode($d);
		//exit('exit');
	break;
	case'form_ajukan';
		$str="select distinct a.karyawanid,b.namakaryawan,b.lokasitugas from ".$dbname.".setup_approval a 
				  left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid where 
				  a.karyawanid!='".$_SESSION['standard']['userid']."' and a.jenispersetujuan='KPI' and a.level='1' and a.kodeunit='".$unit."'  order by b.namakaryawan asc";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$optKry="";
		while($rkry=$res->fetch()){
			$optKry.="<option value='".$rkry['karyawanid']."'>".$rkry['namakaryawan']." [".$rkry['lokasitugas']."]</option>";
		}
	$nmkary=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$karyawan."'");
	$tab = "<table cellspacing=1 border=0 width=100%>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['namakaryawan'] . "</td>
					<td width=5px>:</td>
					<td hidden id=noid>".$noid."</td>
					<td hidden id=form_karyawan>".$karyawan."</td>
					<td >".$nmkary[$karyawan]."</td>
				</tr>
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['tanggal'] . "</td>
					<td width=5px>:</td>
					<td id=form_tglevaluasi>".tanggalnormal($tglevaluasi)."</td>
				</tr>
				
				<tr class=rowcontent>
					<td width=100px>" . $_SESSION['lang']['kepada'] . "</td>
					<td width=5px>:</td>
					<td><select id=kepada style='width:100%;'>".$optKry."</select></td>
				</tr>
				<tr class=rowcontent>
					<td></td><td><input id=numrow style=display:none value=".$numrow."></td>
					<td align=LEFT><button id=tomboldetail class=mybutton onclick=ajukan()>" . $_SESSION['lang']['diajukan'] . "</button></td>
				</tr>				
				</table>";
		
        echo $tab;
	break;
	case'ajukan':
		if($kepada==''){
			exit('Error : Isikan nama penyetuju.');
		}
		# update flag menjadi 1
        $str = "update " . $dbname . ".sdm_evaluasiht set status='9', spersetujuan='9',"."updateby='" . $_SESSION['standard']['userid'] . "' where karyawanid = '" . $karyawan . "' and tanggalevaluasi='".$tglevaluasi."'";
        try {
            $owlPDO->exec($str);
			# insert ke table approval
			$str = "insert into " . $dbname . ".approval (`nourut`,`notransaksi`, `jenispersetujuan`, 
					`level`, `karyawanid`, `status`, `komentar`, `keterangan`, `tanggal`)
					 values ('','".$noid."','KPI','1','" . $kepada."','0','','','')";
			try {$owlPDO->exec($str);
			
			} catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}			
        } catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n";die();}
		
        
	break;
	default:
		# code...
		break;
}
?>