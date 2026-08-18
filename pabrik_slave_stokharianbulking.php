<?php

require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$proses = checkPostGet('proses', '');
$pages = checkPostGet('page','');

$notransaksi = checkPostGet('notransaksi', '');
$kodept = checkPostGet('kodept', '');
$tanggal = checkPostGet('tanggal', '');
$kodeunit = checkPostGet('kodeunit', '');
$kodetangki = checkPostGet('kodetangki', '');
$kuantitas = checkPostGet('kuantitas', '');
$ffa = checkPostGet('ffa', '');
$moisture = checkPostGet('moisture', '');
$dirt = checkPostGet('dirt', '');
$keterangan = checkPostGet('keterangan', '');


$notransaksisch = checkPostGet('notransaksisch', '');
$tanggalsch = checkPostGet('tanggalsch', '');
$kodeptsch = checkPostGet('kodeptsch', '');
$kodetangkisch = checkPostGet('kodetangkisch', '');


$createtime=date('Y-m-d H:i:s');

switch($proses){
	case'getunit':
        $optgetunit.="<option value=''>Pilih Data</option>";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PABRIK' and induk='".$kodept."' order by kodeorganisasi asc";

		$res=fetchdata($str);
		foreach($res as $val){
            if ($kodeunit==$val['kodeorganisasi']) {
              $optgetunit.="<option value='".$val['kodeorganisasi']."' selected>".$val['namaorganisasi']."</option>";
                
            }
            else
            {
			  $optgetunit.="<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";

            }
		}
		echo $optgetunit;

    break;

    case'gettangki':
        $str="select * from ".$dbname.".pabrik_5tangki where kodeorg='".$kodeunit."' order by kodetangki asc";


        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($sres=$res->fetch())
        {
            if ($kodetangkie==$sres['kodetangki']) {
        $optgettangki.="<option value='".$sres['kodetangki']."' selected>".$sres['keterangan']."</option>";
            }

            else
            {
        $optgettangki.="<option value='".$sres['kodetangki']."'>".$sres['keterangan']."</option>";

            }
        }

     

        echo $optgettangki;


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
		if($notransaksisch!=''){
			$where.=" and notransaksi like '%".$notransaksisch."%'";
		}

        if($tanggalsch!=''){
            $where.=" and tanggal like '%".$tanggalsch."%'";
        }

        if($kodeptsch!=''){
            $where.=" and kodept like '%".$kodeptsch."%'";
        }

         if($kodetangkisch!=''){
            $where.=" and kodetangki like '%".$kodetangkisch."%'";
        }

		/*if($daTtgl!=''){
			$thn=substr($daTtgl,6,4);
			$bln=substr($daTtgl,3,2);
			$tgl=substr($daTtgl,0,2);
			$where.=" and createtime like '%".$thn.'-'.$bln.'-'.$tgl."%'";
		}*/
		

        $str = "select count(*) as jmlhrow from ".$dbname.".pabrik_stokharianbulking where 1=1 ".$where." order by `createtime` desc";
		$res=fetchdata($str);
		$jlhbrs = $res[0]['jmlhrow'];
		
		if($jlhbrs <= 0){
			$tab.="<tr class=rowcontent><td colspan='14' style='text-align:center'>".$_SESSION['lang']['errdatanotexist']."</td></tr>";
		}else{
			$no = 0;
			
			$str = "select * from ".$dbname.".pabrik_stokharianbulking where 1=1 ".$where." order by createtime desc limit ".$offset.",".$limit."";
  
			$res=fetchdata($str);
			foreach($res as $val){
				$no++;
				
                $nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['kodept']."'");
                $nmunit=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$val['kodeunit']."'");
				$nmtangki=makeOption($dbname,'pabrik_5tangki','kodetangki,keterangan',"kodetangki='".$val['kodetangki']."'");
				$tab.="<tr class=rowcontent id='tr_".$no."'>
					<td align=center>".$no."</td>
					<td>".$val['notransaksi']."</td>
					<td style='min-width:80px;text-align:center'>".tanggalnormal($val['tanggal'])."</td>
                    <td align:right>".$nmorg[$val['kodept']]."</td>
                    <td align:right>".$nmunit[$val['kodeunit']]."</td>
			        <td align:right>".$nmtangki[$val['kodetangki']]."</td>
                    <td align:right>".number_format($val['kuantitas'])."</td>
                    <td align:right>".$val['ffa']."</td>
                    <td align:right>".$val['dirt']."</td>
					<td align:right>".$val['moisture']."</td>
					<td align:left>".$val['keterangan']."</td>
					<td align:left>".getNamaKaryawan($val['updateby'])."</td>";
				
					$tab.="<td style='text-align:center'>
						<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$val['notransaksi']."','".$val['tanggal']."','".$val['kodept']."','".$val['kodeunit']."','".$val['kodetangki']."','".$val['kuantitas']."','".$val['ffa']."','".$val['dirt']."','".$val['moisture']."','".$val['keterangan']."');\">&nbsp;
						<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldata('".$val['notransaksi']."');\">";
				
				$tab.="</tr>";
			}
			
			  $skeupenagih = "select count(*) as rowd from " . $dbname . ".pabrik_stokharianbulking";
        $qkeupenagih = $owlPDO->query($skeupenagih) or die(print " Gagal: " . PDOException::getMessage());
        $rkeupenagih = owlBaris($qkeupenagih);
        $totrows = ceil($rkeupenagih / $limit);
        
        if ($totrows == 0) {
            $totrows = 1;
        }
        $isiRow = '';
        for ($er = 1; $er <= $totrows; $er++) {
            $sel = ($page==$er-1)? 'selected': '';
            $isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
        }
        $footd = "</tr>
            <tr><td colspan=14 align=center>
            
            <button class=mybutton onclick=loaddata(" . ($page - 1) . ");>" . $_SESSION['lang']['pref'] . "</button>
            <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">" . $isiRow . "</select>
            <button class=mybutton onclick=loaddata(" . ($page + 1) . ");>" . $_SESSION['lang']['lanjut'] . "</button>
            </td>
            </tr>";
            $tab.="</table>";
        }
        
        echo $tab;
        echo  $footd;
	break;

    case'insert':

          if($notransaksi==''){
            $notransaksi = generateNotransaksi();

    
        }else{
            $notransaksi = $notransaksi;
       
        }


        if (($tanggal == '') || ($kodept == '') || ($kodeunit == '') || ($kodetangki == '') || ($kuantitas == '')) {
            echo"warning:Please Complete The Form";
            exit();
        }
        

        $sCek = "select notransaksi,createtime from " . $dbname . ".pabrik_stokharianbulking where notransaksi='" . $kontrak . "' ";

		$qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
		$rCek=owlBaris($qCek);
        if ($rCek < 1) {
            $sIns = "insert into " . $dbname . ".pabrik_stokharianbulking (notransaksi, tanggal, kodept, kodeunit, kodetangki, kuantitas,ffa,moisture,dirt,keterangan,createdby,createtime) values ('" . $notransaksi . "','" . tanggalSystemn($tanggal) . "','" . $kodept . "','" . $kodeunit . "','" . $kodetangki . "','" . $kuantitas . "','" . $ffa . "','" . $moisture . "','" . $dirt . "','" . $keterangan . "','" . $_SESSION['standard']['userid'] . "','" . $createtime . "')";

            
            try{
				$owlPDO->exec($sIns); 
			}
			catch (PDOException $e){
				echo "DB Error : " . $e->getMessage();
			}
        }
        else {
            echo"warning:Data Already Entry";
            exit();
        }
        break;

    case'showData':
        $sql = "select * from " . $dbname . ".pabrik_stokharianbulking where notransaksi='" . $notransaksi . "'";
		$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
		$query->setFetchMode(PDO::FETCH_ASSOC);
        $res = $query->fetch();
        echo $res['notransaksi'] . "###" . $res['tanggal'] . "###" . $res['kodept']. "###" . $res['kodeunit']. "###" . $res['kodetangki']. "###" . $res['kuantitas']. "###" . $res['ffa']. "###" . $res['moisture']. "###" . $res['dirt']. "###" . $res['keterangan'] ;
        break;
    case'update':
       
        $sUpd = "update " . $dbname . ".pabrik_stokharianbulking set  createtime='" . checkPostGet('tgl', '') . "', tanggal='" . $tanggal . "', kodept='" . $kodept . "', kodeunit='" . $kodeunit . "', kodetangki='" . $kodetangki . "', kuantitas='" . $kuantitas . "', ffa='" . $ffa . "', moisture='" . $moisture . "', dirt='" . $dirt . "',keterangan='" . $keterangan . "',updateby='".$_SESSION['standard']['userid']."' where  notransaksi='" . $notransaksi . "'";
       
        try{
			$owlPDO->exec($sUpd); 
		}
		catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
		}

        break;

    case'delData':
        $sDel = "delete from " . $dbname . ".pabrik_stokharianbulking where  notransaksi='" . $notransaksi . "'";

        try{
			$owlPDO->exec($sDel); 
		}
		catch (PDOException $e){
			echo "DB Error : " . $e->getMessage();
		}
        break;

    case'CekData':
       
        break;

    
    default:
        break;
}

function generateNotransaksi() {
    global $dbname;
    global $conn;
    global $_POST;
    global $kodept;
    global $tanggal;
    global $kodetangki;
    global $owlPDO;


    $tgl=tanggalSystemn($tanggal);
    $tmpTgl = explode('-',$tgl);
    
    
    
 /*   $str="select count(*) as nomor from ".$dbname.".pmn_suratperintahpengiriman_nodo where 
    substr(tanggaldo,1,4) = '".$tmpTgl[0]."' and kodept = '".$bar1['kodept']."' 
    and kodebarang='".$bar1['kodebarang']."'";
    
    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
    $res->setFetchMode(PDO::FETCH_ASSOC);
    $bar=$res->fetch();
    $noDO=$bar['nomor']+1;*/
    
    

   
        $notransaksi=$tmpTgl[0].$tmpTgl[1].$tmpTgl[2]."/STBW /".$kodept."/".$kodetangki;
   
    //exit('error'.$notransaksi); 
    return $notransaksi;

}
?>