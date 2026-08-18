<?php
session_start();
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');
include_once('config/connection.php');

$proses=checkPostGet('proses','');
$id=checkPostGet('absnId','');
$kdOrg=checkPostGet('kdOrg','');
$tgl=tanggalsystem(checkPostGet('tgl',''));
$arrTipeLembur=array($_SESSION['lang']['haribiasa'],$_SESSION['lang']['hariminggu'],$_SESSION['lang']['harilibur'],$_SESSION['lang']['hariraya'],"Hari Libur Spesial");
switch($proses)
{
        case'createTable':
        $table .= "<table id='ppDetailTable'>";
        # Header
        $table .= "<thead>";
        $table .= "<tr>";
        $table .= "<td>".$_SESSION['lang']['namakaryawan']."</td>";
        $table .= "<td>".$_SESSION['lang']['tipelembur']."</td>";
        $table .= "<td style=width:50px>".$_SESSION['lang']['jamaktual']."</td>";
        $table .= "<td>".$_SESSION['lang']['uangmakan']."</td>";
        $table .= "<td>".$_SESSION['lang']['penggantiantransport']."</td>";
        $table .= "<td>".$_SESSION['lang']['uangkelebihanjam']."</td>";
        $table .= "<td>Action</td>";
        $table .= "</tr>";
        $table .= "</thead>";

    # Data
    $table .= "<tbody id='detailBody'>";
        $idAbn=explode("###",$id);

        $sTpLmbr2="select tipelembur from ".$dbname.".sdm_5lembur where kodeorg='".substr($idAbn[0],0,4)."'";
		$qTpLmbr2=$owlPDO->query($sTpLmbr2) or die(print " Gagal: ".PDOException::getMessage());
		$qTpLmbr2->setFetchMode(PDO::FETCH_ASSOC);
        while($rTpLmbr2=$qTpLmbr2->fetch())
        {
                $optLmbr2.="<option value=".$rTpLmbr2['tipelembur']." >".$arrTipeLembur[$rTpLmbr2['tipelembur']]."</option>";
        }
        if(strlen($idAbn[0])>4)
        {
                $where=" subbagian='".$idAbn[0]."'";
        }
        else
        {
                $where=" lokasitugas='".$idAbn[0]."'"; //echo"warning:".$where;exit();
        }
        $optKry=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',$where,0);
        $optAbsen=makeOption($dbname,'sdm_5absensi','kodeabsen,keterangan') ;
        for($t=0;$t<24;)
        {
                if(strlen($t)<2)
                {
                        $t="0".$t;
                }
                $jm.="<option value=".$t." ".($t==00?'selected':'').">".$t."</option>";
                $t++;
        }
        for($y=0;$y<60;)
        {
                if(strlen($y)<2)
                {
                        $y="0".$y;
                }
                $mnt.="<option value=".$y." ".($y==00?'selected':'').">".$y."</option>";
                $y++;
        }



        $table .= "<tr id='detail_tr' class='rowcontent'>";
        $table .= "<td>".makeElement("krywnId",'select','',
        array('style'=>'width:150px'),$optKry)."</td>";
        $table .= "<td><select id=tpLmbr>".$optLmbr2."</select></td>";
        $table .= "<td><select id=jmId name=jmId >".$jm."</select>:<select id=mntId name=mntId >".$mnt."</select></td>";
        $table .= "<td>".makeElement("uang_mkn",'textnum',0,
        array('style'=>'width:100px','onkeypress'=>'return angka_doang(event)','maxlength'=>'10','onblur'=>"chngeFormat()",'onfocus'=>"normal_number_1()"))."</td>";
        $table .= "<td>".makeElement("uang_trnsprt",'textnum',0,
        array('style'=>'width:100px','onkeypress'=>'return angka_doang(event)','maxlength'=>'10','onblur'=>"chngeFormat()",'onfocus'=>"normal_number_2()"))."</td>";
        $table .= "<td>".makeElement("uang_lbhjm",'textnum',0,
        array('style'=>'width:100px','onkeypress'=>'return angka_doang(event)','maxlength'=>'10','onblur'=>"chngeFormat()",'onfocus'=>"normal_number_3()"))."</td>";

    # Add, Container Delete
    $table .= "<td align=center><img id='detail_add' title='Simpan' class=zImgBtn onclick=\"addDetail()\" src='images/save.png'/>";
    $table .= "&nbsp;<img id='detail_delete' /></td>";
    $table .= "</tr>";
    $table .= "</tbody>";
    $table .= "</table>";
    echo $table;
        break;
    
	case'loadDetail':
		$str="select * from ".$dbname.".keu_5akun";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optakun[$bar['noakun']]=$bar['namaakun'];
		}
		$str="select * from ".$dbname.".setup_kegiatan";
		$res=fetchdata($str);
		foreach($res as $bar){
			$optakun[$bar['kodekegiatan']]=$bar['namakegiatan'];
		}
		
		$e="";
		$k="style=display:none;";
		if($_SESSION['empl']['tipelokasitugas']!='KEBUN'){
			$e="style=display:none;";
		}
		$optjabatan=makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan');
		$optdept=makeOption($dbname,'sdm_5departemen','kode,nama');
		$optnmtipe=makeOption($dbname,'sdm_5tipekaryawan','id,tipe');
	
        $sDt="select * from ".$dbname.".sdm_lemburdt where kodeorg='".$kdOrg."' and tanggal='".$tgl."'";
		$qDt=$owlPDO->query($sDt) or die(print " Gagal: ".PDOException::getMessage());
		$qDt->setFetchMode(PDO::FETCH_ASSOC);
        $totum=$totut=$totle=0;
        while($rDet=$qDt->fetch()){
            $strdkar = "select karyawanid from ".$dbname.".datakaryawan_hist a where karyawanid='".$rDet['karyawanid']."'  and approval_status='8' and version_type='B' and periodegaji='".substr($tgl, 0,6)."' "; 
            $resdkar = fetchdata($strdkar);
            if(count($resdkar)>0)
            { 

            $sNm="select * from ".$dbname.".datakaryawan_hist where  karyawanid='".$rDet['karyawanid']."' and approval_status='8' and version_type='B' and periodegaji='".substr($tgl, 0,6)."' order by namakaryawan";
            }
            else
            {
            $sNm="select * from ".$dbname.".datakaryawan where karyawanid='".$rDet['karyawanid']."' order by namakaryawan";

            }
			//$sNm="select * from ".$dbname.".datakaryawan where karyawanid='".$rDet['karyawanid']."'";
			$qNm=$owlPDO->query($sNm) or die(print " Gagal: ".PDOException::getMessage());
			$qNm->setFetchMode(PDO::FETCH_ASSOC);
			$rNm=$qNm->fetch();
			$no+=1;
			$opttipe=makeOption($dbname,'datakaryawan','karyawanid,tipekaryawan',"karyawanid='".$rNm['karyawanid']."'");
			echo"
			<tr class=rowcontent>
                                <td align=center>".$no."</td>
                                <td>".$rNm['nik']."</td>
                                <td>".$rNm['namakaryawan']."</td>
                                <td>".$optjabatan[$rNm['kodejabatan']]."</td>
                                <td>".$rNm['subbagian']."</td>
                                <td>".$optdept[$rNm['bagian']]."</td>
                                <td>".$optnmtipe[$opttipe[$rNm['karyawanid']]]."</td>
                                <td align=left ".$e.">".$optakun[$rDet['noakun']]."</td>
                                <td align=left ".$e." ".$k.">".$rDet['alokasi']."</td>
                                <td>".$arrTipeLembur[$rDet['tipelembur']]."</td>
                                <td align=center>".$rDet['jamaktual']."</td>
                                <td hidden align=right>".number_format($rDet['uangmakan'],2)."</td>
                                <td hidden align=right>".number_format($rDet['uangtransport'],2)."</td>
                                <td align=right>".number_format($rDet['uangkelebihanjam'],2)."</td>
                                <td align=right>".$rDet['jammulai']."</td>
                                <td align=right>".$rDet['jamselesai']."</td>
                                <td align=left>".$rDet['ket']."</td>
                                
                                <td align=center width=25px><img src=images/application/application_edit.png class=zImgBtn  title='Edit' onclick=\"editDetail('".$rDet['karyawanid']."','".$rDet['tipelembur']."','".$rDet['jamaktual']."','".$rDet['uangmakan']."','".$rDet['uangtransport']."','".$rDet['uangkelebihanjam']."','".$rDet['jammulai']."','".$rDet['jamselesai']."','".$rDet['ket']."','".$rDet['noakun']."','".$rDet['alokasi']."','".$rNm['namakaryawan']."');\"></td>
                                <td align=center width=25px><img src=images/application/application_delete.png class=zImgBtn  title='Delete' onclick=\"delDetail('".$rDet['kodeorg']."','".tanggalnormal($rDet['tanggal'])."','".$rDet['karyawanid']."');\" ></td>
			</tr>
			";
			$totum+=$rDet['uangmakan'];
			$totut+=$rDet['uangtransport'];
			$totle+=$rDet['uangkelebihanjam'];
        }
    //             echo"
    //             <tr class=rowcontent>
    //             <td colspan=4 align=center>Total</td>
    //             <td hidden align=right>".number_format($totum,2)."</td>
    //             <td hidden  align=right>".number_format($totut,2)."</td>
    //             <td align=right>".number_format($totle,2)."</td>
    //             <td></td>
				// <td></td>
				// <td></td>
				// <td></td>
    //             </tr>
    //             ";
        break;
        default:
        break;
}
?>