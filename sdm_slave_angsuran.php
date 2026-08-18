<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
require_once('dompdf/autoload.inc.php');
use Dompdf\Dompdf;
$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}
$arrtipeangsuran=array('angsuranjangkawaktu'=>'Angsuran Jangka Waktu','angsurannominal'=>'Angsuran Nominal');

$path	= "fileupload/angsurankaryawan/";

switch ($method) {
	case'loadjenisangsuran':

		$arrlokasitugas=makeOption($dbname, 'datakaryawan', 'karyawanid,kodeorganisasi'," karyawanid='".$param['karyawanid']."'");
        $lokasitugas=$arrlokasitugas[$param['karyawanid']];

        $str="select komponengaji,jenisangsuran from ".$dbname.".sdm_angsuran_komponen where kodeorg='".$lokasitugas."' and status='1'";
        $res=fetchdata($str);
        foreach($res as $val){
            $optjenisangsuran.="<option value='".$val['komponengaji']."'>".$val['jenisangsuran']."</option>";
        }

        echo $optjenisangsuran;
	break;	

    case'hitungbulan':
        $totalhutang=doubleval(str_replace(',', '', $param['totalhutang']));
        $rpbulan=doubleval(str_replace(',', '',$param['rpbulan']));
        if($rpbulan>$totalhutang){
            exit('Warning : Total hutang lebih kecil dari rupiah/bulan');
        }
        $jumlahbulan=ceil($totalhutang/$rpbulan)-1;

        $tglx=$param['bulandari'].'-01';
        $tglxnext = date('Y-m-d', strtotime($tglx . ' +'.$jumlahbulan.' month'));

        echo substr($tglxnext, 0,7);
    break;

	case'getdetail':
		
        $strApp="select * from ".$dbname.".sdm_angsuran where karyawanid='".$param['karyawanid']."' and jenis='".$param['jenisangsuran']."' and (status='1' or status='0') and notransaksi!='".$param['notransaksi']."' ";
        $resApp=fetchData($strApp);
        if(count($resApp) > 0){
            exit("Warning : Sudah ada data yang tersimpan atas karyawan dan jenis angsuran ini yang belum lunas/belum diposting , edit data yang belum diposting");
        }else{

            $strApp="select * from ".$dbname.".sdm_angsuran where  notransaksi='".$param['notransaksi']."' ";
            $resApp=fetchData($strApp);
            $lokasitugas=getKary($resApp[0]['karyawanid'],'lokasitugas');
            $rangebulan = month_inbetween($param['bulandari'],$param['bulansampai']);
            $jumlahbulan=count($rangebulan);
            $jumlahbulanx=count($rangebulan);
            if($resApp[0]['status']=='1'){
                $tab="<thead>";
                $tab.="<tr class=rowcontent>";
                if($jumlahbulan>=5){
                    $pembagi=5;
                    $tab.="<td align=center>No</td>";
                    $tab.="<td align=center>Bulan</td>";
                    $tab.="<td align=center>Rupiah</td>";
                    $tab.="<td align=center>No</td>";
                    $tab.="<td align=center>Bulan</td>";
                    $tab.="<td align=center>Rupiah</td>";
                    $tab.="<td align=center>No</td>";
                    $tab.="<td align=center>Bulan</td>";
                    $tab.="<td align=center>Rupiah</td>";
                    $tab.="<td align=center>No</td>";
                    $tab.="<td align=center>Bulan</td>";
                    $tab.="<td align=center>Rupiah</td>";
                    $tab.="<td align=center>No</td>";
                    $tab.="<td align=center>Bulan</td>";
                    $tab.="<td align=center>Rupiah</td>";
                }else{
                    $pembagi=$jumlahbulan;
                    for ($i=1; $i <=$jumlahbulan; $i++) { 
                        $tab.="<td align=center>No</td>";
                        $tab.="<td align=center>Bulan</td>";
                        $tab.="<td align=center>Rupiah</td>";
                    }
                }
                $tab.="</tr>";
                $tab.="</thead>";
                $tab.="<tbody>";
                $no=0;
                $trx=0;
                #kodeorg='".$lokasitugas."' and 
                $datagajitutup=array();
                $str="select * from ".$dbname.".sdm_5periodegaji where sudahproses='1'";
                $res=fetchdata($str);
                foreach($res as $keyx=>$valx){
                    $datagajitutup[$valx['periode']]=1;
                }
                $bulanakhirx='';
                $str="select * from ".$dbname.".sdm_angsurandt where notransaksi='".$param['notransaksi']."'";
                $res=fetchdata($str);
                foreach($res as $keyx=>$valx){
                    $bulanakhirx=$valx['bulan'];
                    $jumlahbulanx--;
                    $disabled='';
                    if(isset($datagajitutup[$valx['bulan']])){
                        $disabled='disabled';
                    }
                    $no++;
                    if($no%$pembagi==0){
                        $trx=$no+1;
                    }
                    if($no==$trx){
                        $tab.="<tr class=rowcontent>";
                    }
                    $tab.="<td align=center><b>".$no."</b></td>";
                    $tab.="<td id=bulan_".$no." align=center>".$valx['bulan']."</td>";
                    $tab.="<td align=center><input id=rpdetailx_".$no." style=width:75px class=myinputtextnumber onkeypress=\"return angka_doang(event);\" value=".number_format($valx['jumlah'],0)." ".$disabled."><input type=hidden id=rpdetail_".$no."></td>";
                    
                    if($no%$pembagi==0){
                        $tab.="</tr>";
                    }

                }

                if($jumlahbulanx>0){

                    $rangebulanx = month_inbetween($bulanakhirx,$param['bulansampai']);
                    foreach($rangebulanx as $bulan){
                        if($bulan!=$bulanakhirx){
                            $no++;
                            if($no%$pembagi==0){
                                $trx=$no+1;
                            }
                            if($no==$trx){
                                $tab.="<tr class=rowcontent>";
                            }
                            $tab.="<td align=center><b>".$no."</b></td>";
                            $tab.="<td id=bulan_".$no." align=center>".$bulan."</td>";
                            $tab.="<td align=center><input id=rpdetailx_".$no." style=width:75px class=myinputtextnumber onkeypress=\"return angka_doang(event);\" value=0 ><input type=hidden id=rpdetail_".$no."></td>";
                            
                            if($no%$pembagi==0){
                                $tab.="</tr>";
                            }

                        }

                    }
                }

                    $tab.="<input hidden id=totaldetail value=".$no."></tbody>";
            }else{
                 $totalhutang=doubleval(str_replace(',', '', $param['tothutang']));
                    $rpbulan=doubleval(str_replace(',', '',$param['rpbulan']));
                    $rangebulan = month_inbetween($param['bulandari'],$param['bulansampai']);
                    
                    if($param['tipeangsuran']=='angsurannominal'){
                        $nilaiperbulan=$rpbulan;
                    }else{
                        $jumlahbulan=count($rangebulan);
                        
                        $rpbulan=ceil($totalhutang/$jumlahbulan);
                        if(substr($rpbulan,-2)>0){
                            $pengurang=substr($rpbulan,-2);
                            $rpbulan=$rpbulan-$pengurang;
                        }
                        $nilaiperbulan=$rpbulan;
                    }
                    //$tab.="<table cellspacing='1' border='0'>";
                    $tab.="<thead>";
                    $tab.="<tr class=rowcontent>";
                    $jumlahbulan=count($rangebulan);
                    if($jumlahbulan>=5){
                        $pembagi=5;
                        $tab.="<td align=center>No</td>";
                        $tab.="<td align=center>Bulan</td>";
                        $tab.="<td align=center>Rupiah</td>";
                        $tab.="<td align=center>No</td>";
                        $tab.="<td align=center>Bulan</td>";
                        $tab.="<td align=center>Rupiah</td>";
                        $tab.="<td align=center>No</td>";
                        $tab.="<td align=center>Bulan</td>";
                        $tab.="<td align=center>Rupiah</td>";
                        $tab.="<td align=center>No</td>";
                        $tab.="<td align=center>Bulan</td>";
                        $tab.="<td align=center>Rupiah</td>";
                        $tab.="<td align=center>No</td>";
                        $tab.="<td align=center>Bulan</td>";
                        $tab.="<td align=center>Rupiah</td>";
                    }else{
                        $pembagi=$jumlahbulan;
                        for ($i=1; $i <=$jumlahbulan; $i++) { 
                            $tab.="<td align=center>No</td>";
                            $tab.="<td align=center>Bulan</td>";
                            $tab.="<td align=center>Rupiah</td>";
                        }
                    }
                    $tab.="</tr>";
                    $tab.="</thead>";
                    $tab.="<tbody>";
                    $no=0;
                    $trx=0;
                    foreach($rangebulan as $bulan){
                        if($param['tipeangsuran']=='angsurannominal'){
                            if($totalhutang>$nilaiperbulan){
                                $totalhutang-=$nilaiperbulan;
                            }else{
                                $nilaiperbulan=$totalhutang;
                            }
                        }else{
                           if($totalhutang>$nilaiperbulan){
                                if($bulan==$param['bulansampai']){
                                    $nilaiperbulan=$totalhutang;
                                }else{
                                    $totalhutang-=$nilaiperbulan;
                                }
                            }else{
                                $nilaiperbulan=$totalhutang;
                            }
                        }
                        $no++;
                        if($no%$pembagi==0){
                            $trx=$no+1;
                        }
                        if($no==$trx){
                            $tab.="<tr class=rowcontent>";
                        }
                        $tab.="<td align=center><b>".$no."</b></td>";
                        $tab.="<td id=bulan_".$no." align=center>".$bulan."</td>";
                        $tab.="<td align=center><input id=rpdetailx_".$no." style=width:75px class=myinputtextnumber onkeypress=\"return angka_doang(event);\" value=".number_format($nilaiperbulan,0)."><input type=hidden id=rpdetail_".$no."></td>";
                        
                        if($no%$pembagi==0){
                            $tab.="</tr>";
                        }

                    }
                    $tab.="<input hidden id=totaldetail value=".$no."></tbody>";
            }

           
            //$tab.="</table>";

            echo $tab;
        }
	break;		
	
    case'insert':
        
        $strApp="select * from ".$dbname.".sdm_angsuran where karyawanid='".$param['karyawanid']."' and jenis='".$param['jenisangsuran']."' and (status='1' or status='0') ";
        $resApp=fetchData($strApp);
        if(count($resApp) > 0){
            exit("Warning : Sudah ada data yang tersimpan atas karyawan dan jenis angsuran ini yang belum lunas/belum diposting , silahkan edit data yang belum diposting");
        }
        $totalhutang=doubleval(str_replace(',', '', $param['tothutang']));
        $totaldetail=0;
        $arrdetail=array();
        for ($i=1; $i <=$param['totalcountdetail']; $i++) { 
            if(!isset($arrdetail[$param['bulan'.$i]])){
                $arrdetail[$param['bulan'.$i]]=0;
            }
            $totaldetail+=doubleval(str_replace(',', '', $param['rpbulandet'.$i]));
            $arrdetail[$param['bulan'.$i]]+=doubleval(str_replace(',', '', $param['rpbulandet'.$i]));
        }
        if($totalhutang!=$totaldetail){
            exit("Warning : Total hutang dengan total detail tidak sama dengan varian : ".($totalhutang-$totaldetail)."<br> silahkan dilakukan pengecekan kembali");
        }
        $optptkar = makeOption($dbname,'datakaryawan','karyawanid,kodeorganisasi');
        $arrjenisangsuran=makeOption($dbname,'sdm_angsuran_komponen','komponengaji,inisial');
        #bulanawal/jenisangsuran/counter dimana counter per periode tahun-bulan dan jenis angsuran
        $str    = "SELECT max(right(notransaksi,5)) as jmlhrow FROM " . $dbname . ".sdm_angsuran 
        WHERE notransaksi like '".str_replace('-', '', $param['bulandari'])."%' and jenis = '".$param['jenisangsuran']."'";
        $res    = fetchData($str);
        $jlhbrs     = $res[0]['jmlhrow'];
        $counterid  = intval($res[0]['jmlhrow'])+1;
        $idbaru = addZero($counterid,5);
        $notransaksi=str_replace('-', '', $param['bulandari'])."/".str_replace(' ', '', $arrjenisangsuran[$param['jenisangsuran']])."/".$idbaru;

        $insertht     = "INSERT INTO " . $dbname . ".sdm_angsuran 
        (`notransaksi`,`karyawanid`,`jenis`,`tipe`,`start`,`end`,`total`,`totalpinjaman`,`bulanan`,`keterangan`,`status`,`updatetime`,`updateby`)
        VALUES 
        ('" . $notransaksi . "','" . $param['karyawanid']. "','" . $param['jenisangsuran']. "','" . $param['tipeangsuran']. "','" . $param['bulandari']. "','" . $param['bulansampai']. "','" . doubleval(str_replace(',', '', $param['tothutang'])). "','" . doubleval(str_replace(',', '', $param['tothutang'])). "','" . doubleval(str_replace(',', '', $param['rpbulan'])). "','" . $param['ket']. "','0','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."')";
        try{
            $owlPDO->exec($insertht);
        }catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

        foreach ($arrdetail as $key => $val) {
            $insertdt     = "INSERT INTO " . $dbname . ".sdm_angsurandt 
            (`notransaksi`,`bulan`,`jumlah`) VALUES ('" . $notransaksi . "','" . $key. "','" . $val. "')";
            //echo $insertdt;
            try{
                $owlPDO->exec($insertdt);
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }

    break;
    case'inserttopup':
        $rpbulantopup=$param['rpbulantopup'];
        $tothutangtopup=$param['tothutangtopup'];
        if($rpbulantopup>$tothutangtopup and $param['tipeangsuran']=='angsurannominal'){
            exit('Warning : Total hutang lebih kecil dari rupiah/bulan');
        }

        $strApp="select * from ".$dbname.".sdm_angsuran_topup where notransaksi='".$param['notransaksi']."' and post='0' ";
        $resApp=fetchData($strApp);
        if(count($resApp) > 0){
            exit("Warning : Ada data topup yang belum diposting,silahkan diposting terlebih dahulu");
        }

        if($param['tipeangsuran']=='angsuranjangkawaktu'){
            $strApp="select * from ".$dbname.".sdm_angsuran_topup where notransaksi='".$param['notransaksi']."' and bulanmulaiperubahan>='".$param['bulanmulaiperubahan']."'  ";
            $resApp=fetchData($strApp);
            if(count($resApp) > 0 and $param['jenistopup']==0){
                exit("Warning : Sudah ada data topup bulan mulai perubahan yang sama atau lebih tinggi dari bulan mulai perubahan yang dipilih, silahkan mengganti bulan mulai perubahan");
            }

        }else{
            $strApp="select * from ".$dbname.".sdm_angsuran_topup where notransaksi='".$param['notransaksi']."' and bulanmulaiperubahan>'".$param['bulanmulaiperubahan']."'  ";
            $resApp=fetchData($strApp);
            if(count($resApp) > 0  and $param['jenistopup']==0){
                exit("Warning : Sudah ada data topup bulan mulai perubahan yang sama atau lebih tinggi dari bulan mulai perubahan yang dipilih, silahkan mengganti bulan mulai perubahan");
            }
        }

        
        #bulanawal/jenisangsuran/counter dimana counter per periode tahun-bulan dan jenis angsuran
        $str    = "SELECT count(*)as jmlhrow FROM " . $dbname . ".sdm_angsuran_topup 
        WHERE notransaksi = '".$param['notransaksi']."'";
        $res    = fetchData($str);
        $counteridx  = intval($res[0]['jmlhrow'])+1;
        $idbarux = addZero($counteridx,5);
        //echo intval(count($res)).'##'.$counteridx;
        $nomotopup=str_replace('-', '', $param['bulanmulaiperubahan'])."/TopupAngsuran/".$idbarux;

        $insertht     = "INSERT INTO " . $dbname . ".sdm_angsuran_topup 
        (`notransaksi`,`nomotopup`,`tipetopup`,`bulanmulaiperubahan`,`bulansampaiangsuran`,`rupiah`,`rupiahbulanan`,`post`,`updatetime`,`updatedby`)
        VALUES 
        ('" . $param['notransaksi'] . "','" . $nomotopup. "','" . $param['jenistopup']. "','" . $param['bulanmulaiperubahan']. "','" . $param['bulansampaitopup']. "','" . $param['tothutangtopup']. "','" . $param['rpbulantopup']. "','0','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."')";
        //echo $insertht;
        try{
            $owlPDO->exec($insertht);
        }catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

    break;
    case'loadData':
		
        $tab        ="";
        $footer     ="";
		$limit      = 10;
        $page       = 0;
        $colspan    = 15;

		if (isset($_POST['page'])) {
            $page = $_POST['page'];
            if ($page < 0)
                $page = 0;
        }
        
		$offset     = floatval($page) * $limit;
		$maxdisplay =(floatval($page) * $limit);
        $no         =((floatval($page) * $limit));
        $arrorgdet  = getOrgDetail(2);
		
        if($param['notransaksisch'] != ''){
            $where.=" and notransaksi like '%".$param['notransaksisch']."%'";
        }
        if($param['namasch'] != ''){
            $where.=" and namakaryawan like '%".$param['namasch']."%'";
        }
        if($param['lokasitugassch'] != ''){
            $where.=" and lokasitugas = '".$param['lokasitugassch']."'";
        }
        if($param['jenisangsuransch'] != ''){
            $where.=" and jenis = '".$param['jenisangsuransch']."'";
        }
        $str        = "SELECT COUNT(*) AS jmlhrow FROM ".$dbname.".sdm_angsuran a 
        left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
        WHERE b.lokasitugas in (".$arrorgdet.")  ".$where." ORDER BY start DESC";
        $res        = $owlPDO->query($str) or die(print " Gagal: " . PDOException::getMessage());
        $jlhbrs     = owlBaris($res);
		$res        = fetchdata($str);
		$jlhbrs     = $res[0]['jmlhrow'];
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
            $iList  = "SELECT a.*,b.lokasitugas,b.namakaryawan,b.kodeorganisasi FROM ".$dbname.".sdm_angsuran a 
        left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
        WHERE b.lokasitugas in (".$arrorgdet.")  ".$where." ORDER BY start DESC LIMIT ".$offset.",".$limit." ";
            $hasil  = fetchdata($iList);
            foreach ($hasil as $dList){
                if($dList['status'] == 0){
                    $status = "Belum Aktif/Berjalan";
                }
                elseif ($dList['status'] == 1) {
                    $status = "Aktif/Sudah Berjalan";
                }
                elseif ($dList['status'] == 3) {
                    $status = 'Sudah Lunas';
                }
                $arrjenisangsuran=makeOption($dbname,'sdm_angsuran_komponen','komponengaji,jenisangsuran');
                $no+=1;
                $tab.="<tr class=rowcontent>";
                    $tab.="<td align=left>".$dList['notransaksi']."</td>";
                    $tab.="<td align=center>".$dList['namakaryawan']."</td>";
                    $tab.="<td align=center>".$arrjenisangsuran[$dList['jenis']]."</td>";
                    $tab.="<td align=center>".$arrtipeangsuran[$dList['tipe']]."</td>";
                    $tab.="<td align=center>".$dList['start']."</td>";
                    $tab.="<td align=center>".$dList['end']."</td>";
                    $tab.="<td align=center>".$dList['keterangan']."</td>";
                    $tab.="<td align=center>".$status."</td>";

                    if($dList['status']==0){
                        $tab.="<td align=center>
                                <img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"edit('" . $dList['notransaksi'] . "','" . $dList['status'] . "');\">
                            </td>";
                        $tab.="<td align=center>
                                <img src=images/application/application_delete.png class=zImgBtn  caption='Delete' onclick=\"del('" . $dList['notransaksi'] . "');\">
                            </td>";
                        $tab.="<td align=center>
                                <img src=images/skyblue/posting.png class=zImgBtn  caption='Posting' onclick=\"postdata('" . $dList['notransaksi'] . "');\">
                            </td>";
                        $tab.="<td align=center><img src=images/zoom.png class=zImgBtn title=Detail onclick=previewDetail('".$dList['notransaksi']."');></td>";
                        
                    }

                    if($dList['status']==1){
                        $tab.="<td align=center>
                                <img src=images/application/application_edit.png class=zImgBtn  caption='Edit' onclick=\"edit('" . $dList['notransaksi'] . "','" . $dList['status'] . "');\">
                            </td>";
                        $tab.="<td hidden align=center>
                                <img src=images/plus.png class=zImgBtn title=Topup onclick=topup('".$dList['notransaksi']."');>
                            </td>";
                        $tab.="<td align=center>
                                <img src=images/close.png class=zImgBtn title=close onclick=close_a('".$dList['notransaksi']."','" . $dList['end'] . "');>
                            </td>";
                        $tab.="<td align=center><img src=images/zoom.png class=zImgBtn title=Detail onclick=previewDetail('".$dList['notransaksi']."');></td>";
                        $tab.="<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$dList['notransaksi']."','".$dList['karyawanid']."')\" src='images/upload-2-xxl.png'/></td>";
                    }

                    if($dList['status']==3){
                        $tab.="<td align=center></td>";
                        $tab.="<td align=center></td>";
                        $tab.="<td align=center><img src=images/zoom.png class=zImgBtn title=Detail onclick=previewDetail('".$dList['notransaksi']."');></td>";
                        $tab.="<td align=center><img title='".$_SESSION['lang']['upload']."' class=zImgBtn onclick=\"showupload(event,'".$dList['notransaksi']."','".$dList['karyawanid']."')\" src='images/upload-2-xxl.png'/></td>";
                    }                

                $tab.="</tr></tbody>";
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

    case 'showupload':
            
        $tab="";
        $tab.="<table cellspacing='1' border='0' id='uploadpopup' width=100%>";
        $tab.="<tr>
                <td>".$_SESSION['lang']['notransaksi']."</td>
                <td>:</td>
                <td>
                    <label id='notransaksix' style='font-weight:bold'>".$param['notransaksi']."</label>
                </td>
            </tr>
            <tr>
                <td>".$_SESSION['lang']['nama']."</td>
                <td>:</td>
                <td>
                    <label id='karyawanidx' hidden style='font-weight:bold'>".$param['karyawanid']."</label>
                    <label style='font-weight:bold'>".getNamaKaryawan($param['karyawanid'])."</label>
                </td>
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
                    <td colspan=2></td>
                    <td>
                        <button class=mybutton onclick=\"submitfile()\">Submit</button>
                    </td>
                </tr>
            </table>
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

    case 'loadfiles':
        $no = 0;
        $tab = "";	
        $str="select * from ".$dbname.".listfile_sdm_angsurankaryawan where notransaksi = '".$param['notransaksi']."' and status='1' and karyawanid='".$param['karyawanid']."'";
        $res=fetchData($str);
        if(empty($res)){
            $tab.="<tr class=rowcontent><td colspan=4 style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
        }else{
            foreach($res as $key=>$val){
                $no++;
                $tab.="<tr class=rowcontent>
                    <td style='text-align:center'>".$no."</td>";
                    
                if($val['formaticon']=='.jpeg'||$val['formaticon']=='.jpg'){
                    $tab.="<td style='text-align:center'>
                        <a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon title='JPG'></a>
                    </td>";
                }elseif($val['formaticon']=='.png'){
                    $tab.="<td style='text-align:center'>
                        <a href='".$path.$val['namafile']."' download><img src=images/uploader/png.png class=resicon  title='PNG'></a>
                    </td>";
                }elseif($val['formaticon']=='.pdf'){
                    $tab.="<td style='text-align:center'>
                        <a href='".$path.$val['namafile']."' download><img src=images/uploader/pdf.png class=resicon  title='PDF'></a>
                    </td>";
                }elseif($val['formaticon']=='.xls'||$val['formaticon']=='.xlsx'){
                    $tab.="<td style='text-align:center'>
                        <a href='".$path.$val['namafile']."' download><img src=images/uploader/excel.png class=resicon  title='xls'></a>
                    </td>";
                }elseif($val['formaticon']=='.doc'||$val['formaticon']=='.docx'){
                    $tab.="<td style='text-align:center'>
                        <a href='".$path.$val['namafile']."' download><img src=images/uploader/word.png class=resicon  title='doc'></a>
                    </td>";
                }else{
                    $tab.="<td style='text-align:center'>
                        <a href='".$path.$val['namafile']."' download><img src=images/uploader/jpg.png class=resicon  title='jpg'></a>
                    </td>";
                }
                
                $tab.="<td style='text-align:left;cursor:pointer' onclick=\"viewfile('".$path.str_replace('/','',$val['namafile'])."')\">".$val['namafile']."</td>
                    <td align=center>
                    <a href='".$path.$val['namafile']."' download><img src=images/uploader/dwnld8.png class=resicon  title='download'></a>&nbsp";
                
                $tab.="<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deletefile('".$val['notransaksi']."','".$val['karyawanid']."','".$val['namafile']."');\" >";
                
                $tab."	</td>
                </tr>";
            }	
        }
        
        echo $tab;
    break;

    case 'deletefile':
        $str="delete from ".$dbname.".listfile_sdm_angsurankaryawan where notransaksi = '".$param['notransaksi']."' and status='1' and karyawanid='".$param['karyawanid']."' and namafile='".$param['namafile']."'";
        try{
            $owlPDO->exec($str);
            $pathx = $path.$param['namafile'];
            unlink($pathx);
        }
        catch(PDOException $e){
            echo " Gagal," . addslashes($e->getMessage());
        }
    break;

    case'submitfile':
        $tgl = date("YmdHis");
        $his = date("His");
        $data = $_POST;
        
        if($data['fileupload']!=''){
            if($_FILES['file']['error']==0){	
                $filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
                $filename = $pt."_".$his."".$filetype;
                $file_tmpname = file_get_contents($_FILES['file']['tmp_name']);	
                
                if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx')){
                    if($_FILES['file']['size'] <= 2500000){
                        $str = "insert into ".$dbname.".listfile_sdm_angsurankaryawan (notransaksi,karyawanid,namafile,formaticon,status,createdby,createdtime) values ('".$param['notransaksi']."','".$param['karyawanid']."','".$filename."','".$filetype."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i')."')";
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
                    }else{
                        exit("warning : Ukuran file upload maksimal 250kb");
                    }
                }else{
                    exit("Warning : Format file upload salah");
                }
            }
        }
    break;

    case'viewfile':
        $tab="";
        $res[0]['formaticon'] = strtolower('.'.substr($param['namafile'],strripos($param['namafile'],'.')+1));
        
        if($res[0]['formaticon']=='.xls' or $res[0]['formaticon']=='.xlsx' or $res[0]['formaticon']=='.doc' or $res[0]['formaticon']=='.docx'){
            exit("Warning: Tidak bisa ditampilkan, silahkan download.");
        }
        
        if($res[0]['formaticon']=='.pdf'){
            $tab.="<embed src='".$param['namafile']."' style='width:100%;height:97%;' type='application/pdf'>";
        }else{			
            $tab.="<img src='".$param['namafile']."'>";
        }
        
        echo $tab;
    break;	
    
    case 'delete':
        $str    = "Delete FROM " . $dbname . ".sdm_angsuran WHERE notransaksi ='".$param['notransaksi']."'";
        try{
            $owlPDO->exec($str);
        }catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break; 
    case 'deltopup':
        $str    = "Delete FROM " . $dbname . ".sdm_angsuran_topup WHERE notransaksi ='".$param['notransaksi']."' and nomotopup='".$param['nomotopup']."'";
        try{
            $owlPDO->exec($str);
        }catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break; 
    case 'postdata':
        $str    = "update " . $dbname . ".sdm_angsuran set status='1' WHERE notransaksi ='".$param['notransaksi']."'";
        try{
            $owlPDO->exec($str);
        }catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break; 
    case 'posttopup':
        $str="select * from ".$dbname.".sdm_angsuran_topup where nomotopup='".$param['nomotopup']."' and notransaksi='".$param['notransaksi']."'";
        $res=fetchData($str);
        $notransaksi=$res[0]['notransaksi'];
        $nomotopup=$res[0]['nomotopup'];
        $tipetopup=$res[0]['tipetopup'];
        $bulanmulaiperubahan=$res[0]['bulanmulaiperubahan'];
        $bulansampaiangsuran=$res[0]['bulansampaiangsuran'];
        $rupiah=$res[0]['rupiah'];
        $rupiahbulanan=$res[0]['rupiahbulanan'];
        
        if($tipetopup==0){

            $str="select sum(jumlah) as total from ".$dbname.".sdm_angsurandt where notransaksi='".$notransaksi."' and bulan>='".$bulanmulaiperubahan."'";
            $res=fetchdata($str);
            $totalsisahutangsebelumtopup=$res[0]['total'];
            $totalhutangyangakandiubah=$totalsisahutangsebelumtopup+$rupiah;

            
            // echo $totalsisahutangsebelumtopup;
            // exit('Error');
            $totalhutang=$totalhutangyangakandiubah;

            $str    = "Delete FROM " . $dbname . ".sdm_angsurandt WHERE notransaksi ='".$notransaksi."' and bulan>='".$bulanmulaiperubahan."'";
            //echo $str;
            try{
                $owlPDO->exec($str);
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

            
            if($param['tipeangsuran']=='angsurannominal'){
                $nilaiperbulan=$rupiahbulanan;
                $jumlahbulan=ceil($totalhutang/$rupiahbulanan)-1;

                $tglx=$bulanmulaiperubahan.'-01';
                $tglxnext = date('Y-m-d', strtotime($tglx . ' +'.$jumlahbulan.' month'));

                $bulansampaiangsuran=substr($tglxnext, 0,7);
            }else{
                $rangebulan = month_inbetween($bulanmulaiperubahan,$bulansampaiangsuran);

                $jumlahbulan=count($rangebulan);
                
                $rpbulan=ceil($totalhutangyangakandiubah/$jumlahbulan);
                if(substr($rpbulan,-2)>0){
                    $pengurang=substr($rpbulan,-2);
                    $rpbulan=$rpbulan-$pengurang;
                }
                $nilaiperbulan=$rpbulan;
            }

            $rangebulan = month_inbetween($bulanmulaiperubahan,$bulansampaiangsuran);
            foreach($rangebulan as $bulan){
                 if($param['tipeangsuran']=='angsurannominal'){
                    if($totalhutangyangakandiubah>$nilaiperbulan){
                        $totalhutangyangakandiubah-=$nilaiperbulan;
                    }else{
                        $nilaiperbulan=$totalhutangyangakandiubah;
                    }
                }else{
                   if($totalhutangyangakandiubah>$nilaiperbulan){
                        if($bulan==$bulansampaiangsuran){
                            $nilaiperbulan=$totalhutangyangakandiubah;
                        }else{
                            $totalhutangyangakandiubah-=$nilaiperbulan;
                        }
                    }else{
                        $nilaiperbulan=$totalhutangyangakandiubah;
                    }
                }

                $insertdt     = "INSERT INTO " . $dbname . ".sdm_angsurandt 
                (`notransaksi`,`bulan`,`jumlah`) VALUES ('" . $notransaksi . "','" . $bulan. "','" . $nilaiperbulan. "')";
                //echo $insertdt;
                try{
                    $owlPDO->exec($insertdt);
                }catch (PDOException $e) {
                    print " Gagal  !: " . $e->getMessage() . "\n";
                    die();
                }
            }


            $str="select totalpinjaman from ".$dbname.".sdm_angsuran where notransaksi='".$notransaksi."'";
            $res=fetchdata($str);
            $totalhutangx=$res[0]['totalpinjaman'];
            $totalhutangxdiubah=$totalhutangx+$rupiah;

            $str    = "update " . $dbname . ".sdm_angsuran set totalpinjaman='".$totalhutangxdiubah."',bulanan='".$rupiahbulanan."',end='".$bulansampaiangsuran."' WHERE notransaksi ='".$notransaksi."'";
            try{
                $owlPDO->exec($str);
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }else{
            $str    = "update " . $dbname . ".sdm_angsuran set status='3' WHERE notransaksi ='".$notransaksi."'";
            try{
                $owlPDO->exec($str);
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }

        $str    = "update " . $dbname . ".sdm_angsuran_topup set post='1' WHERE nomotopup ='".$param['nomotopup']."' and notransaksi='".$notransaksi."'";
        try{
            $owlPDO->exec($str);
        }catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break; 
    case'update':
        
        $strApp="select * from ".$dbname.".sdm_angsuran where karyawanid='".$param['karyawanid']."' and jenis='".$param['jenisangsuran']."' and (status='1' or status='0') and notransaksi!='".$param['notransaksi']."' ";
        $resApp=fetchData($strApp);
        if(count($resApp) > 0){
            exit("Warning : Sudah ada data yang tersimpan atas karyawan dan jenis angsuran ini yang belum lunas/belum diposting , silahkan edit data yang belum diposting");
        }


        $totalhutang=doubleval(str_replace(',', '', $param['tothutang']));
        $totaldetail=0;
        $arrdetail=array();
        for ($i=1; $i <=$param['totalcountdetail']; $i++) { 
            if(!isset($arrdetail[$param['bulan'.$i]])){
                $arrdetail[$param['bulan'.$i]]=0;
            }
            $totaldetail+=doubleval(str_replace(',', '', $param['rpbulandet'.$i]));
            $arrdetail[$param['bulan'.$i]]+=doubleval(str_replace(',', '', $param['rpbulandet'.$i]));
        }
        if($totalhutang!=$totaldetail){
            exit("Warning : Total hutang dengan total detail tidak sama dengan varian : ".($totalhutang-$totaldetail)."<br> silahkan dilakukan pengecekan kembali");
        }

        $strApp="select * from ".$dbname.".sdm_angsuran where status='1' and notransaksi='".$param['notransaksi']."' ";
        $resApp=fetchData($strApp);
        if(count($resApp) == 0){
            $str    = "Delete FROM " . $dbname . ".sdm_angsuran WHERE notransaksi ='".$param['notransaksi']."'";
            try{
                $owlPDO->exec($str);
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }

            $notransaksi=$param['notransaksi'];

            $insertht     = "INSERT INTO " . $dbname . ".sdm_angsuran 
            (`notransaksi`,`karyawanid`,`jenis`,`tipe`,`start`,`end`,`total`,`totalpinjaman`,`bulanan`,`keterangan`,`status`,`updatetime`,`updateby`)
            VALUES 
            ('" . $notransaksi . "','" . $param['karyawanid']. "','" . $param['jenisangsuran']. "','" . $param['tipeangsuran']. "','" . $param['bulandari']. "','" . $param['bulansampai']. "','" . doubleval(str_replace(',', '', $param['tothutang'])). "','" . doubleval(str_replace(',', '', $param['tothutang'])). "','" . doubleval(str_replace(',', '', $param['rpbulan'])). "','" . $param['ket']. "','0','".date('Y-m-d H:i:s')."','".$_SESSION['standard']['userid']."')";
            try{
                $owlPDO->exec($insertht);
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
            
        }else{

            $str    = "update " . $dbname . ".sdm_angsuran set `end`='".$param['bulansampai']."' WHERE notransaksi ='".$param['notransaksi']."'";
            //echo $str;
            try{
                $owlPDO->exec($str);
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }


            $str    = "Delete FROM " . $dbname . ".sdm_angsurandt WHERE notransaksi ='".$param['notransaksi']."'";
            //echo $str;
            try{
                $owlPDO->exec($str);
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }


        foreach ($arrdetail as $key => $val) {
            $insertdt     = "INSERT INTO " . $dbname . ".sdm_angsurandt 
            (`notransaksi`,`bulan`,`jumlah`) VALUES ('" . $param['notransaksi'] . "','" . $key. "','" . $val. "')";
            //echo $insertdt;
            try{
                $owlPDO->exec($insertdt);
            }catch (PDOException $e) {
                print " Gagal  !: " . $e->getMessage() . "\n";
                die();
            }
        }
        
    break;

	case 'preview':
        $str="select a.*,b.namakaryawan,b.kodeorganisasi from ".$dbname.".sdm_angsuran a 
        left join  ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
        where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        $karyawanid=$res[0]['karyawanid'];
        $komponengaji=$res[0]['jenis'];
        $bulandari=$res[0]['start'];
        $bulansampai=$res[0]['end'];
        $arrjenisangsuran=makeOption($dbname,'sdm_angsuran_komponen','komponengaji,jenisangsuran');
        $tabx="<legend>Data Angsuran</legend><br><br>
        <table cellspacing=0 border=0 width=100% style='text-align:left'>";
        $tabx.="<tr>";
        $tabx.="<td> No.Transaksi </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$res[0]['notransaksi']."</td>";
        $tabx.="<td> Nama Karyawan </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$res[0]['namakaryawan']."</td>";
        $tabx.="</tr>";
        $tabx.="<tr>";
        $tabx.="<td> Jenis Angsuran </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$arrjenisangsuran[$res[0]['jenis']]."</td>";
        $tabx.="<td> Tipe Angsuran </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$arrtipeangsuran[$res[0]['tipe']]."</td>";
        $tabx.="</tr>";
        $tabx.="<tr>";
        $tabx.="<td> Bulan Awal </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$res[0]['start']."</td>";
        $tabx.="<td> Bulan Sampai </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$res[0]['end']."</td>";
        $tabx.="</tr>";
        $tabx.="<tr>";
        $tabx.="<td> Total Hutang Awal </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".number_format($res[0]['total'],2)."</td>";
        $tabx.="<td> Total Hutang </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".number_format($res[0]['totalpinjaman'],2)."</td>";
        $tabx.="</tr>";
        $tabx.="<tr>";
        $tabx.="<td> Keterangan </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td colspan='4'>".$res[0]['keterangan']."</td>";
        $tabx.="</tr></table>";

        $str="select count(*) as jumlahbulan from ".$dbname.".sdm_angsurandt where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        $jumlahbulan=intval($res[0]['jumlahbulan']);

        $tabx.="<table cellspacing=0 border=0 width=50% style='text-align:left'><thead>";
        $tabx.="<tr>";
        if($jumlahbulan>=5){
            $pembagi=5;
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
        }else{
            $pembagi=$jumlahbulan;
            for ($i=1; $i <=$jumlahbulan; $i++) { 
                $tabx.="<td align=center>No</td>";
                $tabx.="<td align=center>Bulan</td>";
                $tabx.="<td align=center>Rupiah</td>";
            }
        }
        $tabx.="</tr>";
        $tabx.="</thead>";
        $tabx.="<tbody>";
        $no=0;
        $trx=0;

        $str="select * from ".$dbname.".sdm_angsurandt where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        foreach($res as $keyx=>$valx){
            $no++;
            if($no%$pembagi==0){
                $trx=$no+1;
            }
            if($no==$trx){
                $tabx.="<tr>";
            }
            $tabx.="<td align=center><b>".$no."</b></td>";
            $tabx.="<td id=bulan_".$no." align=center>".$valx['bulan']."</td>";
            $tabx.="<td align=center>".number_format($valx['jumlah'],0)."</td>";
            
            if($no%$pembagi==0){
                $tabx.="</tr>";
            }

        }

        $tabx.="</tbody>";
        $databayar=array();
        $str="select * from ".$dbname.".sdm_gaji where karyawanid='".$karyawanid."' and idkomponen='".$komponengaji."' 
        and periodegaji>='".$bulandari."' and periodegaji<='".$bulansampai."'";
        $res=fetchdata($str);
        foreach($res as $keyx=>$valx){
            $databayar[$valx['periodegaji']]['Gaji']=$valx['jumlah'];
        }

         $str="select * from ".$dbname.".keu_kasbankdt where nodok='".$param['notransaksi']."' and nik='".$karyawanid."'";
        $res=fetchdata($str);
        foreach($res as $keyx=>$valx){
            $databayar[substr($valx['tanggal'],0,7)]['Kas/Bank']=$valx['jumlah'];
        }

        $tabx.="</table>";
        $tabx.="<legend>Data Angsuran telah dibayar</legend><br><br>
        <table cellspacing=0 border=0 width=50% style='text-align:left'><thead>";
        $tabx.="<tr>";
        $tabx.="<td>Bulan</td>";
        $tabx.="<td>Jumlah</td>";
        $tabx.="<td>Source</td>";
        $tabx.="</tr>";
        $tabx.="</thead>";
        $tabx.="<tbody>";
        foreach ($databayar as $bulan => $key) {
            foreach ($key as $source => $val) {
                $tabx.="<tr>";
                $tabx.="<td>".$bulan."</td>";
                $tabx.="<td>".$val."</td>";
                $tabx.="<td>".$source."</td>";
                $tabx.="</tr>";
            }
        }

        $tabx.="</tbody>";
        $tabx.="</table>";

        // $arrtipetopup=array('0'=>'Penambahan Hutang','1'=>'Pelunasan Hutang');
        // $tabx.="</table><br><br><legend>List Data Top-up</legend><br><br>
        // <table cellspacing=0 border=1 width=40% style='text-align:center'>";
        // $tabx.="<thead><tr>";
        // $tabx.="<td>No. Top-up</td>";
        // $tabx.="<td>Jenis Top-up</td>";
        // $tabx.="<td>Bulan Mulai Perubahan</td>";
        // $tabx.="<td>Bulan Sampai Angsuran</td>";
        // $tabx.="<td>Rupiah/Bulan</td>";
        // $tabx.="<td>Total Top-up</td>";
        // $tabx.="</tr></thead>";
        // $str="select * from ".$dbname.".sdm_angsuran_topup where notransaksi='".$param['notransaksi']."'";
        // $res=fetchdata($str);
        // foreach($res as $keyx=>$valx){
        //     $tabx.="<tr>";
        //     $tabx.="<td>".$valx['nomotopup']."</td>";
        //     $tabx.="<td>".$arrtipetopup[$valx['tipetopup']]."</td>";
        //     $tabx.="<td>".$valx['bulanmulaiperubahan']."</td>";
        //     $tabx.="<td>".$valx['bulansampaiangsuran']."</td>";
        //     $tabx.="<td>".number_format($valx['rupiahbulanan'])."</td>";
        //     $tabx.="<td>".number_format($valx['rupiah'])."</td>";
        // }
        // $tabx.="</table>";

        echo $tabx;

    break;
    case 'close_a':
        
        // ambil periode gaji saat ini
        // $str0="SELECT * FROM ".$dbname.".`sdm_5periodegaji` WHERE 1=1 order by periode desc limit 1 ";
        // $res0=fetchdata($str0);
        // $periode_gaji_0=$res0[0]['periode'];

        // if($param['periodeakhir'] > $periode_gaji_0){
        //     exit("Warning : periode gaji saat ini ".$periode_gaji_0." ");
        // }

        $ubah_d_ = "UPDATE " . $dbname . ".sdm_angsuran SET status='3' WHERE notransaksi= '" . $param['notransaksi'] . "'";
        try {
            $owlPDO->exec($ubah_d_);
        } catch (PDOException $e) {
            print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }

    break;
    case 'topup':
        $str="select a.*,b.namakaryawan,b.kodeorganisasi from ".$dbname.".sdm_angsuran a 
        left join  ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
        where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        $karyawanid=$res[0]['karyawanid'];
        $komponengaji=$res[0]['jenis'];
        $tipeangsuran=$res[0]['tipe'];
        $bulandari=$res[0]['start'];
        $bulansampai=$res[0]['end'];


        $arrjenisangsuran=makeOption($dbname,'sdm_angsuran_komponen','komponengaji,jenisangsuran',"kodeorg='".$res[0]['kodeorganisasi']."'");
        $tabx="<legend>Data Angsuran</legend><br><br>
        <table cellspacing=0 border=0 width=100% style='text-align:left'>";
        $tabx.="<tr>";
        $tabx.="<td> No.Transaksi </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$res[0]['notransaksi']."</td>";
        $tabx.="<td> Nama Karyawan </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$res[0]['namakaryawan']."</td>";
        $tabx.="</tr>";
        $tabx.="<tr>";
        $tabx.="<td> Jenis Angsuran </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$arrjenisangsuran[$res[0]['jenis']]."</td>";
        $tabx.="<td> Tipe Angsuran </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$arrtipeangsuran[$res[0]['tipe']]."</td>";
        $tabx.="</tr>";
        $tabx.="<tr>";
        $tabx.="<td> Bulan Awal </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".$res[0]['start']."</td>";
        $tabx.="<td> Bulan Sampai </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td id='bulanakhirsebenarnya'>".$res[0]['end']."</td>";
        $tabx.="</tr>";
        $tabx.="<tr>";
        $tabx.="<td> Total Hutang Awal </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".number_format($res[0]['total'],2)."</td>";
        $tabx.="<td> Total Hutang </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td>".number_format($res[0]['totalpinjaman'],2)."</td>";
        $tabx.="</tr>";
        $tabx.="<tr>";
        $tabx.="<td> Keterangan </td>";
        $tabx.="<td>:</td>";
        $tabx.="<td colspan='4'>".$res[0]['keterangan']."</td>";
        $tabx.="</tr></table>";

        $str="select count(*) as jumlahbulan from ".$dbname.".sdm_angsurandt where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        $jumlahbulan=intval($res[0]['jumlahbulan']);

        $tabx.="<table cellspacing=0 border=1 width=50% style='text-align:left'><thead>";
        $tabx.="<tr>";
        if($jumlahbulan>=5){
            $pembagi=5;
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
            $tabx.="<td align=center>No</td>";
            $tabx.="<td align=center>Bulan</td>";
            $tabx.="<td align=center>Rupiah</td>";
        }else{
            $pembagi=$jumlahbulan;
            for ($i=1; $i <=$jumlahbulan; $i++) { 
                $tabx.="<td align=center>No</td>";
                $tabx.="<td align=center>Bulan</td>";
                $tabx.="<td align=center>Rupiah</td>";
            }
        }

        $tabx.="</tr>";
        $tabx.="</thead>";
        $tabx.="<tbody>";
        $no=0;
        $trx=0;

        $str="select * from ".$dbname.".sdm_angsurandt where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        foreach($res as $keyx=>$valx){
            $no++;
            if($no%$pembagi==0){
                $trx=$no+1;
            }
            if($no==$trx){
                $tabx.="<tr>";
            }
            $tabx.="<td align=center><b>".$no."</b></td>";
            $tabx.="<td id=bulan_".$no." align=center>".$valx['bulan']."</td>";
            $tabx.="<td align=center>".number_format($valx['jumlah'],0)."</td>";
            
            if($no%$pembagi==0){
                $tabx.="</tr>";
            }

        }

        $tabx.="</tbody></table><br><br>";
        $optjenistopup = "<option value=''>Pilih Data</option>";
        $optjenistopup.="<option value='0'>Penambahan Hutang</option>";
        $optjenistopup.="<option value='1'>Pelunasan Hutang</option>";


        $periodemulai='';
        $str="select min(periode) as periodegajimin from ".$dbname.".sdm_5periodegaji where  periode>='".$bulandari."' and periode<='".$bulansampai."' and sudahproses='0' ";
        $res=fetchdata($str);
        $periodemulai=$res[0]['periodegajimin'];

        $optbulan2 = "";
        for ($z = -240; $z <= 0; $z++) {
            $da = mktime(0, 0, 0, substr($bulandari, 5,7) - $z, '1', substr($bulandari, 0,4));
            
            if(date('Y-m', $da)==$bulandari){
                $optbulan2.="<option value='" . date('Y-m', $da) . "' selected>" . date('m-Y', $da) . "</option>";
            }else{
                $optbulan2.="<option value='" . date('Y-m', $da) . "'>" . date('m-Y', $da) . "</option>";
            }
        }

        $optbulan = "<option value=''>Pilih Data</option>";
        if($periodemulai!=''){
            $rangebulan = month_inbetween($periodemulai,$bulansampai);
            $datarange='';
            foreach($rangebulan as $bulan){
                $datarange.="<option value='".$bulan."'>".substr($bulan, 5,7)."-".substr($bulan, 0,4)."</option>";
            }
            for ($z = -240; $z <= 0; $z++) {
                $da = mktime(0, 0, 0, substr($periodemulai, 5,7) - $z, '1', substr($bulandari, 0,4));
                
                if(date('Y-m', $da)==$periodemulai){
                    $optbulan.="<option value='" . date('Y-m', $da) . "'>" . date('m-Y', $da) . "</option>";
                }else{
                    $optbulan.="<option value='" . date('Y-m', $da) . "'>" . date('m-Y', $da) . "</option>";
                }
            }

        }else{
            $optbulan=$optbulan2;
            $rangebulan = month_inbetween($bulandari,$bulansampai);
            $datarange='';
            foreach($rangebulan as $bulan){
                $datarange.="<option value='".$bulan."'>".substr($bulan, 5,7)."-".substr($bulan, 0,4)."</option>";
            }
        }



        $tabx.="<legend>Top-up transaksi</legend><br><br>
        <textarea id='datarange'  style='width:228px;'  onkeypress='return tanpa_kutip(event);' hidden>".$datarange."</textarea>
        <textarea id='datarange2'  style='width:228px;'  onkeypress='return tanpa_kutip(event);' hidden>".$optbulan."</textarea>
        <table cellspacing=0 border=1 width=40% style='text-align:center'>";
        if($tipeangsuran=='angsuranjangkawaktu'){
            $tabx.="<thead><tr>";
            $tabx.="<td>Jenis Top-up</td>";
            $tabx.="<td>Bulan Mulai Perubahan</td>";
            $tabx.="<td id='bulansampaitopupth'>Bulan Sampai Angsuran</td>";
            $tabx.="<td>Total Top-up</td>";
            $tabx.="<td>Action</td>";
            $tabx.="</tr></thead>";
            $tabx.="<tr>";
            $tabx.="<td><select id='jenistopup' name='jenistopup' class='select2' onchange=\"perbuahanjenistopup('".$param['notransaksi']."');\">".$optjenistopup."</select></td>";
            $tabx.="<td><select id='bulanmulaiperubahan' name='bulanmulaiperubahan' class='select2' onchange=\"hitunghutangsisa('".$param['notransaksi']."');\">".$optbulan."</select></td>";
            $tabx.="<td id='bulansampaitopuptd'><select id='bulansampaitopup' name='bulansampai' class='select2'>".$optbulan2."</select></td>";
            $tabx.="<td><input type=text id=tothutangtopup  class=myinputtextnumber   onkeypress=\"return angka_doang(event);\" value=0 onblur=change_number(this)></td>";
            $tabx.="<td hidden><input type=text id=rpbulantopup  class=myinputtextnumber size=9  onkeypress=\"return angka_doang(event);\" value=0 onblur=change_number(this) onkeyup='hitungbulantopup()'></td>";
            $tabx.="<td><img id='detail_add' title='Simpan' class='zImgBtn' onclick=\"addTopup('".$param['notransaksi']."','".$tipeangsuran."');\" src='images/save.png'</td>";
            $tabx.="</tr>";


        }elseif($tipeangsuran=='angsurannominal'){
            $tabx.="<thead><tr>";
            $tabx.="<td>Jenis Top-up</td>";
            $tabx.="<td>Bulan Mulai Perubahan</td>";
            $tabx.="<td>Total Top-up</td>";
            $tabx.="<td>Rupiah/Bulan</td>";
            $tabx.="<td>Action</td>";
            $tabx.="</tr></thead>";
            $tabx.="<tr>";
            $tabx.="<td><select id='jenistopup' name='jenistopup' class='select2' onchange=\"perbuahanjenistopup('".$param['notransaksi']."');\">".$optjenistopup."</select></td>";
            $tabx.="<td><select id='bulanmulaiperubahan' name='bulanmulaiperubahan' class='select2' onchange=\"hitunghutangsisa('".$param['notransaksi']."');\">".$optbulan."</select></td>";
            $tabx.="<td hidden><select id='bulansampaitopup' name='bulansampai' class='select2'>".$optbulan2."</select></td>";
            $tabx.="<td><input type=text id=tothutangtopup  class=myinputtextnumber   onkeypress=\"return angka_doang(event);\" value=0 onblur=change_number(this)></td>";
            $tabx.="<td><input type=text id=rpbulantopup  class=myinputtextnumber size=9  onkeypress=\"return angka_doang(event);\" value=0 onblur=change_number(this) onkeyup='hitungbulantopup()'></td>";
            $tabx.="<td><img id='detail_add' title='Simpan' class='zImgBtn' onclick=\"addTopup('".$param['notransaksi']."','".$tipeangsuran."');\" src='images/save.png'</td>";
            $tabx.="</tr>";
        }
        $arrtipetopup=array('0'=>'Penambahan Hutang','1'=>'Pelunasan Hutang');
        $tabx.="</table><legend>List Data Top-up</legend><br><br>
        <table cellspacing=0 border=1 width=40% style='text-align:center'>";
        $tabx.="<thead><tr>";
        $tabx.="<td>No. Top-up</td>";
        $tabx.="<td>Jenis Top-up</td>";
        $tabx.="<td>Bulan Mulai Perubahan</td>";
        $tabx.="<td>Bulan Sampai Angsuran</td>";
        $tabx.="<td>Rupiah/Bulan</td>";
        $tabx.="<td>Total Top-up</td>";
        $tabx.="<td>Action</td>";
        $tabx.="</tr></thead>";
        $str="select * from ".$dbname.".sdm_angsuran_topup where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        foreach($res as $keyx=>$valx){
            $tabx.="<tr>";
            $tabx.="<td>".$valx['nomotopup']."</td>";
            $tabx.="<td>".$arrtipetopup[$valx['tipetopup']]."</td>";
            $tabx.="<td>".$valx['bulanmulaiperubahan']."</td>";
            $tabx.="<td>".$valx['bulansampaiangsuran']."</td>";
            $tabx.="<td>".number_format($valx['rupiahbulanan'])."</td>";
            $tabx.="<td>".number_format($valx['rupiah'])."</td>";
            if($valx['post']==0){
                $tabx.="<td><img src=images/skyblue/posting.png class=zImgBtn  caption='Posting' onclick=\"posttopup('" . $param['notransaksi'] . "','" . $valx['nomotopup'] . "','" . $tipeangsuran . "');\"><br><img src=images/application/application_delete.png class=zImgBtn  caption='Delete TopUp' onclick=\"deltopup('" . $param['notransaksi'] . "','" . $valx['nomotopup'] . "','" . $tipeangsuran . "');\"></td>";
            }else{
                $tabx.="<td><img src=images/skyblue/posted.png class=zImgBtn  caption='Sudah diposting'></td>";
            }
        }
        $tabx.="</table>";
        echo $tabx;

    break;
    case 'hitunghutangsisa':
    $str="select sum(jumlah) as total from ".$dbname.".sdm_angsurandt where notransaksi='".$param['notransaksi']."' and bulan>='".$param['bulanmulaiperubahan']."'";
    $res=fetchdata($str);

    echo $res[0]['total'];
    break;
    case 'getedit':
        $str1="select * from ".$dbname.".sdm_angsuran where notransaksi ='".$param['notransaksi']."' ";
        $res1=fetchData($str1);
        $datasend=$res1[0]['notransaksi'].'###'.$res1[0]['karyawanid'].'###'.$res1[0]['jenis'].'###'.$res1[0]['tipe'].'###'.$res1[0]['start'].'###'.$res1[0]['end'].'###'.$res1[0]['totalpinjaman'].'###'.$res1[0]['bulanan'].'###'.$res1[0]['keterangan'];

        $str="select count(*) as jumlahbulan from ".$dbname.".sdm_angsurandt where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        $jumlahbulan=intval($res[0]['jumlahbulan']);

        $datasend2="<thead>";
        $datasend2.="<tr class=rowcontent>";
        if($jumlahbulan>=5){
            $pembagi=5;
            $datasend2.="<td align=center>No</td>";
            $datasend2.="<td align=center>Bulan</td>";
            $datasend2.="<td align=center>Rupiah</td>";
            $datasend2.="<td align=center>No</td>";
            $datasend2.="<td align=center>Bulan</td>";
            $datasend2.="<td align=center>Rupiah</td>";
            $datasend2.="<td align=center>No</td>";
            $datasend2.="<td align=center>Bulan</td>";
            $datasend2.="<td align=center>Rupiah</td>";
            $datasend2.="<td align=center>No</td>";
            $datasend2.="<td align=center>Bulan</td>";
            $datasend2.="<td align=center>Rupiah</td>";
            $datasend2.="<td align=center>No</td>";
            $datasend2.="<td align=center>Bulan</td>";
            $datasend2.="<td align=center>Rupiah</td>";
        }else{
            $pembagi=$jumlahbulan;
            for ($i=1; $i <=$jumlahbulan; $i++) { 
                $datasend2.="<td align=center>No</td>";
                $datasend2.="<td align=center>Bulan</td>";
                $datasend2.="<td align=center>Rupiah</td>";
            }
        }
        $datasend2.="</tr>";
        $datasend2.="</thead>";
        $datasend2.="<tbody>";
        $no=0;
        $trx=0;

        $datagajitutup=array();
        $str="select * from ".$dbname.".sdm_5periodegaji where periode>='".$res1[0]['start']."' and periode<='".$res1[0]['end']."' and sudahproses='1'";
        $res=fetchdata($str);
        foreach($res as $keyx=>$valx){
            $datagajitutup[$valx['periode']]=1;
        }

        $str="select * from ".$dbname.".sdm_angsurandt where notransaksi='".$param['notransaksi']."'";
        $res=fetchdata($str);
        foreach($res as $keyx=>$valx){
            $disabled='';
            if(isset($datagajitutup[$valx['bulan']])){
                $disabled='disabled';
            }
            $no++;
            if($no%$pembagi==0){
                $trx=$no+1;
            }
            if($no==$trx){
                $datasend2.="<tr class=rowcontent>";
            }
            $datasend2.="<td align=center><b>".$no."</b></td>";
            $datasend2.="<td id=bulan_".$no." align=center>".$valx['bulan']."</td>";
            $datasend2.="<td align=center><input id=rpdetailx_".$no." style=width:75px class=myinputtextnumber onkeypress=\"return angka_doang(event);\" value=".number_format($valx['jumlah'],0)." ".$disabled."><input type=hidden id=rpdetail_".$no."></td>";
            
            if($no%$pembagi==0){
                $datasend2.="</tr>";
            }

        }
            $datasend2.="<input hidden id=totaldetail value=".$no."></tbody>";

        $arrlokasitugas=makeOption($dbname, 'datakaryawan', 'karyawanid,lokasitugas'," karyawanid='".$res1[0]['karyawanid']."'");
        $lokasitugas=$arrlokasitugas[$res1[0]['karyawanid']];
        $arrpt=makeOption($dbname, 'organisasi', 'kodeorganisasi,induk'," kodeorganisasi='".$lokasitugas."'");
        $pt=$arrpt[$lokasitugas];
        
        $str="select komponengaji,jenisangsuran from ".$dbname.".sdm_angsuran_komponen where kodeorg='".$pt."' and status='1'";
        $res=fetchdata($str);
        foreach($res as $val){
            if($res1[0]['jenis']==$val['komponengaji']){
                $optjenisangsuran.="<option value='".$val['komponengaji']."' selected>".$val['jenisangsuran']."</option>";
            }else{
                $optjenisangsuran.="<option value='".$val['komponengaji']."'>".$val['jenisangsuran']."</option>";
            }
        }


        echo $datasend.'$$$'.$datasend2.'$$$'.$optjenisangsuran;
    break;
    case 'pdf':
        $pattern = "/[\s,]+/";//space Regex
        $str="select a.*,b.namakaryawan,b.nik,b.bagian from ".$dbname.".sdm_ijin a 
        left join ".$dbname.".datakaryawan b on a.karyawanid = b.karyawanid
        where a.notransaksi='".$param['notransaksi']."'";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        $bar=$res->fetch();
        $karyawancuti=$bar['karyawanid'];
        $idjenis=$bar['idjenis'];
        $periodecuti=$bar['periodecuti'];
        $keperluan=$bar['keperluan'];
        $jumlahhari=$bar['jumlahhari'];
        $drtglnya=$bar['darijam'];
        $sptglnya=$bar['sampaijam'];
       
        //tanggal pengajuan
        $tglPengajuan = preg_split($pattern, date("d n Y",strtotime($bar['tanggal'])));
        $tglPengajuan[1] = numToMonth($tglPengajuan[1],substr($_SESSION['language'],0,1),'long');
        $tglPengajuan = implode(" ",$tglPengajuan);
        //dari tanggal
        $daritanggal = preg_split($pattern, date("d n Y",strtotime($bar['darijam'])));
        $daritanggal[1] = numToMonth($daritanggal[1],substr($_SESSION['language'],0,1),'long');
        $daritanggal = implode(" ",$daritanggal);

        $sampaitanggal = preg_split($pattern, date("d n Y",strtotime($bar['sampaijam'])));
        $sampaitanggal[1] = numToMonth($sampaitanggal[1],substr($_SESSION['language'],0,1),'long');
        $sampaitanggal = implode(" ",$sampaitanggal);
        
        //$sampaitanggal=substr($bar['sampaijam'],0,10);
        $periodegaji=$bar['periodecuti'];
        $statuscuti=$bar['statuspersetujuan'];
        $statusbatalcuti=$bar['statuspersetujuan_cancel'];
        $statuspotongan=$bar['statuspotongan'];
        $potonggaji=$bar['potonggaji'];
        

        $hakcutiawal=0;
        $diambilsebelumnya=0;
        
        if($statuspotongan=='1' or $statuspotongan=='2'){
            $str="select * from ".$dbname.".sdm_cutiht where karyawanid ='".$karyawancuti."' and periodecuti='".$periodecuti."'";
            $res=fetchData($str);
            if($res[0]['hakcuti']>0){
                $hakcutiawal=$res[0]['hakcuti'];
            }

            $str="select sum(jumlahhari) as diambil from ".$dbname.".sdm_ijin where karyawanid ='".$karyawancuti."' and statuspotongan in (1,2) and tanggal<='".$bar['tanggal']."' and notransaksi!='".$param['notransaksi']."' and statuspersetujuan!=2 and statuspersetujuan_cancel!=1";
            $res=fetchData($str);
            if($res[0]['diambil']>0){
                $diambilsebelumnya=$res[0]['diambil'];
            }
        }
        

        

        

        // echo $arrdataperiode[$karyawancuti];
        // exit();
        $sisa=$hakcutiawal-$diambilsebelumnya;
        
        $sisaAfter = $sisa-$jumlahhari;
        
        
        $arrjenis = makeOption($dbname,'sdm_5jenisijin','idjenis,jenisijin',"idjenis='".$idjenis."'");

        $string = "SELECT karyawanid FROM ".$dbname.".`approval` WHERE `notransaksi` = '".$param['notransaksi']."' AND `status` = '1'";
        $hasil = fetchdata($string);

        $ttdpembuat = makeOption($dbname,'setup_ttd','karyawanid,file',"kode='TTD'");

        $style = "style='border:1px solid #000;'";
        $border = "style='border:1px solid #000;'";
        $tab="<style>table{border:1px solid #000;}th{padding:10px;}th.right{border-left:1px solid #000;}td{padding:15px;}th{padding:15px;}
        td.isi{padding:5px 15px;}.title{font-size:25px;}
        td.ttd{height:70px;border-bottom:solid 1px #000;}td.jedattd{height:2%;}td.batas{width:10px;}</style>";
        $tab .="<table class=sortable cellspacing=0 border=0 width='100%'>
                    <thead ".$border.">
                         <tr class=rowcontent>
                            <th colspan='14' align=center ".$border." class='title'><b>Leave Application Form</b></th>
                        </tr>
                         <tr class=rowcontent >
                            <th width='5%'></th>
                            <th colspan='6'>Name: ".@$bar['namakaryawan']."</th>
                            <th colspan='6' class=right>Bagian: ".@$bar['bagian']."</th>
                            <th width='5%'></th>
                        </tr>
                    </thead>
                    <thead ".$border.">
                        <tr class=rowcontent>
                            <th></th>
                            <th colspan='6'>NIK: ".@$bar['nik']."</th>
                            <th colspan='6' class=right>Form Submission Date: ".@$tglPengajuan."</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                         <tr class=rowcontent>
                            <td colspan='14' class='jedattd'></td>
                        </tr>";

                        if($statuspotongan==1 or $statuspotongan==2){
                        $tab .="<tr class=rowcontent>
                            <td></td>
                            <td colspan='12' class='isi'>Balance of Annual Leave: ".$sisa." days</td>
                            <td></td>
                        </tr>";

                        }
                        $tab .="<tr class=rowcontent>
                            <td></td>
                            <td colspan='6' class='isi'>Date of Leave: <br><b>".$daritanggal."-".$sampaitanggal."</b></td>
                            <td colspan='6' class='isi'>/ ".$jumlahhari." days</td>
                            <td></td>
                        </tr>
                        <tr class=rowcontent>
                            <td></td>
                            <td colspan='6' class='isi'>Reason: <b>".$keperluan."</b></td>
                            <td colspan='6' class='isi'></td>
                            <td></td>
                        </tr>
                        <tr class=rowcontent>
                            <td></td>
                            <td colspan='12'><u>".$arrjenis[$idjenis]."</u></td>
                            <td></td>
                        </tr>
                        ";

                        if($statuspotongan==1 or $statuspotongan==2){
                        $tab .="<tr class=rowcontent>
                            <td></td>
                            <td colspan='6'><b>Balance After: ".$sisaAfter." days</b></td>
                            <td colspan='6'></td>
                            <td></td>
                        </tr>
                        ";

                        }
                        
                       
                $tab.=" 
                    </tbody>
                </table>";
        
        $dompdf = new Dompdf();
        $dompdf->loadHtml($tab);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        $dompdf->stream("Pengobatan", array("Attachment" => false));
    break;
    
   

}




?>