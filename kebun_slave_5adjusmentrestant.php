<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$method=checkPostGet('method','');
$kodeorg=checkPostGet('kodeorg','');
$divisi=checkPostGet('divisi','');
$blok=checkPostGet('blok','');
$tgl=checkPostGet('tgl','');
$tgl=tanggalsystemn($tgl);
$adjust=checkPostGet('adjust','');
$tahuntanam=checkPostGet('tahuntanam','');
$ket=checkPostGet('ket','');
$restan=checkPostGet('restan','');

$tglsrc = tanggalsystem(checkPostGet('tglsrc', ''));
$bloksrc = checkPostGet('bloksrc', '');

$nmOrg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
$namakaryan=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');

switch($method){   
    case 'update':
		#adj tidak boleh minus dan nol
		if(abs($adjust)>abs($restan)){
			exit("Error : Janjang penyesuaian tidak boleh lebih besar dari janjang restant.");
		}
		
		#ambil data sebelumnya
		$str=" select * from ".$dbname.".kebun_rekappnn where blok='".$blok."' and tanggal = '".$tgl."'";
		$bar = fetchdata($str);
		$bjr=$bar[0]['bjr'];
		$jjgpanen=$bar[0]['jjgpanen'];
		$kgkebunawal=$bar[0]['kgkebun'];
		
		$optt=makeOption($dbname,'setup_blok','kodeorg,tahuntanam',"kodeorg='".$blok."'");
		
		if(count($bar)>0){
			#update kebun_rekappnn
			$str="UPDATE `kebun_rekappnn` SET
			`jjgafkir` = '".$adjust."',
			`keterangan` = '".$ket."',
			`keterangan` = 'Penyesuaian Oleh ".$_SESSION['standard']['username'].", ".$ket."',
			`updateby` = '".$_SESSION['standard']['userid']."'
			WHERE `tanggal` = '".$tgl."' AND `blok` = '".$blok."' ";
			try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
		}else{
			#insert
			$str="INSERT INTO `kebun_rekappnn` (`divisi`,`tanggal`, `blok`, `tahuntanam`, `jjgafkir`,`keterangan`, `updateby`,`updatetime`,`posting`, `postingby`, `postingdate`)
			VALUES ('".$divisi."','".$tgl."', '".$blok."', '".$optt[$blok]."', '".$adjust."', 'Penyesuaian Oleh ".$_SESSION['standard']['username'].", ".$ket."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."','1','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
			try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
		}
		
		#insert sebagai log
		$str="INSERT INTO `kebun_rekappnn_adj` (`id`,`tanggal`, `blok`, `jjgpanenawal`, `jjgpanenadjust`, `kgkebunawal`, `kgkebunadjust`, `keterangan`, `updateby`, `postingby`, `postingdate`)
		 VALUES ('','".$tgl."', '".$blok."', '', '".$adjust."', '', '','Penyesuaian Oleh ".$_SESSION['standard']['username']."', '".$_SESSION['standard']['userid']."','".$_SESSION['standard']['userid']."','".date('Y-m-d H:i:s')."')";
		try{$owlPDO->exec($str);}catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "<br/>"; die(); }
		
    break;
    
    case'loadData':
            echo"<div id=container>
                    <table class=sortable cellspacing=1 border=0>
					 <thead>
						 <tr class=rowheader>
							<td align=center>No</td>
							<td align=center>".$_SESSION['lang']['divisi']."</td>
							<td align=center>".$_SESSION['lang']['blok']."</td>
							<td align=center>".$_SESSION['lang']['tanggal']."</td>
							<td align=center>".$_SESSION['lang']['Penyesuaian']."<br>(Jjg)</td> 
							<td align=center>Keterangan</td>
							<td align=center>updateby [empl_name]</td>
							<td align=center>updatetime</td></tr>
						 </tr>
                        </thead>
                        <tbody>";

			$where="";
			if ($bloksrc != '') {
				$where.=" and blok like '%" . $bloksrc . "%' ";
			}
			if ($tglsrc != '') {
				$where.=" and tanggal like '%" . $tglsrc . "%' ";
			}
	
			
            $limit=20;
            $page=0;
            if(isset($_POST['page']))
            {
            $page=$_POST['page'];
            if($page<0)
            $page=0;
            }
            $offset=$page*$limit;
            $maxdisplay=($page*$limit);

            $ql2="select count(*) as jmlhrow from ".$dbname.".kebun_rekappnn_adj where '".$_SESSION['empl']['lokasitugas']."'  = substr(blok,1,4) ".$where."";
            $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
            $query2->setFetchMode(PDO::FETCH_OBJ);
            while($jsl=$query2->fetch()){  
                $jlhbrs= $jsl->jmlhrow;
            }
            $no=$maxdisplay;
            $str="select * from ".$dbname.".kebun_rekappnn_adj where '".$_SESSION['empl']['lokasitugas']."'  = substr(blok,1,4) ".$where." order by updatetime desc limit ".$offset.",".$limit."";
			
            $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while($bar=$res->fetch()){
                $no+=1;
                echo "<tr class=rowcontent>";
                echo "<td align=center>".$no."</td>";
				echo "<td align=left>".substr($bar['blok'],0,6)."</td>";
				echo "<td align=left>".$bar['blok']."</td>";
				echo "<td align=left>".$bar['tanggal']."</td>";
				echo "<td align=right>".@number_format($bar['jjgpanenadjust'])."</td>";
				echo "<td>".$bar['keterangan']."</td>";
				echo "<td>".$namakaryan[$bar['updateby']]."</td>";
				echo "<td>".$bar['updatetime']."</td>";
                
                echo "</tr>";
            }
            echo"
            <tr class=rowheader><td colspan=11 align=center>
            ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
            <button class=mybutton onclick=loadData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
            <button class=mybutton onclick=loadData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
            </td>
            </tr>";
    break;
	
	case 'getdivisi':
		$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$kodeorg."%' and tipe ='AFDELING' order by kodeorganisasi asc";
		$optdivisi.="<option value=''></option>";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){	
				$optdivisi.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}		
			echo $optdivisi;
			break;
	case 'getblok':
		$str="select kodeorganisasi, namaorganisasi from ".$dbname.".organisasi where kodeorganisasi like '".$divisi."%' and tipe ='BLOK' order by kodeorganisasi asc";
		// exit ('error :' .$str);
		$optblok.="<option value=''></option>";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){	
				$optblok.="<option value='".$bar['kodeorganisasi']."'>".$bar['kodeorganisasi']." - ".$bar['namaorganisasi']."</option>";
			}		
			echo $optblok;
			break;
	case 'gettahuntanam':
		$str="select kodeorg, tahuntanam from ".$dbname.".setup_blok where kodeorg = '".$blok."'";
		$tahuntanam.="";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_ASSOC);
			while($bar=$res->fetch()){	
				$tahuntanam = $bar['tahuntanam'];
			}		
			echo $tahuntanam;
			break;
	case 'getrestan':
		$str=" select sum(jjgpanen) as jjgpanen,sum(jjgafkir) as jjgafkir from ".$dbname.".kebun_rekappnn_vw where blok='".$blok."' and tanggal <= '".$tgl."' and posting='1'";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jjgpanen=$bar['jjgpanen'];
			$jjgafkir=$bar['jjgafkir'];	
		
		$str=" select sum(jjg) as jjg from ".$dbname.".kebun_spb_vw where blok='".$blok."' and tanggal <= '".$tgl."' ";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$res->fetch();
			$jjgspbvw=$bar['jjg'];
		
		$restan=$jjgpanen-$jjgafkir-$jjgspbvw;
		echo $restan . "##";
		break;
default:
}
?>