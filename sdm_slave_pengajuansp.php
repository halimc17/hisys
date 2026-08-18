<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('lib/fpdf.php');
include_once('lib/zFunction.php');

$method = checkPostGet('method', '');
$jenissurat = checkPostGet('jenissurat', '');
$fileupload = checkPostGet('fileupload', '');
$jenispelanggaran = checkPostGet('jenispelanggaran', '');
$pelanggaran = checkPostGet('pelanggaran', '');
$kodeorg = checkPostGet('kodeorg', '');
$karyawan = checkPostGet('karyawan', '');
$persetujuan1 = checkPostGet('persetujuan1', '');
$persetujuan2 = checkPostGet('persetujuan2', '');
$nopengajuan = checkPostGet('nopengajuan', '');
$nopengajuancr = checkPostGet('nopengajuancr', '');
$tglpengajuan = tanggalsystem(checkPostGet('tglpengajuan', ''));
$tglcr = tanggalsystem(checkPostGet('tglcr', ''));
$mendengar = checkPostGet('mendengar', '');
$keterangan = checkPostGet('keterangan', '');
$pembuat = checkPostGet('pembuat', '');
$sifatpelanggaran = checkPostGet('sifatpelanggaran', '');
$id = checkPostGet('id', '');
$sanksipelanggaran = checkPostGet('sanksipelanggaran', '');
$jenissurat = checkPostGet('jenissurat', '');
$tembusan = checkPostGet('tembusan', '');
$tanggaldari = tanggalsystemn(checkPostGet('tanggaldari', ''));
$tanggalsampai = tanggalsystemn(checkPostGet('tanggalsampai', ''));
$per['persetujuan1']=checkPostGet('persetujuan1', '');
$per['persetujuan2']=checkPostGet('persetujuan2', '');
$jenispersetujuan='SP';

$path	= "fileupload/berkaspelanggaran/";

