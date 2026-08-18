<?php
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
require_once('lib/fpdf.php');
require_once('lib/terbilang.php');


$proses=checkPostGet('proses','');
$kdBlok=checkPostGet('kdBlok','');
$kdKebun=checkPostGet('kdKebun','');
$kdKebun2=checkPostGet('kdKebun2','');
$kelaspohon=checkPostGet('kelaspohon','');
$thnProd=checkPostGet('thnProd','');
$jmBjr=checkPostGet('jmBjr','');
$bln=checkPostGet('bln','');
$tahun=checkPostGet('tahun','');
$periode1=checkPostGet('periode1','');
$periode2=checkPostGet('periode2','');

$optNmOrg=makeOption($dbname, 'organisasi', 'kodeorganisasi,namaorganisasi');

	switch($proses)
	{
		case'insert':
		if($jmBjr==''||$thnProd==''||$kdBlok=='')
		{
			echo"warning:Field tidak boleh kosong";
			exit();
		}
		else
		{	
			$sDel="delete from ".$dbname.".kebun_5bjr where periode='".$thnProd."' and kodeorg='".$kdBlok."'";
			try{
				$owlPDO->exec($sDel); 
				$sIns="insert into ".$dbname.".kebun_5bjr (periode,tahunproduksi,kodeorg,kelaspohon,bjr,updateby) values ('".$thnProd."','".substr($thnProd,0,4)."','".$kdBlok."','".$kelaspohon."','".$jmBjr."','".$_SESSION['standard']['userid']."')";
				
				try{
					$owlPDO->exec($sIns); 
					echo"";
				}catch (PDOException $e){
					echo"Gagal:Db Error".$sIns."__".$e->getMessage();
					die();
				}
			}catch (PDOException $e){
				echo"Gagal:Db Error".$sDel."__".$e->getMessage();
				die();
			}
		}
		break;
		case'loadData':
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
				$no=0;
				$no=$maxdisplay;
				
                 $sql2="select count(b.kodeorg) as jmlhrow from ".$dbname.".setup_blok a left join ".$dbname.".kebun_5bjr b on a.kodeorg=b.kodeorg
                      where b.kodeorg like '".$kdKebun."%' and a.statusblok in ('TM','TBM')  and b.periode='".$thnProd."' and a.luasareaproduktif!=0 order by b.kodeorg asc";
					$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
					$query2->setFetchMode(PDO::FETCH_OBJ);
                    while($jsl=$query2->fetch()){
                    $jlhbrs= $jsl->jmlhrow;
                    }

		$str="select a.tahuntanam,a.jenisbibit,b.*,c.nama from ".$dbname.".setup_blok a left join ".$dbname.".kebun_5bjr b on a.kodeorg=b.kodeorg
			left join ".$dbname.".kebun_5kelaspohon c on b.kelaspohon=c.kelas
                      where b.kodeorg like '".$kdKebun."%' and a.statusblok in ('TM','TBM') and b.periode='".$thnProd."' and a.luasareaproduktif!=0 order by b.kodeorg asc  limit ".$offset.",".$limit."";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		$row=owlBaris($res);
                if($row>0)
                {
		while($bar=$res->fetch())
		{
                    
		$no+=1;	
		echo"<tr class=rowcontent>
		<td align=center>".$no."</td>
		<td>".$optNmOrg[$bar['kodeorg']]."</td>
		<td style='display:none;'>".$bar['kelaspohon']." - ".$bar['nama']."</td>
		<td>".$bar['tahunproduksi']."</td>
		<td>".$bar['periode']."</td>
		<td>".$bar['tahuntanam']."</td>
		<td>".$bar['jenisbibit']."</td>
		<td align=right>".number_format($bar['bjr'],2)."</td>
		<td>";
			  if($_SESSION['empl']['tipelokasitugas']=='HOLDING')echo"<img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$bar['kodeorg']."','".$bar['kelaspohon']."','".$bar['bjr']."');\"> 
			  <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$bar['tahunproduksi']."','".$bar['kodeorg']."');\">";
		  echo"</td>
		
		</tr>";	
		}     
                echo" <tr><td colspan=8 align=center>
                        ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                        <button class=mybutton onclick=cariBast2(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                        <button class=mybutton onclick=cariBast2(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                        </td>
                        </tr>";   
                }
                else
                {
                    echo "<tr class=rowcontent><td colspan=10>".$_SESSION['lang']['dataempty']."</td></tr>";
                echo" <tr><td colspan=10 align=center>
                        ".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
                        <button class=mybutton onclick=cariBast2(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                        <button class=mybutton onclick=cariBast2(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                        </td>
                        </tr>";   
                }
		break;
		case'loadBlok':
			$optBlok="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			$sBlok="select distinct kodeorg, tahuntanam, statusblok from ".$dbname.".setup_blok where kodeorg like '".$kdKebun."%' and statusblok in ('TM','TBM')";
			$qBlok=$owlPDO->query($sBlok) or die(print " Gagal: ".PDOException::getMessage());
			$qBlok->setFetchMode(PDO::FETCH_ASSOC);
			while($rBlok=$qBlok->fetch())
			{
				$optBlok.="<option value='".$rBlok['kodeorg']."'>".$optNmOrg[$rBlok['kodeorg']]." - ".$rBlok['tahuntanam']." - ".$rBlok['statusblok']."</option>";
			}          
			echo $optBlok;
		break;
		
		case'getPrd':
			$bln;
			$tahun;
			// if($bln==1){
				// $prd2 = ($tahun-1)."-12";
			// }else{
				// $prd2 = $tahun."-".addZero($bln,2);
			// }
				$prd2 = $tahun."-".addZero($bln,2);
			
			// $bln=$bln+2;
			// if($bln>12){
				// $prd2 = ($tahun+1)."-".addZero(($bln-12),2);
			// }else{
				// $prd2 = $tahun."-".addZero($bln,2);
			// }
			for($x=0;$x<24;$x++){
				$dt=mktime(0,0,0,$bln-$x,12,date('Y'));
				$optprd1.="<option value=".date("Y-m",$dt).">".date("m-Y",$dt)."</option>";
			}


           //$optprd1="<option value='".$prd1."'>".$prd1."</option>";
		   //$optprd2="<option value='".$prd2."'>".$prd2."</option>";
			echo $optprd1."###".$optprd1;
		break;

		case'getBln':
			$arrPrd=array("01"=>"Januari","02"=>"Febuary","03"=>"Maret","04"=>"April","05"=>"Mei","06"=>"Juni","07"=>"Juli","08"=>"Agustus","09"=>"September","10"=>"Oktober","11"=>"November","12"=>"Desember");
			$optPrd="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			foreach($arrPrd as $brs1 => $isi1)
			{
				$optPrd.="<option value=".$brs1.">".$isi1."</option>";
			}
			echo $optPrd;
		break;
		
		case'getThn':
			$thn="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
			for($x=0;$x<3;$x++)
			{
				$dt=mktime(0,0,0,0,12,date('Y')+$x);
				$thn.="<option value=".date("Y",$dt).">".date("Y",$dt)."</option>";
			}

			echo $thn;
		break;
		
		case'update':
		if(($thnProd=='')||($jmBjr==''))
		{
			echo"warning:Field tidak boleh kosong";
			exit();
		}
		else
		{	
			$sDel="delete from ".$dbname.".kebun_5bjr where periode='".$thnProd."' and kodeorg='".$kdBlok."'";
			try{
				$owlPDO->exec($sDel); 
				$sIns="insert into ".$dbname.".kebun_5bjr (periode,tahunproduksi,kodeorg,kelaspohon,bjr,updateby) values ('".$thnProd."','".substr($thnProd,0,4)."','".$kdBlok."','".$kelaspohon."','".$jmBjr."','".$_SESSION['standard']['userid']."')";
			
				try{
					$owlPDO->exec($sIns); 
					echo"";
				}catch (PDOException $e){
					echo"Gagal:Db Error".$sIns."__".$e->getMessage();
					die();
				}      
			}catch (PDOException $e){
				echo"Gagal:Db Error".$sDel."__".$e->getMessage();
				die();
			}
		}
		break;
		case'delData':
		$sDel="delete from ".$dbname.".kebun_5bjr where tahunproduksi='".$thnProd."' and kodeorg='".$kdBlok."'";
		try{
			$owlPDO->exec($sDel); 
		}catch (PDOException $e){
			echo"Gagal".$e->getMessage();
			die();
		}
	
		break;
		case'getData':
		$sDt="select * from ".$dbname.".setup_franco where id_franco='".$idFranco."'";
		$qDt=$owlPDO->query($sDt) or die(print " Gagal: ".PDOException::getMessage());
		$qDt->setFetchMode(PDO::FETCH_ASSOC);
		$rDet=$qDt->fetch();
		echo $rDet['id_franco']."###".$rDet['franco_name']."###".$rDet['alamat']."###".$rDet['contact']."###".$rDet['handphone']."###".$rDet['status'];
		break;
		default:
		break;
	}
?>