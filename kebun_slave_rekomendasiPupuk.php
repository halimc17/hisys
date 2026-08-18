<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$kdBrg=checkPostGet('kdBrg','');
$periode=checkPostGet('periode','');
$idKbn=checkPostGet('idKbn','');
$thnTnm=checkPostGet('thnTnm','');
$jnsPpk=checkPostGet('jnsPpk','');
$dosis=checkPostGet('dosis','');
$jnsBibit=checkPostGet('jnsBibit','');
$satuan=checkPostGet('satuan','');
$kdAfd=checkPostGet('kdAfd','');
$kdBlok=checkPostGet('kdBlok','');
$oldBlok=checkPostGet('oldBlok','');

	switch($proses)
	{
		//load data
		case'loadData':
		//$thnBln=date("Y-m");
		OPEN_BOX();
		 echo"<fieldset>
<legend>".$_SESSION['lang']['list']."</legend>";
			echo"<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('kebun_rekomendasipupuk','','','kebun_slave_rekomendasipupukPdf',event);\">&nbsp;
			<img onclick=dataKeExcel(event,'kebun_slave_rekomendasipupukExcel.php') src=images/excel.jpg class=resicon title='MS.Excel'>
			<table cellspacing=1 border=0 id=rkmndsiPupuk class='sortable'>
		<thead>
<tr class=rowheader>
<td>No</td>
<td>".$_SESSION['lang']['tahunpupuk']."</td>
<td>".$_SESSION['lang']['afdeling']."</td>
<td>".$_SESSION['lang']['blok']."</td>
<td>".$_SESSION['lang']['tahuntanam']."</td>
<td>".$_SESSION['lang']['jenisPupuk']."</td>
<td>".$_SESSION['lang']['dosis']."</td>
<td>".$_SESSION['lang']['satuan']."</td>
<td>".$_SESSION['lang']['jenisbibit']."</td>
<td>Action</td>
</tr>
</thead>
<tbody>
";
		$limit=10;
		$page=0;
		if(isset($_POST['page']))
		{
		$page=$_POST['page'];
		if($page<0)
		$page=0;
		}
		$offset=$page*$limit;
		
		$sql2="select count(*) as jmlhrow from ".$dbname.".kebun_rekomendasipupuk where substring(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."' order by `periodepemupukan` desc";
        $query2 = $owlPDO->query($sql2) or die(print " Gagal: " . PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);		
		while($jsl=$query2->fetch()){
			$jlhbrs= $jsl->jmlhrow;
		}
		$slvhc="select * from ".$dbname.".kebun_rekomendasipupuk where  substring(kodeorg,1,4)='".$_SESSION['empl']['lokasitugas']."' order by `periodepemupukan` desc limit ".$offset.",".$limit."";
        $qlvhc = $owlPDO->query($slvhc) or die(print " Gagal: " . PDOException::getMessage());
        $qlvhc->setFetchMode(PDO::FETCH_ASSOC);
		$no=0;
		while($res=$qlvhc->fetch())
		{
			$skdBrg="select  namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";//echo $skdBrg;
	        $qkdBrg = $owlPDO->query($skdBrg) or die(print " Gagal: " . PDOException::getMessage());
	        $qkdBrg->setFetchMode(PDO::FETCH_ASSOC);
			$rBrg=$qkdBrg->fetch();
			
			$sBibit="select jenisbibit  from ".$dbname.".setup_jenisbibit where jenisbibit='".$res['jenisbibit']."'" ;
	        $qBibit = $owlPDO->query($sBibit) or die(print " Gagal: " . PDOException::getMessage());
	        $qBibit->setFetchMode(PDO::FETCH_ASSOC);			
			$rBibit=$qBibit->fetch();

			$sOrg="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$res['kodeorg']."'";
	        $qOrg = $owlPDO->query($sOrg) or die(print " Gagal: " . PDOException::getMessage());
	        $qOrg->setFetchMode(PDO::FETCH_ASSOC);
			$rOrg=$qOrg->fetch();
			
			$no+=1;

		echo"
					<tr class=rowcontent>
					<td>".$no."</td>
					<td>". $res['periodepemupukan']."</td>
					<td>". $rOrg['namaorganisasi']."</td>
					<td>". $res['blok']."</td>
					<td>". $res['tahuntanam']."</td>
					<td>". $rBrg['namabarang']."</td>
					<td align='right'>". $res['dosis']."</td>
					<td>". $rBrg['satuan']."</td>
					<td>".$rBibit['jenisbibit']."</td>";
						if(substr($res['kodeorg'],0,4)==$_SESSION['empl']['lokasitugas'])
						{
							echo"
							<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$res['periodepemupukan']."','".$res['kodeorg']."','".$res['tahuntanam']."','".$res['blok']."');\">
							<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('".$res['periodepemupukan']."','".$res['kodeorg']."','".$res['tahuntanam']."','".$res['blok']."');\" >
						</td>
						</tr>";
						}
					}
					echo"
					<tr><td colspan=9 align=center>
					".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
					<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
					<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
					</td>
					</tr>";
					echo"</table></fieldset>";
					CLOSE_BOX();
		break;
		case'getSatuan':
			$skdBrg="select  satuan from ".$dbname.".log_5masterbarang where kodebarang='".$kdBrg."'";//echo $skdBrg;
	        $qkdBrg = $owlPDO->query($skdBrg) or die(print " Gagal: " . PDOException::getMessage());
	        $qkdBrg->setFetchMode(PDO::FETCH_ASSOC);			
			$rBrg=$qkdBrg->fetch();
			echo $rBrg['satuan'];
		break;
		
		///insert data
		case'insert':
		//echo"warning:masuk";
		if(($jnsPpk=='')||($dosis==''))
		{
			echo"warning:Please Complete The Form";
			exit();
		}
		$sCek="select kodeorg,tahuntanam,periodepemupukan from ".$dbname.".kebun_rekomendasipupuk where kodeorg='".$idKbn."' and tahuntanam='".$thnTnm."' and periodepemupukan='".$periode."' and blok='".$kdBlok."'";
        $qCek = $owlPDO->query($sCek) or die(print " Gagal: " . PDOException::getMessage());
        $qCek->setFetchMode(PDO::FETCH_ASSOC);		
		$rCek=owlBaris($qCek);
		if($rCek<1)
		{
			$sIns="insert into ".$dbname.".kebun_rekomendasipupuk (kodeorg,blok, tahuntanam, kodebarang, dosis, satuan, periodepemupukan, jenisbibit) values 
			('".$idKbn."','".$kdBlok."','".$thnTnm."','".$jnsPpk."','".$dosis."','".$satuan."','".$periode."','".$jnsBibit."')";
			try{$owlPDO->exec($sIns); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); } 
		}
		else
		{
			echo"warning:This Data Already Input";
			exit();
		}
		break;
		
		//getData
		case'getData':
		$sGet="select * from ".$dbname.".kebun_rekomendasipupuk where kodeorg='".$idKbn."' and tahuntanam='".$thnTnm."' and periodepemupukan='".$periode."' and blok='".$kdBlok."'";
        $qGet = $owlPDO->query($sGet) or die(print " Gagal: " . PDOException::getMessage());
        $qGet->setFetchMode(PDO::FETCH_ASSOC);	
		$rGet=$qGet->fetch();
		
		echo $rGet['kodeorg']."###".$rGet['kodebarang']."###".$rGet['dosis']."###".$rGet['satuan']."###".$rGet['periodepemupukan']."###".$rGet['jenisbibit']."###".$rGet['blok'];
		break;
		
		case'update':
		if(($jnsPpk=='')||($dosis==''))
		{
			echo"warning:Please Complete The Form";
			exit();
		}
		$sUp="update ".$dbname.".kebun_rekomendasipupuk set kodebarang='".$jnsPpk."', dosis='".$dosis."', satuan='".$satuan."', jenisbibit='".$jnsBibit."',blok='".$kdBlok."',tahuntanam='".$thnTnm."' where kodeorg='".$idKbn."' and periodepemupukan='".$periode."' and blok='".$oldBlok."'";
			try{$owlPDO->exec($sUp); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); } 
		break;
		//hapus data
		case'delData':
		$sDel="delete from ".$dbname.".kebun_rekomendasipupuk where kodeorg='".$idKbn."' and blok='".$kdBlok."' and tahuntanam='".$thnTnm."' and periodepemupukan='".$periode."'";
			try{$owlPDO->exec($sDel); }catch (PDOException $e) {print " Gagal  !: " . $e->getMessage() . "\n"; die(); } 
		break;
		
		//cari transaksi
			case 'cariData':
		 OPEN_BOX();
		 echo"<fieldset>