switch ($method) {
	case 'getjenispel':
		# code...
		$optjenispel="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		$str="select * from ".$dbname.".sdm_5jenispelanggaran where kode='".$jenissurat."'";
		$qtr=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$qtr->setFetchMode(PDO::FETCH_OBJ);
		while ($rtr=$qtr->fetch()) {
			# code...
			if($jenispelanggaran==$rtr->idjenispelanggaran){
				$optjenispel.="<option value='".$rtr->idjenispelanggaran."' selected>".$rtr->idjenispelanggaran."</option>";	
			}else{
				$optjenispel.="<option value='".$rtr->idjenispelanggaran."'>".$rtr->idjenispelanggaran."</option>";
			}
		}	
		echo $optjenispel;
	break;

	case 'getpelanggaran':
		# code...
		$optjenispel='';
		$str="select * from ".$dbname.".sdm_5jenispelanggaran where idjenispelanggaran='".$jenispelanggaran."'";
		$qtr=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$qtr->setFetchMode(PDO::FETCH_OBJ);
		while ($rtr=$qtr->fetch()) {
			# code...
			$pelanggaran=$rtr->pelanggaran;
		}	
		echo $pelanggaran;
	break;

    case 'getkar':

        $str="select tipekaryawan from ".$dbname.".datakaryawan where karyawanid='".$pembuat."' ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar=$res->fetch();
        $tipekar=$bar->tipekaryawan;

        if ($tipekar==0) {
            $whr=" and tipekaryawan in (1,4,6,5)";
        } else if ($tipekar==9) {
            $whr=" and tipekaryawan=0";
        } else if ($tipekar==10) {
            $whr=" and tipekaryawan in (0,9)";
        }
    
        $tip=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');
        $str = " select karyawanid,namakaryawan,bagian,subbagian,lokasitugas,tipekaryawan from " . $dbname . ".datakaryawan
           where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '" . date("Y-m-d") . "') and lokasitugas='".$kodeorg."' ".$whr;
        // exit('warning : '.$str);
        $optkar = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            if($karyawan==$bar->karyawanid){
                $optkar.="<option value='" . $bar->karyawanid . "' selected>" . $bar->namakaryawan . " | " . $tip[$bar->tipekaryawan] . " | " . $bar->lokasitugas . " | " . $bar->subbagian . "</option>";   
            }else{
                $optkar.="<option value='" . $bar->karyawanid . "'>" . $bar->namakaryawan . " | " . $tip[$bar->tipekaryawan] . " | " . $bar->lokasitugas . " | " . $bar->subbagian . "</option>";
            }
        }   

        //get pembuat
        $str = " select karyawanid,namakaryawan,bagian,subbagian,lokasitugas,tipekaryawan from " . $dbname . ".datakaryawan
           where (tanggalkeluar = '0000-00-00' or tanggalkeluar>= '" . date("Y-m-d") . "') and lokasitugas='".$kodeorg."' and tipekaryawan in (0,9,10)";
        $optpembuat = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while($bar=$res->fetch())
        {
            if($pembuat==$bar->karyawanid){
                $optpembuat.="<option value='" . $bar->karyawanid . "' selected>" . $bar->namakaryawan . " | " . $tip[$bar->tipekaryawan] . " | " . $bar->lokasitugas . " | " . $bar->subbagian . "</option>";   
            }else{
                $optpembuat.="<option value='" . $bar->karyawanid . "'>" . $bar->namakaryawan . " | " . $tip[$bar->tipekaryawan] . " | " . $bar->lokasitugas . " | " . $bar->subbagian . "</option>";
            }
        }

        echo $optkar."##".$optpembuat;
    break;

    case 'savepengajuan':
        $bulan = substr($tglpengajuan, 4, 2);
        $tahun = substr($tglpengajuan, 0, 4);
        $arrayRomawi = array("I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX", "X", "XI", "XII");
        $resultRomawi = $arrayRomawi[(int) $bulan - 1];

        $str="select induk from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";
        $qtr=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $qtr->setFetchMode(PDO::FETCH_OBJ);
        $rtr=$qtr->fetch();
        $kodept=$rtr->induk;

        $whr="";
        if (substr($jenissurat,0,2)=='SP') {
            $surat='SP';
            $whr=" and left(jenissurat,2)='".$surat."'";
        }else if (substr($jenissurat,0,2)=='PH') {
            $surat='SPB';
            $whr=" and substr(nopengajuan,5,3)='".$surat."'";
        }else{
            $surat='ST';
            $whr=" and substr(nopengajuan,5,2)='".$surat."'";
        }


        //Nomor : 001/SP/GAL/VII/2017 no dibuat per PT dan reset nomor per tahun
        $strCount = "select left(nopengajuan,3) as nourut from " . $dbname . ".`sdm_pengajuanspht` where year(tanggalpengajuan)='" . $tahun . "' and kodept='".$kodept."' ".$whr."  order by nopengajuan desc limit 1";
        // exit('warning : '.$strCount);
        $rData=fetchData($strCount);
            if(intval($rData[0]['nourut'])==0){
                $nourut=addZero(1,3);
            }else{
                $nourut=addZero((intval($rData[0]['nourut'])+1),3);
            }

        if ($jenissurat=='PHK') {
            $nopengajuan=$nourut."/".$surat."/".$kodept."/HRD"."/".$resultRomawi."/".$tahun; 
        }else{
           $nopengajuan=$nourut."/".$surat."/".$kodept."/".$resultRomawi."/".$tahun; 
        }
        

        $strht = "insert into " . $dbname . ".sdm_pengajuanspht (nopengajuan,karyawanid,tanggalpengajuan,kodeorg,updateby,pembuat,sifatpelanggaran,tanggaldari,tanggalsampai,kodept,jenissurat) values ('".$nopengajuan."','".$karyawan."','".$tglpengajuan."','".$kodeorg."','".$_SESSION['standard']['userid']."','".$pembuat."','".$sifatpelanggaran."','".$tanggaldari."','".$tanggalsampai."','".$kodept."','".$jenissurat."')";
        try
        {
            $owlPDO->exec($strht);
        }
        catch (PDOException $e)
        {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        echo $nopengajuan;

    break;

    case 'detail':
        //get jenis surat peringatan
        $optjenispel="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str="select * from ".$dbname.".sdm_5jenispelanggaran where kode='".$jenissurat."'";
        $qtr=$owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $qtr->setFetchMode(PDO::FETCH_OBJ);
        while ($rtr=$qtr->fetch()){
            $optjenispel.="<option value='".$rtr->idjenispelanggaran."'>".$rtr->idjenispelanggaran."</option>";
        }   

        OPEN_BOX();
        echo "
        <fieldset>
            <legend>".$_SESSION['lang']['detail']." ".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['pelanggaran']."</legend>
            <table>
            <tr>
            <td style='vertical-align:top;'>
                <table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>
                <thead>
                <tr class=rowheader>    
                    <td align=center>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['pelanggaran']."</td>
                    <td align=center>".$_SESSION['lang']['pelanggaran']."</td>
                    <td align=center>".$_SESSION['lang']['action']."</td>
                </tr>
                </thead>
                <tbody>
                <tr class=rowcontent>
                    <td style='vertical-align:top;' style='width:100px'><select id=jenispelanggaran onchange=getpelanggaran() style='width:120px'>".$optjenispel."</select></td>
                    <td id=pelanggaran style=width:500px></td>
                    <td align=center style='vertical-align:top;'>
                    <input type=hidden id=method value='insertdt'>
                    <input type=hidden id=nopengajuandt value='".$nopengajuan."'>
                        <img title='" . $_SESSION['lang']['save'] . "' class=zImgBtn onclick=savedetail() src='images/save.png'/>
                    </td>
                </tr>
                </tbody>
                </table>
            </td>
        
            <td style='vertical-align:top;'>
                <table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>
                <thead>
                <tr class=rowheader>    
                    <td align=center>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['sanksipelanggaran']."</td>
                    <td align=center>".$_SESSION['lang']['action']."</td>
                </tr>
                </thead>
                <tbody>
                <tr class=rowcontent>
                    <td><textarea id=sanksipelanggaran onkeypress=\"return tanpa_kutip(event);\" cols=55 rows=1 ></textarea></td>
                    <td align=center>
                    <input type=hidden id='id' value=''>
                    <input type=hidden id=methodsp value='insertsp'>
                        <img title='".$_SESSION['lang']['save']."' class=zImgBtn onclick=savedetailsp() src='images/save.png'/>
                    </td>
                </tr>
                </tbody>
                </table>
            </td>
            </tr>
            <tr>
            <td align=left style='vertical-align:top;'>
                <div id=loaddatadetail>
                    <script>loaddatadetail()</script>
                </div>
            </td>
            <td align=left style='vertical-align:top;'>
                <div id=loaddatadetailsp>
                    <script>loaddatadetailsp()</script>
                </div>
            </td>
            </tr>
            <table>
            <button id=selesai class=mybutton onclick=displayList()>".$_SESSION['lang']['done']."</button>
        </fieldset>";
        
    break;

    case 'insertdt':
        $str="select * from ".$dbname.".sdm_pengajuanspdt where nopengajuan='".$nopengajuan."' and idjenispelanggaran='".$jenispelanggaran."'";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs>0){
            exit("Warning : jenis pelanggaran telah diinput");
        }else{
            $strdt = "insert into " . $dbname . ".sdm_pengajuanspdt (nopengajuan,idjenispelanggaran) values ('".$nopengajuan."','".$jenispelanggaran."')";
            try
            {
                $owlPDO->exec($strdt);
            }
            catch (PDOException $e)
            {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }
    break;

    case'loaddatadetail':
        echo"
        <table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>
        <thead>
        <tr class=rowheader>    
            <td align=center width=30px>".$_SESSION['lang']['nourut']."</td>
            <td align=center width=500px>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['pelanggaran']."</td>
            <td align=center width=30px>".$_SESSION['lang']['action']."</td>
        </tr>
        </thead>";

        $no = 0;
        $str="select * from ".$dbname.".sdm_pengajuanspdt where nopengajuan='".$nopengajuan."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $idjenispelanggaran=$bar->idjenispelanggaran;
            $no+=1;
            $skrit="select * from ".$dbname.".sdm_5jenispelanggaran where idjenispelanggaran='".$idjenispelanggaran."'";
            $rkrit = $owlPDO->query($skrit) or die(print " Gagal: " . PDOException::getMessage());
            $rkrit->setFetchMode(PDO::FETCH_OBJ);
            $bkrit = $rkrit->fetch();
            echo"<tr class=rowcontent>   
                <td align=center>".$no."</td>
                <td >".$bkrit->pelanggaran."</td>
                <td align=center>
                    <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldt('" .$nopengajuan. "','".$idjenispelanggaran."');\" >
                </td>
                </tr>";
        }
        echo "</table>";
    break;

    case 'insertsp':

        $strdt = "insert into " . $dbname . ".sdm_sanksipelanggaransp (nopengajuan,sanksipelanggaran) values ('".$nopengajuan."','".$sanksipelanggaran."')";
        try
        {
            $owlPDO->exec($strdt);
        }
        catch (PDOException $e)
        {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

    break;

    case'loaddatadetailsp':
        echo"
        <table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>
        <thead>
        <tr class=rowheader>    
            <td align=center style='width:30px'>".$_SESSION['lang']['nourut']."</td>
            <td align=center>".$_SESSION['lang']['jenis']." ".$_SESSION['lang']['sanksipelanggaran']."</td>
            <td align=center style='width:30px'>".$_SESSION['lang']['action']."</td>
        </tr>
        </thead>";

        $no = 0;
        $str="select * from ".$dbname.".sdm_sanksipelanggaransp where nopengajuan='".$nopengajuan."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $no+=1;
            echo"<tr class=rowcontent>   
                <td align=center>".$no."</td>
                <td >".$bar->sanksipelanggaran."</td>
                <td align=center>
                    <img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"editsp('".$bar->id."','".$bar->sanksipelanggaran."')\">
                    <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delsp('" .$nopengajuan. "','".$bar->id."');\" >
                </td>
                </tr>";
        }
        echo "</table>";
    break;

    case'updatesp':

        $strsp="update ".$dbname.".sdm_sanksipelanggaransp set sanksipelanggaran='".$sanksipelanggaran."' where nopengajuan='".$nopengajuan."' and id='".$id."'";
        try{
            $owlPDO->exec($strsp);
        }catch (PDOException $e){
            echo "Gagal : ".$e->getMessage();
            die();
        }
    break;


    case'delsp':

        $strht = "delete from " . $dbname . ".sdm_sanksipelanggaransp where nopengajuan='" . $nopengajuan . "' and id='".$id."'";
        try {
            $owlPDO->exec($strht);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

    break;

    case 'formpersetujuan':
        //Check data dt
        $str="select * from ".$dbname.".sdm_pengajuanspdt where nopengajuan='".$nopengajuan."'";
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs<=0){
            exit('Warning : Data Pelanggaran harus dipilih.');
        }

        //get data edit
        $str="select * from ".$dbname.".sdm_pengajuanspht where nopengajuan='".$nopengajuan."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar = $res->fetch();

        //get tipekar
        $strtk="select tipekaryawan from ".$dbname.".datakaryawan where karyawanid='".$bar->karyawanid."'";
        $restk=$owlPDO->query($strtk) or die(print " Gagal: " . PDOException::getMessage());
        $restk->setFetchMode(PDO::FETCH_OBJ);
        $bartk=$restk->fetch();
        $tipekar=$bartk->tipekaryawan;

        $str1="select * from ".$dbname.".approval where notransaksi='".$bar->nopengajuan."'";
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_ASSOC);
        while($bar1=$res1->fetch()){
            $per['persetujuan'.$bar1['level']]=$bar1['karyawanid'];
        }

        ##persetujuan1
        #$optmanager=$optHRD="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str="select a.namakaryawan,a.nik,b.karyawanid,a.lokasitugas from ".$dbname.".datakaryawan a left join ".$dbname.".setup_approval b on a.karyawanid=b.karyawanid where b.kodeunit='".$kodeorg."' and b.jenispersetujuan='SP' and b.level='1' order by a.namakaryawan";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
        {
            if($per['persetujuan1']==$bar['karyawanid']){
                @$optHRD.="<option value=".$bar['karyawanid']." selected>".$bar['namakaryawan']."</option>";
            }else{
                @$optHRD.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']." - ".$bar['nik']."</option>";
            }
            
        }


        ##persetujuan2
        $str="select a.namakaryawan,a.nik,b.karyawanid,a.lokasitugas from ".$dbname.".datakaryawan a left join ".$dbname.".setup_approval b on a.karyawanid=b.karyawanid where b.kodeunit='".$kodeorg."' and b.jenispersetujuan='SP' and b.level='2' order by a.namakaryawan";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
        {
            if($per['persetujuan2']==$bar['karyawanid']){
                @$optmanager.="<option value=".$bar['karyawanid']." selected>".$bar['namakaryawan']." - ".$bar['nik']." - ".$bar['lokasitugas']."</option>";
            }else{
                @$optmanager.="<option value='".$bar['karyawanid']."'>".$bar['namakaryawan']." - ".$bar['nik']."</option>";
            }
            
        }

        if ($tipekar==0 or $tipekar==9 or $tipekar==10 or $tipekar==7 or $tipekar==8) {
            $disab="disabled";
            $dis="";
        }else{
            $disab="";
            $dis="disabled";
        }
        echo "
        <fieldset>
        <legend>".$_SESSION['lang']['persetujuan']."</legend>
        <table >
            <tr>
                <td><label>Kepada</label></td>
                <td>:</td>
                <td><select style=width:225px  id=persetujuan1 >".$optHRD."</td>
            </tr>
            <tr hidden>
                <td><label>Manager / Div Head</label></td>
                <td>:</td>
                <td><select style=width:225px  id=persetujuan2 ".$disab.">".$optmanager."</td>
            </tr>
            <tr hidden>
                <td>Upload</td>
                <td>:</td>
                <td><form id=frmUpload enctype=multipart/form-data method=post >
                    <input type='file' name='upload' id='upload' style=width:225px ></form>
                </td>
            </tr>
            <tr>
                <table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:30%'>
                <thead>
                <tr class=rowheader>    
                    <td align=center>".$_SESSION['lang']['tembusan']."</td>
                    <td align=center>".$_SESSION['lang']['action']."</td>
                </tr>
                </thead>
                <tr class=rowcontent>
                    <td><input type=text id='tembusan' class=myinputtext style='width:270px;'></td>
                    <td align=center>
                    <input type=hidden id='idtemb' value=''>
                    <input type=hidden id=methodtemb value='inserttemb'>
                        <img title='".$_SESSION['lang']['save']."' class=zImgBtn onclick=savedetailtemb() src='images/save.png'/>
                    </td>
                </tr>
                </table>
                <hr>
                <div id=loaddatadetailtemb>
                    <script>loaddatadetailtemb()</script>
                </div>
            </tr>
            <br>
            <input type=hidden value='insertht' id=methodht>
            <input type=hidden value='".$nopengajuan."' id=nopengajuanx>
            <table width=100%>
			<tr>
                <td align=center colspan=5>
                    <button class=mybutton id=tombolsimpanhtx onclick=simpan()>Ajukan</button>
                </td>
            </tr></table>
        </table>
        </fieldset>";
    break;
	
	case 'insertht':
		/* $nmfile = str_replace("/","",$nopengajuan);
        if($fileupload!=''){
            if($_FILES['file']['error']==0){
                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                $filename = $nmfile."".$filetype;
				$file_tmpname = file_get_contents($_FILES['file']['tmp_name']);	

                if($filetype=='.pdf'){
					if (!file_exists($path)) {
						mkdir($path, 0777, true);
					}
					file_put_contents($path.$filename,$file_tmpname);
                }else{
                    exit("Warning : Format file upload harus .pdf");
                }
            }
        }
		
		$str="select * from ".$dbname.".sdm_pengajuanspht where nopengajuan='".$nopengajuan."'";
		$res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		$bar = $res->fetch();
		$path=$bar->file_pendukung;
		*/
		
		$strht = "update " . $dbname . ".sdm_pengajuanspht set ajukan='1',updateby='".$_SESSION['standard']['userid']."' where nopengajuan='".$nopengajuan."'";   //exit("error".$strht);             
		try{
			$owlPDO->exec($strht);
		}catch (PDOException $e){
			print " Gagal  !: " . $e->getMessage() . "\n";
			die();
		} 

        $strht = "delete from " . $dbname . ".approval where notransaksi='" . $nopengajuan . "'";
        try {
            $owlPDO->exec($strht);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

		$str="insert into ".$dbname.".approval (`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`) values 
			  ('".$nopengajuan."','".$jenispersetujuan."','1','".$per['persetujuan1']."')";
		try{
			$owlPDO->exec($str); 
		}catch(PDOException $e){
			echo " Gagal," . addslashes($e->getMessage());
		}

    break;

	case'update':

		$strht="update ".$dbname.".sdm_pengajuanspht set karyawanid='".$karyawan."', persetujuan1='".$persetujuan1."', persetujuan2='".$persetujuan2."' where nopengajuan='".$nopengajuan."'";
		try{
			$owlPDO->exec($strht);
		}catch (PDOException $e){
			echo "Gagal : ".$e->getMessage();
			die();
		}
	break;

    case'delete':

        $strht = "delete from " . $dbname . ".sdm_pengajuanspht where nopengajuan='" . $nopengajuan . "'";
        try {
            $owlPDO->exec($strht);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

    break;

    case'deldt':

        $strht = "delete from " . $dbname . ".sdm_pengajuanspdt where nopengajuan='" . $nopengajuan . "' and idjenispelanggaran='".$jenispelanggaran."'";
        try {
            $owlPDO->exec($strht);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

    break;

    case 'viewdetail':
		$nmkar = makeOption($dbname, 'datakaryawan', 'karyawanid,namakaryawan');
        //get data spdt dan spht
        $str="SELECT a.*, b.* from ".$dbname.".sdm_pengajuanspht a left join ".$dbname.".sdm_pengajuanspdt b on a.nopengajuan=b.nopengajuan where a.nopengajuan='".$nopengajuan."'";
        //echo $str;
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        $bar=$res->fetch();

        //get data surat
        $ssp="SELECT * from ".$dbname.".sdm_5jenissp where kode='".substr($bar->idjenispelanggaran,0,3)."'";
        $qsp=$owlPDO->query($ssp) or die(print " Gagal: ".PDOException::getMessage());
        $qsp->setFetchMode(PDO::FETCH_OBJ);
        $rsp=$qsp->fetch();

        $str1="select * from ".$dbname.".approval where notransaksi='".$nopengajuan."'";
        $res1=$owlPDO->query($str1) or die(print " Gagal: ".PDOException::getMessage());
        $res1->setFetchMode(PDO::FETCH_ASSOC);
        while($bar1=$res1->fetch()){
            $per['persetujuan'.$bar1['level']]=$bar1['karyawanid'];
            $alasan['persetujuan'.$bar1['level']]=$bar1['komentar'];
        }

        //get data jenis pelanggaran
        $sjp="SELECT a.*, b.pelanggaran from ".$dbname.".sdm_pengajuanspdt a left join ".$dbname.".sdm_5jenispelanggaran b on a.idjenispelanggaran=b.idjenispelanggaran where a.nopengajuan='".$nopengajuan."'";
        $qjp=$owlPDO->query($sjp) or die(print " Gagal: ".PDOException::getMessage());
        $qjp->setFetchMode(PDO::FETCH_OBJ);
        while ($rjp=$qjp->fetch()) {
            $no+=1;
            $pelanggaran.="<li>".$rjp->pelanggaran."</li>";
        }

            #karyawan
            $whrKar1="karyawanid='".$bar->karyawanid."'";
            $optkaryawan=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar1);
            #persetujuan 1
            $whrKar2="karyawanid='".$per['persetujuan1']."'";
            $optpersetujuan1=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
            #persetujuan 1
            $whrKar3="karyawanid='".$per['persetujuan2']."'";
            $optpersetujuan2=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar3);    
            if ($bar->statuspersetujuan==0){
                $status="Menunggu Persetujuan";
            }else if ($bar->statuspersetujuan==1){
                $status="Disetujui";
            }else{
               $status="Ditolak";
            }
            $tab="<legend><b>DETAIL SURAT PENGAJUAN</b></legend><br>";
            $tab.="<table align=left border=0>
            <tr>
                <td style=width:150px;>" . $_SESSION['lang']['nopengajuan'] . "</td>
                <td> : </td>
                <td>".$bar->nopengajuan."</td>
            </tr>
            <tr>
                <td style=width:150px;>" . $_SESSION['lang']['tanggalpengajuan'] . "</td>
                <td> : </td>
                <td>".tanggalnormal($bar->tanggalpengajuan)."</td>
            </tr>
            <tr>
                <td style=width:150px;>" . $_SESSION['lang']['namakaryawan'] . "</td>
                <td> : </td>
                <td>".$optkaryawan[$bar->karyawanid]."</td>
            </tr>
            <tr>
                <td style=width:150px;>" . $_SESSION['lang']['jenis'] . " " . $_SESSION['lang']['surat'] . "</td>
                <td> : </td>
                <td>".$rsp->keterangan."</td>
            </tr>
            <tr>
                <td style='vertical-align:top;=width:150px;'>" . $_SESSION['lang']['jenis'] . " " . $_SESSION['lang']['pelanggaran'] . "</td>
                <td style='vertical-align:top'> : </td>
                <td style='vertical-align:top;text-align:justify'><ol type='1'>".$pelanggaran."</ol></td>
            </tr>
            ";
            $tab.="</table>";
			
			$arrHsl=array("0"=>$_SESSION['lang']['wait_approval'],"1"=>$_SESSION['lang']['disetujui'],"2"=>$_SESSION['lang']['koreksi'],"3"=>$_SESSION['lang']['ditolak']);

			$str=" select * from ".$dbname.".sdm_pengajuanspht where  nopengajuan='".$nopengajuan."' ";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			$bar=$res->fetch();
		
			$countApprove = getCountApproval('SP',$bar['kodeorg']);
			$tab.= "<table border=0 cellspacing=1 class=sortable width=100%>
				<thead>
				<tr style='font-weight:bold'>
					<td style='text-align:center'>".$_SESSION['lang']['dbuat_oleh']."</td>";
					for($i=1;$i<=$countApprove;$i++){
						$tab.= "<td style='text-align:center'>".$_SESSION['lang']['persetujuan']." ".$i."</td>";
					}
					
			$tab.= "
				</tr>
				</thead>
				<tbody>";
				$tab.= "<tr class=rowcontent>
						<td>".$nmkar[$bar['updateby']]."<br>
							".$bar['updatetime']."</td>";
					for($i=1;$i<=$countApprove;$i++){
						$arrApp = detailApprove($i,$nopengajuan,'SP');
						
						if($arrApp['tanggal']==''||$arrApp['tanggal']=='0000-00-00 00:00:00'){
							$tngl='';
						}else{
							$tngl=tanggalnormal($arrApp['tanggal']);
						}
						if(($arrApp['karyawanid']!='')&&($arrApp['karyawanid']!=0)){
							$tab.= "<td>".$arrApp['nama']."
								<br />".$arrHsl[$arrApp['status']]."
								<br>".$tngl."
							</td>";
						}else{
							$tab.= "<td>&nbsp;</td>";
						}
					}
					
				
				$tab.= "</tbody>
				</table>";
			
        echo $tab;
    break;

    case 'loadData':
        $where = "";
        $where = "(updateby ='".$_SESSION['standard']['userid']."' or pembuat ='".$_SESSION['standard']['userid']."')";
        if ($nopengajuancr != '') {
            $where.=" and nopengajuan like '%" . $nopengajuancr . "%' ";
        }
        if ($tglcr != '') {
            $where.=" and tanggalpengajuan='" . $tglcr . "' ";
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
        $str="select * from ".$dbname.".sdm_pengajuanspht where ".$where;
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=7>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
            $optSt=makeOption($dbname,'sdm_5jenissp','kode,keterangan');
            $no=$maxdisplay;
            $str="SELECT * from ".$dbname.".sdm_pengajuanspht where ".$where." order by tanggalpengajuan desc limit ".$offset.",".$limit."";
            $tab="";
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_OBJ);
            while($bar=$res->fetch()){
                $whrKar1="karyawanid='".$bar->karyawanid."'";
                $optkaryawan=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar1);
                #pembuat
                $whrKar2="karyawanid='".$bar->pembuat."'";
                $optpembuat=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$whrKar2);
                
                if ($bar->statuspersetujuan==0){
                    $status="Menunggu Persetujuan";
                }else if ($bar->statuspersetujuan==1){
                    $status="Disetujui";
                }else{
                    $status="Ditolak";
                }

                $sdt="SELECT * from ".$dbname.".sdm_pengajuanspdt where nopengajuan='".$bar->nopengajuan."'";
                $rdt=$owlPDO->query($sdt) or die(print " Gagal: ".PDOException::getMessage());
                $rdt->setFetchMode(PDO::FETCH_OBJ);
                $bdt=$rdt->fetch();
                $no+=1;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center;'>".$no."</td>
                    <td>".$bar->nopengajuan."</td>
                    <td>".tanggalnormal($bar->tanggalpengajuan)."</td>
                    <td>".$optkaryawan[$bar->karyawanid]."</td>
                    <td>".$optpembuat[$bar->pembuat]."</td>
                    <td align=center>".$status."</td>
                    <td align=center>";
                    if ($bar->ajukan==0){
                        $tab.="<img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"edit('".$bar->nopengajuan."','".$bar->kodeorg."','".$bar->karyawanid."','".tanggalnormal($bar->tanggalpengajuan)."','".$bar->jenissurat."','".$bar->pembuat."','".$bar->sifatpelanggaran."','".$bar->tanggaldari."','".$bar->tanggalsampai."')\">
                               <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"del('" . $bar->nopengajuan. "');\" >";
						$tab.=" <img src=images/skyblue/submit.jpg class='resicon' title='Ajukan' onclick=formpersetujuan('".$bar->nopengajuan."','".$bar->kodeorg."')>";
                    }
                    if ($bar->ajukan==1){
                        $tab.="<img src=images/pdf.jpg class=resicon title='".$_SESSION['lang']['pdf']."' onclick=\"previewsp('".$bar->nopengajuan."',event);\">";
                    }
                $tab.="&nbsp;<img src=images/skyblue/zoom.png class=resicon class=zImgBtn height='30'  title='View Detail' onclick=\"viewdetail('" . $bar->nopengajuan. "',event);\" >";
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
                <tr><td colspan=7 align=center>
                <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
    break;


    case 'inserttemb':

        $strdt = "insert into " . $dbname . ".sdm_tembusansp (nopengajuan,tembusan) values ('".$nopengajuan."','".$tembusan."')";
		try
        {
            $owlPDO->exec($strdt);
        }
        catch (PDOException $e)
        {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

    break;

    case'loaddatadetailtemb':
        echo"
        <table border=0 cellpadding=1 cellspacing=1 class=sortable style='width:100%'>
        <thead>
        <tr class=rowheader>    
            <td align=center style='width:30px'>".$_SESSION['lang']['nourut']."</td>
            <td align=center>".$_SESSION['lang']['tembusan']."</td>
            <td align=center style='width:30px'>".$_SESSION['lang']['action']."</td>
        </tr>
        </thead>";

        $no = 0;
        $str="select * from ".$dbname.".sdm_tembusansp where nopengajuan='".$nopengajuan."'";
        $res = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_OBJ);
        while ($bar = $res->fetch()) {
            $no+=1;
            echo"<tr class=rowcontent>   
                <td align=center>".$no."</td>
                <td >".$bar->tembusan."</td>
                <td align=center>
                    <img src='images/skyblue/edit.png' class='resicon' title='Edit' onclick=\"edittemb('".$bar->id."','".$bar->tembusan."')\">
                    <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deltemb('" .$nopengajuan. "','".$bar->id."');\" >
                </td>
                </tr>";
        }
        echo "</table>";
    break;

    case'updatetemb':

        $strsp="update ".$dbname.".sdm_tembusansp set tembusan='".$tembusan."' where nopengajuan='".$nopengajuan."' and id='".$id."'";
        try{
            $owlPDO->exec($strsp);
        }catch (PDOException $e){
            echo "Gagal : ".$e->getMessage();
            die();
        }
    break;


    case'deltemb':

        $strht = "delete from " . $dbname . ".sdm_tembusansp where nopengajuan='" . $nopengajuan . "' and id='".$id."'";
        try {
            $owlPDO->exec($strht);
        } catch (PDOException $e) {
            print " Gagal: " . $e->getMessage() . "\n";
            die();
        }

    break;

	default:
		# code...
		break;
}







?>