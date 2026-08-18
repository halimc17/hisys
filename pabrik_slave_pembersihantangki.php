<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method=checkPostGet('method','');
$pabrik=checkPostGet('pabrik','');
$tangki=checkPostGet('tangki','');
$barang=checkPostGet('barang','');
$tipe=checkPostGet('tipe','');
$tgl=tanggalsystemn(checkPostGet('tgl',''));
$jm=checkPostGet('jm','');
$mn=checkPostGet('mn','');
$jumlah=checkPostGet('jumlah','');
$ket=checkPostGet('ket','');
$noba=checkPostGet('noba','');
$sawal=checkPostGet('sawal','');
$jmlRey=checkPostGet('jmlRey','');
$nodok=checkPostGet('nodok','');

$brgSch=checkPostGet('brgSch','');
$tglSch=tanggalsystemn(checkPostGet('tglSch',''));

$tanggal=$tgl.' '.$jm.':'.$mn;

switch($method){
	
	case'getTangki':
        $optTangki.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $str="select kodetangki,keterangan from ".$dbname.".pabrik_5tangki where kodeorg='".$pabrik."' and komoditi in ('CPO','KER') ";
        $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while($bar=$res->fetch())
        {
			$select='';
			if($bar['kodetangki']==$tangki){
				$select='selected';
			}
            $optTangki.="<option ".$select." value=".$bar['kodetangki'].">".$bar['keterangan']."</option>";
        }
        echo $optTangki;
    break;
    
    case'getBarang':
        $arrKdBrg=array('CPO'=>'40000001','KER'=>'40000002');
        $arrNmBrg=array('CPO'=>'CRUDE PALM OIL (CPO)','KER'=>'PALM KERNEL (PK)');
        $iBarang="select komoditi from ".$dbname.".pabrik_5tangki where kodeorg='".$pabrik."' and kodetangki='".$tangki."'";
        $nBarang=$owlPDO->query($iBarang) or die(print " Gagal: ".PDOException::getMessage());
        $nBarang->setFetchMode(PDO::FETCH_ASSOC);
        while($dBarang=$nBarang->fetch())
        {
            $optBrg.="<option value=".$arrKdBrg[$dBarang['komoditi']].">".$arrNmBrg[$dBarang['komoditi']]."</option>";
        }
        echo $optBrg;
    break;

	########### case insert header
   
        
    case 'update':
		
		$str="delete from ".$dbname.".approval where notransaksi='".$nodok."' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}
		
        $str="update ".$dbname.".pabrik_pembersihantangki set 
			kodetangki='".$tangki."',
			kodebarang='".$barang."',
			tipe='".$tipe."',
			tanggal='".$tanggal."',
			keterangan='".$ket."',
			updateby='".$_SESSION['standard']['userid']."'
			where notransaksi='".$nodok."'";
			// exit("Error:$str");
        try {
            $owlPDO->exec($str);
			for($i=1; $i<4; $i++){
					if($per['persetujuan'.$i]!=''){
						$str="insert into ".$dbname.".approval (`notransaksi`,`jenispersetujuan`,`level`,`karyawanid`) values 
						  ('".$nodok."','".$jenispersetujuan."','".$i."','".$per['persetujuan'.$i]."')";

						try{
			            	$owlPDO->exec($str); 
				        }catch(PDOException $e){
				            echo " Gagal," . addslashes($e->getMessage());
				        }
					}
				}
        }
        catch (PDOException $e){
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }
    break;
    
    case'getNodok':
        
        $iList="select notransaksi,tanggal,statasiun  from ".$dbname.".pabrik_rawatmesinht where statasiun ='".$station."' "
            . "and tanggal='".$tglOrder."' order by notransaksi desc limit 1";
        $nList=$owlPDO->query($iList) or die(print " Gagal: ".PDOException::getMessage());
        $nList->setFetchMode(PDO::FETCH_ASSOC);
        $dList=$nList->fetch();
        
            
        if($dList['notransaksi']!='')
        {
            $listDok=  explode('/', $dList['notransaksi']);
            $noUrut=$listDok[2]+1;
        }
        else
        {
            $noUrut=1;
        }
        $counter=addZero($noUrut,4);
        $noDok=$station.'/'.$tglOrderDok.'/'.$counter;
        echo $noDok;
        
    break;
    
    case'getMesin':
        $optMesin.="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
        $optMesin.="<option value='Others'>Others</option>";
        $iMesin="select * from ".$dbname.".organisasi where induk='".$station."' ";
        $nMesin=$owlPDO->query($iMesin) or die(print " Gagal: ".PDOException::getMessage());
        $nMesin->setFetchMode(PDO::FETCH_ASSOC);
        while($dMesin=$nMesin->fetch())
        {
            if($mesin==$dMesin['kodeorganisasi'])
            {$select="selected=selected";}
            else
            {$select="";}
           
            $optMesin.="<option ".$select." value=".$dMesin['kodeorganisasi'].">".$dMesin['namaorganisasi']."</option>";
        }
        echo $optMesin;
    break;
	
	
    case'loadData':
            echo"
            <table cellspacing=1 border=0 class=sortable>
            <thead>
             <tr class=rowheader>
				<td align=center>No</td>
				<td align=center>".$_SESSION['lang']['nodok']."</td>
				<td align=center>".$_SESSION['lang']['pabrik']."</td>
				<td align=center>".$_SESSION['lang']['tangki']."</td>   
				<td align=center>".$_SESSION['lang']['tipe']."</td>    
				<td align=center>".$_SESSION['lang']['tanggal']."</td>
				<td align=center>".$_SESSION['lang']['keterangan']."</td>
				<td align=center>".$_SESSION['lang']['action']."</td></tr>
			 </tr>
            </thead>
            <tbody>
            ";//<td align=center>".$_SESSION['lang']['kdpabrik']."</td>

           //exit("Error:$schTgl");
            $wheresch='';
            if($schNodok!='') {
                $wheresch.=" and notransaksi like '%".$schNodok."%' ";
            }
            
            if($schTgl!=''){
                $wheresch.=" and tanggal like '%".$schTgl."%' ";
            }
			
			if($schstation!=''){
                $wheresch.=" and statasiun='".$schstation."' ";
            }

			

             $limit=10;
            $page=0;
            if(isset($_POST['page']))
            {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
            }
            $offset=$page*$limit;
            $maxdisplay=($page*$limit);

            $ql2="select count(*) as jmlhrow from ".$dbname.".pabrik_pembersihantangki where kodeorg='".$_SESSION['empl']['lokasitugas']."' ".$wheresch."  ";// echo $ql2;notran
            $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
            $query2->setFetchMode(PDO::FETCH_OBJ);
            while($jsl=$query2->fetch()){  
                $jlhbrs= $jsl->jmlhrow;
            }
            $no=$maxdisplay;
            $iList="select * from ".$dbname.".pabrik_pembersihantangki where kodeorg='".$_SESSION['empl']['lokasitugas']."' 
			".$wheresch." limit ".$offset.",".$limit."";
            $nList=$owlPDO->query($iList) or die(print " Gagal: ".PDOException::getMessage());
            $nList->setFetchMode(PDO::FETCH_ASSOC);
            while($dList=$nList->fetch()){
				
				
				$postDt="style='cursor:pointer' onclick=ajukan('".$dList['notransaksi']."')";
				$str="select * from ".$dbname.".approval where notransaksi='". $dList['notransaksi']."' order by  level asc";
				// exit("Error:$str");
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				while($bar=$res->fetch()){
					$kar[$bar['level']]=$bar['karyawanid'];
				}
                $no+=1;
               echo "<tr class=rowcontent>";
                echo "<td align=center>".$no."</td>";
                echo "<td align=left>".$dList['notransaksi']."</td>";
                echo "<td align=left>".$dList['kodeorg']."</td>";
                echo "<td align=left>".$dList['kodetangki']."</td>";
                
                echo "<td align=left>".$dList['tipe']."</td>";
                echo "<td align=left>".tanggalnormal($dList['tanggal'])." ".substr($dList['tanggal'],11,8)."</td>";
                echo "<td align=left>".$dList['keterangan']."</td>";

                 echo "<td align=center>";

                if($dList['statuspersetujuan']==0){
					echo"  
					
						<img src=images/application/application_edit.png class=resicon  caption='Edit' 
                        onclick=\"fillField('".$dList['notransaksi']."','".$dList['kodeorg']."','".$dList['kodetangki']."',
                        '".$dList['kodebarang']."','".$dList['tipe']."','".tanggalnormal(substr($dList['tanggal'],0,10))."',
						'".substr($dList['tanggal'],11,2)."','".substr($dList['tanggal'],14,2)."','".$dList['keterangan']."',
						'".$kar['1']."','".$kar['2']."','".$kar['3']."');\">
					<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deleteHead('".$dList['notransaksi']."');\" >";
                    echo"<img src=images/skyblue/posting.png class=resicon  title='Posting' ".$postDt." >";
                }else{
                    echo"<img src=images/skyblue/posted.png class=resicon  title='Posted' >";
                }
                // echo"<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('pabrik_rawatmesinht','".$dList['notransaksi']."','','pabrik_slave_perbaikan_pdf',event)\">";
                echo"</td></tr>";//,`komentarmainten`,`komentarproses`
            }
            echo"
            <tr class=rowheader><td colspan=12 align=center>
            ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
            <button class=mybutton onclick=loaddata(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <button class=mybutton onclick=loaddata(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
            echo"</tbody></table>";
            break;
		
		
	##########case delete
	case 'deleteHead':
		$str="delete from ".$dbname.".approval where notransaksi='".$nodok."' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}
		
		$str="delete from ".$dbname.".pabrik_pembersihantangki where notransaksi='".$nodok."' ";
		try {
			$owlPDO->exec($str);
		} catch (PDOException $e) {
			print " Gagal  !: " . $e->getMessage() . "<br/>"; 
			die(); 
		}
	break;
	
	
	
	
}
?>	