<legend>".$_SESSION['lang']['result']."</legend>";
			echo"<div style=\"width:600px; height:450px; overflow:auto;\">
			<table cellspacing=1 border=0 class='sortable'>
		<thead>
<tr class=rowheader>
<td>No</td>
<td>".$_SESSION['lang']['tahunpupuk']."</td>
<td>".$_SESSION['lang']['kebun']."</td>
<td>".$_SESSION['lang']['tahuntanam']."</td>
<td>".$_SESSION['lang']['jenisPupuk']."</td>
<td>".$_SESSION['lang']['dosis']."</td>
<td>".$_SESSION['lang']['satuan']."</td>
<td>".$_SESSION['lang']['jenisbibit']."</td>
<td>Action</td>
</tr>
</thead>
<tbody>
";		
		
        if($periode!='')
			{
				$where=" periodepemupukan LIKE  '%".$periode."%'";
			}
			elseif($idKbn!='')
			{
				$where.=" kodeorg LIKE '%".$idKbn."%'";
			}
			elseif(($periode!='')&&($idKbn!=''))
			{
				$where.=" periodepemupukan LIKE '%".$periode."%' and kodeorg LIKE '%".$idKbn."%'";
			}
		//echo $strx; exit();
			$limit=10;
		$page=0;
		if(isset($_POST['page']))
		{
		$page=$_POST['page'];
		if($page<0)
		$page=0;
		}
		$offset=$page*$limit;
		
		$sql2="select count(*) as jmlhrow from ".$dbname.".kebun_rekomendasipupuk where  ".$where." order by `periodepemupukan` desc";
        $query2 = $owlPDO->query($sql2) or die(print " Gagal: " . PDOException::getMessage());
        $query2->setFetchMode(PDO::FETCH_OBJ);

		while($jsl=$query2->fetch()){
		$jlhbrs= $jsl->jmlhrow;
		}
                
		$strx="select * from ".$dbname.".kebun_rekomendasipupuk where ".$where." order by periodepemupukan desc limit ".$offset.",".$limit."";
        $qry = $owlPDO->query($strx) or die(print " Gagal: " . PDOException::getMessage());
        $qry->setFetchMode(PDO::FETCH_ASSOC);
		$numrows=owlbaris($qry);
				if($numrows<1)
				{
					echo"<tr class=rowcontent><td colspan=9>Not Found</td></tr>";
				}
				else
				{
					while($res=$qry->fetch())
					{	
					$skdBrg="select  namabarang,satuan from ".$dbname.".log_5masterbarang where kodebarang='".$res['kodebarang']."'";//echo $skdBrg;
			        $qkdBrg = $owlPDO->query($skdBrg) or die(print " Gagal: " . PDOException::getMessage());
    				$qkdBrg->setFetchMode(PDO::FETCH_ASSOC);
					$rBrg=$qkdBrg->fetch();
					
					$sBibit="select jenisbibit  from ".$dbname.".setup_jenisbibit where jenisbibit='".$res['jenisbibit']."'" ;
			        $qBibit = $owlPDO->query($sBibit) or die(print " Gagal: " . PDOException::getMessage());
    				$qBibit->setFetchMode(PDO::FETCH_ASSOC);					
					$rBibit=$qBibit->fetch();
					
					$no+=1;
					echo"
					<tr class=rowcontent>
					<td>".$no."</td>
					<td>". $res['periodepemupukan']."</td>
					<td>". $res['kodeorg']."</td>
					<td>". $res['tahuntanam']."</td>
					<td>". $rBrg['namabarang']."</td>
					<td>". $res['dosis']."</td>
					<td>". $rBrg['satuan']."</td>
					<td>".$rBibit['jenisbibit']."</td>";
					if(substr($res['kodeorg'],0,4)==$_SESSION['empl']['lokasitugas'])
						{
							echo"
							<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('".$res['notransaksi']."');\">
							<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('". $res['notransaksi']."');\" >
						</td>
							</tr>";
						}
					}
                                        echo"
					<tr><td colspan=9 align=center>
					".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
					<button class=mybutton onclick=cariHasil(".($page-1).");>".$_SESSION['lang']['pref']."</button>
					<button class=mybutton onclick=cariHasil(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
					</td>
					</tr>";
					echo"</tbody></table></div></fieldset>";
					
				}
			CLOSE_BOX();
		break;
		case'getBlok':
		$optBlok="<option value=></option>";
		$sBlok="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$kdAfd."'";
        $qBlok = $owlPDO->query($sBlok) or die(print " Gagal: " . PDOException::getMessage());
		$qBlok->setFetchMode(PDO::FETCH_ASSOC);
		while($rBlok=$qBlok->fetch())
		{
			if($kdBlok!='')
			{
				//echo"test";
				$optBlok.="<option value='".$rBlok['kodeorganisasi']."'  ".($kdBlok==$rBlok['kodeorganisasi']?'selected':'').">".$rBlok['namaorganisasi']."</option>";
			}
			else
			{
				$optBlok.="<option value=".$rBlok['kodeorganisasi'].">".$rBlok['namaorganisasi']."</option>";
			}
		}
		echo $optBlok;
		break;
		case'getThn':
		$sThn="select tahuntanam from ".$dbname.".setup_blok where kodeorg='".$kdBlok."'";
        $qThn = $owlPDO->query($sThn) or die(print " Gagal: " . PDOException::getMessage());
		$qThn->setFetchMode(PDO::FETCH_ASSOC);
		while($rThn=$qThn->fetch())
		{
			$optThn.="<option value=".$rThn['tahuntanam'].">".$rThn['tahuntanam']."</option>";
		}
		echo $optThn;
		break;
		default:
		break;
	}
?>