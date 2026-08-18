<?php
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$tgl=tanggalsystem(checkPostGet('tgl',''));
$jmlh=checkPostGet('jmlh','');
$kdFraksi=checkPostGet('kdFraksi','');
$noTiket=checkPostGet('noTiket','');
$lokasi=$_SESSION['empl']['lokasitugas'];
$jmlhJJg=checkPostGet('jmlhJJg','');
$persenBrnd=checkPostGet('persenBrnd','');
$kgPtngan=checkPostGet('kgPtngan','');

if($_SESSION['language']=='EN'){
    $zz='keterangan1 as keterangan';
}else{
    $zz='keterangan';
}

switch($proses)
{
	case'getTiket':
	$thn=substr($tgl,0,4);
	$bln=substr($tgl,4,2);
	$hari=substr($tgl,6,2);
	$tanggal=$thn."-".$bln."-".$hari;
	$optNotiket="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sTim="select notransaksi from ".$dbname.".pabrik_timbangan where substr(tanggal,1,10) = '".$tanggal."' and  kodebarang='40000003'";
        $qTim=$owlPDO->query($sTim) or die(print " Gagal: ".PDOException::getMessage());
        $qTim->setFetchMode(PDO::FETCH_ASSOC);
        $row=owlBaris($qTim);
	if($row>0)
	{
            while($rTim=$qData->fetch())
            {
                if($noTiket=='0')
                {
                    $optNotiket.="<option value=".$rTim['notransaksi'].">".$rTim['notransaksi']."</option>";
                }
                else
                {
                    $optNotiket.="<option value=".$rTim['notransaksi']." ".($rTim['notransaksi']==$noTiket?'selected':'').">".$rTim['notransaksi']."</option>";
                }
            }
            echo $optNotiket;
	}
	else
	{
            echo"warning: Weighbridge data is empty";
            exit();
	}
	break;
        
	case'getData':
	$sDt="select * from ".$dbname.".pabrik_sortasi where notiket='".$noTiket."' and kodefraksi='".$kdFraksi."'";
        $qDt=$owlPDO->query($sDt) or die(print " Gagal: ".PDOException::getMessage());
        $qDt->setFetchMode(PDO::FETCH_ASSOC);
        $rDt=$qDt->fetch();
                
	$sTgl="select tanggal from ".$dbname.".pabrik_timbangan where notransaksi='".$noTiket."'";
       $qTgl=$owlPDO->query($sTgl) or die(print " Gagal: ".PDOException::getMessage());
        $qTgl->setFetchMode(PDO::FETCH_ASSOC);
        $rTgl=$qTgl->fetch();
	echo $rDt['notiket']."###".$rDt['kodefraksi']."###".$rDt['jumlah']."###".tanggalnormal($rTgl['tanggal']);
	break;

	case'LoadData':
	echo"
		<table cellspacing=1 border=0 class=sortable>
	<thead>
	<tr class=rowheader>
	<td align=center>No.</td>
	<td align=center>".$_SESSION['lang']['noTiket']."</td>
	<td align=center>".$_SESSION['lang']['tanggal']."</td>
	";
		
	$sFraksi="select kode,".$zz.",type from ".$dbname.".pabrik_5fraksi2 order by kode asc";
      $qFraksi=$owlPDO->query($sFraksi) or die(print " Gagal: ".PDOException::getMessage());
        $qFraksi->setFetchMode(PDO::FETCH_ASSOC);
        while($rFraksi=$qFraksi->fetch())
        {
		echo"<td align=center style=width:70px>".$rFraksi['keterangan']." ".($rFraksi['type']!=''?"(".$rFraksi['type'].")":'')."</td> ";
	}
					 
	echo"<td align=center style=width:70px>".$_SESSION['lang']['sortasi']."(Jjg)</td>
		 <td align=center style=width:70px> ".$_SESSION['lang']['potongankg']."</td>
	<td align=center>Action</td>
	</tr>
	</thead>
	<tbody>";

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
	$ql2="select distinct a.notiket from ".$dbname.".pabrik_sortasi a left join ".$dbname.".pabrik_timbangan b on a.notiket=b.notransaksi where millcode='".$_SESSION['empl']['lokasitugas']."' and kodebarang='40000003' group by `notiket`   order by a.`notiket` desc";//echo $ql2;
    
	// $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
    // $query2->setFetchMode(PDO::FETCH_OBJ);
    // $jsl=owlBaris($query2);
	
	// $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	// $baris=owlBaris($res);
	$res=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_NUM);
	$baris=0;
 	while($bar=$res->fetch()){
 		@$jsl+=1;
 	}
	
	// echo $jsl;
	$jlhbrs= $jsl;

        $a=0;
		$no=$maxdisplay;
	$sNotiket="select distinct a.notiket from ".$dbname.".pabrik_sortasi a left join ".$dbname.".pabrik_timbangan b on a.notiket=b.notransaksi where millcode='".$_SESSION['empl']['lokasitugas']."' and kodebarang='40000003'  order by `notiket` desc limit ".$offset.",".$limit." ";
        $qNotiket=$owlPDO->query($sNotiket) or die(print " Gagal: ".PDOException::getMessage());
        $qNotiket->setFetchMode(PDO::FETCH_ASSOC);
        while($rNotiket=$qNotiket->fetch())
        {
			$no+=1;
			$aret=1;
			echo"<tr class=rowcontent><td align=center>".$no."</td>";
			echo"<td>".$rNotiket['notiket']."</td>";
			$sFraksi="select kode from ".$dbname.".pabrik_5fraksi2 order by kode asc";
                        $qFraksi=$owlPDO->query($sFraksi) or die(print " Gagal: ".PDOException::getMessage());
                        $qFraksi->setFetchMode(PDO::FETCH_ASSOC);
                        
			
                        $sJjg="select jjgsortasi,tanggal,persenBrondolan,kgpotsortasi from ".$dbname.".pabrik_timbangan 
				   where notransaksi='".$rNotiket['notiket']."'";
                       $qJjg=$owlPDO->query($sJjg) or die(print " Gagal: ".PDOException::getMessage());
                        $qJjg->setFetchMode(PDO::FETCH_ASSOC);
                        $rJjg=$qJjg->fetch();
			echo"<td>".tanggalnormal(substr($rJjg['tanggal'],0,10))."</td>";
                        
                        
                        while($rFraksi=$qFraksi->fetch())
                        {
					$sMax="select notiket,jumlah,kodefraksi from ".$dbname.".pabrik_sortasi where notiket='".$rNotiket['notiket']."' and kodefraksi='".$rFraksi['kode']."'";
                                       $qMax=$owlPDO->query($sMax) or die(print " Gagal: ".PDOException::getMessage());
                                        $qMax->setFetchMode(PDO::FETCH_ASSOC);
                                        $rMax=$qMax->fetch();
					if($rFraksi['kode']==$rMax['kodefraksi'])
					{
                                            echo"<td align=right id='".$rFraksi['kode']."##".$rMax['notiket']."' onclick=\"editDetHead('".$rNotiket['notiket']."','".tanggalnormal((substr($rJjg['tanggal'],0,10)))."')\" style=\"cursor:pointer\" >".number_format($rMax['jumlah'],2)."</td>";
					}
					else
					{
                                            echo"<td align=right>".number_format($rMax['jumlah'],2)."</td>";
					}
			}
			//while($a!=$rMax)

			echo"<td align=right>".number_format($rJjg['jjgsortasi'],0)."</td>";
			echo"<td align=right>".number_format($rJjg['kgpotsortasi'],2)."</td>";
			echo"<td align=center>

<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldata('".$rNotiket['notiket']."');\"></td></tr>";
	}
	echo"
	<tr><td colspan=17 align=center>
	".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
	<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	</td>
	</tr>";  	

	echo"</tbody></table>";
	break;
	case'insert':
	   // echo"warning";
		if($noTiket=='')
		{
			echo"warning:No Tiket Tidak boleh Kosong";
			exit();
		}
	$kdFraksi=$_POST['kdFraksi'];
	$isiData=$_POST['isiData'];
	foreach ($kdFraksi as $rt =>$isi)
	{
		if($isiData[$isi]=='')
		{
		   $isiData[$isi]=0; 
		}
		$sCek="select notiket,kodefraksi from ".$dbname.".pabrik_sortasi where notiket='".$noTiket."' and kodefraksi='".$isi."'";
               $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
                $qCek->setFetchMode(PDO::FETCH_ASSOC);
                $rCek=owlBaris($qCek);
		if($rCek<1)
		{
                    $sIns="insert into ".$dbname.".pabrik_sortasi (notiket, kodefraksi, jumlah) values ('".$noTiket."','".$isi."','".$isiData[$isi]."')";
                   try
                    {
                        $owlPDO->exec($sIns);
                        $sCekDt="select jjgsortasi from ".$dbname.".pabrik_timbangan where notransaksi='".$noTiket."'";
                        
                       $qCekDt=$owlPDO->query($sCekDt) or die(print " Gagal: ".PDOException::getMessage());
                        $qCekDt->setFetchMode(PDO::FETCH_ASSOC);
                        $rCekDt=$qCekDt->fetch();
                        
                        if($rCekDt['jjgsortasi']==0)
                        {
                            $sDt="update ".$dbname.".pabrik_timbangan set jjgsortasi='".$jmlhJJg."',kgpotsortasi='".$kgPtngan."' where notransaksi='".$noTiket."'";
                            try
                            {
                                $owlPDO->exec($sDt);
                            }
                            catch (PDOException $e) 
                            {
                                print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                                die(); 
                            }
                      }
                    }
                    catch (PDOException $e) 
                    {
                        print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                        die(); 
                    }  
		}
		else
		{
			$sIns="update ".$dbname.".pabrik_sortasi set kodefraksi='".$isi."', jumlah='".$isiData[$isi]."' where notiket='".$noTiket."' and kodefraksi='".$isi."'";
                       try
                        {
                            $owlPDO->exec($sIns);
                            $sDt="update ".$dbname.".pabrik_timbangan set jjgsortasi='".$jmlhJJg."',kgpotsortasi='".$kgPtngan."' where notransaksi='".$noTiket."'";
                          try
                            {
                                $owlPDO->exec($sDt);
                            }
                            catch (PDOException $e) 
                            {
                                print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                                die(); 
                            }
                      }
                        catch (PDOException $e) 
                        {
                            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                            die(); 
                        }
		}
	}
	break;

        
        
        
	case'update':
		if($noTiket=='')
		{
			echo"warning : No Tiket Tidak boleh Kosong";
			exit();
		}
	$kdFraksi=$_POST['kdFraksi'];
	$isiData=$_POST['isiData'];
	foreach ($kdFraksi as $rt =>$isi)
	{

		if($isiData[$isi]=='')
		{
		   $isiData[$isi]=0; 
		}
		$sCek="select notiket,kodefraksi from ".$dbname.".pabrik_sortasi where notiket='".$noTiket."' and kodefraksi='".$isi."'";
               $qCek=$owlPDO->query($sCek) or die(print " Gagal: ".PDOException::getMessage());
                $rCek=owlBaris($qCek);
		if($rCek>0)
		{
			$sIns="update ".$dbname.".pabrik_sortasi set kodefraksi='".$isi."', jumlah='".$isiData[$isi]."' where notiket='".$noTiket."' and kodefraksi='".$isi."'";
                       try
                        {
                            $owlPDO->exec($sIns);
                            $sDt="update ".$dbname.".pabrik_timbangan set jjgsortasi='".$jmlhJJg."',persenBrondolan='".$persenBrnd."',kgpotsortasi='".$kgPtngan."' where notransaksi='".$noTiket."'";
                             try
                            {
                                $owlPDO->exec($sDt);
                            }
                            catch (PDOException $e) 
                            {
                                print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                                die(); 
                            }
                     }
                        catch (PDOException $e) 
                        {
                            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                            die(); 
                        }
		}
		else
		{
			$sIns="insert into ".$dbname.".pabrik_sortasi (notiket, kodefraksi, jumlah) values ('".$noTiket."','".$isi."','".$isiData[$isi]."')";
                        try
                        {
                            $owlPDO->exec($sUpdate);
                    
				$sDt="update ".$dbname.".pabrik_timbangan set jjgsortasi='".$jmlhJJg."',persenBrondolan='".$persenBrnd."',kgpotsortasi='".$kgPtngan."' where notransaksi='".$noTiket."'";
                                try
                                {
                                    $owlPDO->exec($sUpdate);
                                }
                                catch (PDOException $e) 
                                {
                                    print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                                    die(); 
                                }    
                                
                        }
                        catch (PDOException $e) 
                        {
                            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                            die(); 
                        }

		}

	}    
	break;
	case'delData':
	$where=" notiket='".$noTiket."'";
	$sDel="delete from ".$dbname.".pabrik_sortasi where  ".$where."";
	// exit ("error:asdsad");
        try
        {
            $owlPDO->exec($sDel);
            $sUpd="update ".$dbname.".pabrik_timbangan set jjgsortasi=0,persenBrondolan=0 where notransaksi='".$noTiket."'";
            try
            {
                $owlPDO->exec($sUpd);
            }
            catch (PDOException $e) 
            {
                print " Gagal  !: " . $e->getMessage() . "<br/>"; 
                die(); 
            }  
        }
        catch (PDOException $e) 
        {
            print " Gagal  !: " . $e->getMessage() . "<br/>"; 
            die(); 
        }  
	break;

	case'cariData':
	echo"<table cellspacing=1 border=0 class=sortable>
	<thead>
	<tr class=rowheader>
	<td align=center>No.</td>
	<td align=center>".$_SESSION['lang']['noTiket']."</td>
	<td align=center>".$_SESSION['lang']['tanggal']."</td>
	";
	$sFraksi="select kode,".$zz.",type from ".$dbname.".pabrik_5fraksi2 order by kode asc";
        $qFraksi=$owlPDO->query($sFraksi) or die(print " Gagal: ".PDOException::getMessage());
        $qFraksi->setFetchMode(PDO::FETCH_ASSOC);
        while($rFraksi=$qFraksi->fetch())
        {
            echo"<td align=center style=width:70px>".$rFraksi['keterangan']." ".($rFraksi['type']!=''?"(".$rFraksi['type'].")":'')."</td> ";
	}
	echo"<td align=center style=width:70px>".$_SESSION['lang']['sortasi']."(Jjg)</td><td align=center style=width:70px> ".$_SESSION['lang']['potongankg']."</td>
	<td align=center>Action</td>
	</tr>
	</thead>
	<tbody>";
			if($noTiket!='')
			{
					$where="and notiket like '%".$noTiket."%'";
			}

	$limit=20;
	$page=0;
	if(isset($_POST['page']))
	{
	$page=$_POST['page'];
	if($page<0)
	$page=0;
	}//indra      
	$offset=$page*$limit;
	$maxdisplay=($page*$limit);
	$jsl=0;
	$ql2="select distinct a.notiket from ".$dbname.".pabrik_sortasi a  left join ".$dbname.".pabrik_timbangan b
		  on a.notiket=b.notransaksi where kodebarang='40000003' ".$where." and millcode='".$_SESSION['empl']['lokasitugas']."' group by `notiket` order by `notiket` desc ";//echo $ql2;
	$res=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_NUM);
	$baris=0;
 	while($bar=$res->fetch()){
 		@$jsl+=1;
 	}
	
    // echo $ql2;  
        // $query2=$owlPDO->query($ql2) or die(print " Gagal: ".PDOException::getMessage());
        // $jsl=owlBaris($query2);
	$jlhbrs= $jsl;

        $a=0;
        $no=0;
		$no=$maxdisplay;
	$sNotiket="select a.notiket from ".$dbname.".pabrik_sortasi a  left join ".$dbname.".pabrik_timbangan b
		  on a.notiket=b.notransaksi  where kodebarang='40000003'  ".$where."  group by `notiket` order by `notiket` desc  limit ".$offset.",".$limit." ";
       $qNotiket=$owlPDO->query($sNotiket) or die(print " Gagal: ".PDOException::getMessage());
        $qNotiket->setFetchMode(PDO::FETCH_ASSOC);
        while($rNotiket=$qNotiket->fetch())
        {
            $no+=1;
            echo"<tr class=rowcontent><td align=center>".$no."</td>";
            echo"<td>".$rNotiket['notiket']."</td>";
            $sFraksi="select kode from ".$dbname.".pabrik_5fraksi2 order by kode asc";
            $qFraksi=$owlPDO->query($sFraksi) or die(print " Gagal: ".PDOException::getMessage());
            $qFraksi->setFetchMode(PDO::FETCH_ASSOC);

            $sJjg="select jjgsortasi,tanggal,persenBrondolan,kgpotsortasi from ".$dbname.".pabrik_timbangan where notransaksi='".$rNotiket['notiket']."'";
            $qJjg=$owlPDO->query($sJjg) or die(print " Gagal: ".PDOException::getMessage());
            $qJjg->setFetchMode(PDO::FETCH_ASSOC);
            $rJjg=$qJjg->fetch();
			echo"<td>".tanggalnormal(substr($rJjg['tanggal'],0,10))."</td>";
            while($rFraksi=$qFraksi->fetch())
            {
                            $sMax="select notiket,jumlah,kodefraksi from ".$dbname.".pabrik_sortasi where notiket='".$rNotiket['notiket']."' and kodefraksi='".$rFraksi['kode']."'";
                            $qMax=$owlPDO->query($sMax) or die(print " Gagal: ".PDOException::getMessage());
                            $qMax->setFetchMode(PDO::FETCH_ASSOC);
                            $rMax=$qMax->fetch();
                            if($rFraksi['kode']==$rMax['kodefraksi'])
                            {
                                echo"<td align=right id='".$rFraksi['kode']."##".$rMax['notiket']."' onclick=\"editDetHead('".$rNotiket['notiket']."','".tanggalnormal((substr($rJjg['tanggal'],0,10)))."')\" style=\"cursor:pointer\" >".number_format($rMax['jumlah'],2)."</td>";
                            }
                            else
                            {
                                echo"<td align=right>".number_format($rMax['jumlah'],2)."</td>";
                            }
            }
            //while($a!=$rMax)
            echo"<td align=right>".number_format($rJjg['jjgsortasi'],0)."</td>";
            echo"<td align=right>".number_format($rJjg['kgpotsortasi'],2)."</td>";
            echo"<td align=center><img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"deldata('".$rNotiket['notiket']."');\"></td></tr>";
	}
	echo"
	<tr><td colspan=17 align=center>
	".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
	<button class=mybutton onclick=cariData(".($page-1).");>".$_SESSION['lang']['pref']."</button>
	<button class=mybutton onclick=cariData(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
	</td>
	</tr>";  	

	echo"</tbody></table>";
	break;
	case'getJenjang':
	$sGet="select jumlahtandan1 from ".$dbname.".pabrik_timbangan where notransaksi='".$noTiket."'";
        $qGet=$owlPDO->query($sGet) or die(print " Gagal: ".PDOException::getMessage());
        $qGet->setFetchMode(PDO::FETCH_ASSOC);
        $rGet=$qGet->fetch();
	echo $rGet['jumlahtandan1'];
	break;
	case'createTable':
	//=========================
	//============================    
	$thn=substr($tgl,0,4);
	$bln=substr($tgl,4,2);
	$hari=substr($tgl,6,2);
	$tanggal=$thn."-".$bln."-".$hari;
	$optNotiket="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	if($_POST['kdOrg']!=''){
	$sTim="select notransaksi from ".$dbname.".pabrik_timbangan where 
		   substr(tanggal,1,10) = '".$tanggal."' and  kodebarang='40000003'
		   and millcode='".$_POST['kdOrg']."'";
	}else{
		$skdorg="select distinct millcode from ".$dbname.".pabrik_timbangan where notiket='".$noTiket."'";
                
                $qkdorg=$owlPDO->query($skdorg) or die(print " Gagal: ".PDOException::getMessage());
                $qkdorg->setFetchMode(PDO::FETCH_ASSOC);
                $kdorg=$qkdorg->fetch();
                
                
		$sTim="select notransaksi from ".$dbname.".pabrik_timbangan where 
			   substr(tanggal,1,10) = '".$tanggal."' and  kodebarang='40000003' 
			   and millcode='".$kdorg['millcode']."'";
	}
       $qTim=$owlPDO->query($sTim) or die(print " Gagal: ".PDOException::getMessage());
        $qTim->setFetchMode(PDO::FETCH_ASSOC);
        $row=owlBaris($qTim);
	if($row>0)
	{
                        while($rTim=$qTim->fetch())
                        {
					if($noTiket=='0')
					{
							$optNotiket.="<option value=".$rTim['notransaksi'].">".$rTim['notransaksi']."</option>";
					}
					else
					{
							$optNotiket.="<option value=".$rTim['notransaksi']." ".($rTim['notransaksi']==$noTiket?'selected':'').">".$rTim['notransaksi']."</option>";
					}
			}
			//echo $optNotiket;
	}
	$table="<table id='ppDetailTable'>";
	//echo"warning:".$table;
	# Header
	$table .= "<thead>";
	$table .= "<tr>";
	$table .= "<td align=center style=width:50px>".$_SESSION['lang']['noTiket']."</td>
			  <td align=center style=width:50px>Netto</td>
			  <td align=center style=width:50px>".$_SESSION['lang']['sortasi']." (Jjg)</td>
			  <td align=center style=width:50px>BJR</td>";
	$qHead="select distinct kode,".$zz." from ".$dbname.".pabrik_5fraksi2  order by kode asc";
	
         $zd=$owlPDO->query($qHead) or die(print " Gagal: ".PDOException::getMessage());
	$rHead=fetchData($qHead);
	foreach($rHead as $row =>$isi)
	{
		$table .= "<td align=center style=width:50px>".$isi['keterangan']."</td>";
		// $brs+=1;
	}
	$table .= "<td align=center style=width:50px>".$_SESSION['lang']['potongankg']."</td>
			   <td align=center>Action</td></tr>";

	$table .= "</thead><tbody>";
	$table.="<tr class=rowcontent>
			 <td><select style='width:80px;' id=noTkt name=noTkt onchange=getNetto(this.options[this.selectedIndex].value)>".$optNotiket."</select></td>";          
	$table.="<td align=right id=nettox></td>";
	$table.="<td><input type=text class=myinputtextnumber style='width:65px;' id=jmlhJJg  onkeypress=\"return angka_doang(event)\" size=\"10\" maxlength=\"4\" value=0  onblur=hitungBJR(this.value,".owlBaris($zd).")></td>";
	$table.="<td id=bjrx></td>";
	$a=0;
	$arr="";
	foreach($rHead as $row2 =>$isi2)
	{
		$a++;
		$arr.="##".$isi2['kode'];
	$table .="<td align=right>
		<input type=text class=myinputtextnumber style='width:65px;' id=inputan_".$a." name=frak".$isi2['kode']." onkeypress=\"return angka_doang(event)\" size=\"10\" maxlength=\"4\" value=0 onblur=hitungPotongan(this.value,'".$isi2['kode']."',".owlBaris($zd).")><input type=hidden id=fraksi_".$a." value=".$isi2['kode']." /></td>";
	}
	$table.="<td><input type=text class=myinputtextnumber style='width:65px;' id=kgPtngan  onkeypress=\"return angka_doang(event)\" size=\"10\" maxlength=\"4\" value=0  /></td>";
	$table .="<td align=center><img id='detail_add' title='".$_SESSION['lang']['save']."' class=zImgBtn onclick=\"addDetail('".$a."')\" src='images/save.png'/></td>";
	$table.="</tr></tbody></table><input type=hidden id=jmlhBaris value=".$a." />";
	echo $table;



	break;
	case'EditData':
	$thn=substr($tgl,0,4);
	$bln=substr($tgl,4,2);
	$hari=substr($tgl,6,2);
	$tanggal=$thn."-".$bln."-".$hari;   
	$optNotiket="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
	$sTim="select notransaksi from ".$dbname.".pabrik_timbangan where substr(tanggal,1,10) = '".$tanggal."' and  kodebarang='40000003'";
        
        $qTim=$owlPDO->query($sTim) or die(print " Gagal: ".PDOException::getMessage());
        $qTim->setFetchMode(PDO::FETCH_ASSOC);
        $row=owlBaris($qTim);
        
	if($row>0)
	{
                      while($rTim=$qTim->fetch())
                        {
                            if($noTiket=='')
                            {
                                            $optNotiket.="<option value=".$rTim['notransaksi'].">".$rTim['notransaksi']."</option>";
                            }
                            else
                            {
                                            $optNotiket.="<option value=".$rTim['notransaksi']." ".($rTim['notransaksi']==$noTiket?'selected':'').">".$rTim['notransaksi']."</option>";
                            }
			}
			//echo $optNotiket;
	}
	$sJjg="select jjgsortasi,tanggal,persenBrondolan,kgpotsortasi,beratbersih from ".$dbname.".pabrik_timbangan where notransaksi='".$noTiket."'";
        $qJjg=$owlPDO->query($sJjg) or die(print " Gagal: ".PDOException::getMessage());
        $qJjg->setFetchMode(PDO::FETCH_ASSOC);
        $rJjg=$qJjg->fetch();
                
	#pembatasan 12.5 persen ada pada javascript    
	//============================ 

	$table="<table id='ppDetailTable'>";
	//echo"warning:".$table;
	# Header
	$table .= "<thead>";
	$table .= "<tr>";               
	$table .= "<td align=center style=width:50px>".$_SESSION['lang']['noTiket']."</td>
			   <td align=center style=width:50px>Netto</td>
			   <td align=center style=width:50px>".$_SESSION['lang']['sortasi']." (Jjg)</td>
			   <td align=center style=width:50px>BJR</td>";
	$qHead="select distinct kode,".$zz." from ".$dbname.".pabrik_5fraksi2 order by kode asc";
	
        
	$zd=$owlPDO->query($qHead) or die(print " Gagal: ".PDOException::getMessage());
       
        
        
        $rHead=fetchData($qHead);
	
	$brs=0;
	foreach($rHead as $row =>$isi)
	{
		$table .= "<td align=center style=width:50px>".$isi['keterangan']."</td>";
	   $brs+=1;
	}
	$table .= "<td align=center style=width:50px>Kg ".$_SESSION['lang']['potongan']."</td>
			   <td align=center>Action</td></tr>";

	$table .= "</thead><tbody>";
	$table.="<tr class=rowcontent>
			 <td><select style='width:80px;' id=noTkt name=noTkt disabled>".$optNotiket."</select></td>";    
	$table.="<td  align=right id=nettox>".number_format($rJjg['beratbersih'])."</td>";// 
	$table.="<td><input type=text class=myinputtextnumber style='width:65px;' id=jmlhJJg  onkeypress=\"return angka_doang(event)\" size=\"10\" maxlength=\"4\" value='".$rJjg['jjgsortasi']."'  onblur=hitungBJR(this.value,".owlBaris($zd).")></td>";
		@$dtbjr=$rJjg['beratbersih']/$rJjg['jjgsortasi'];
	$table.="<td align=right id=bjrx>".number_format($dtbjr,2)."</td>";
	$qData="select * from ".$dbname.".pabrik_sortasi where notiket='".$noTiket."' order by kodefraksi asc";
	$rData=fetchData($qData);
	foreach($rData as $brs =>$dt)
	{
	   $listData[$dt['kodefraksi']]=$dt['jumlah'];
	}
	$a=0;
	foreach($rHead as $row2 =>$isi2)
	{
		$a++;
		setIt($listData[$isi2['kode']],0);
		$table .="<td align=right>
		<input type=text class=myinputtextnumber style='width:65px;' id=inputan_".$a." onkeypress=\"return angka_doang(event)\" size=\"10\" maxlength=\"4\" value=".$listData[$isi2['kode']]." onblur=hitungPotongan(this.value,'".$isi2['kode']."',".owlBaris($zd).")><input type=hidden  id=fraksi_".$a." value=".$isi2['kode']." /></td>";
	}
	$table.="<td><input type=text class=myinputtextnumber style='width:65px;' id=kgPtngan  onkeypress=\"return angka_doang(event)\" size=\"10\" maxlength=\"4\" value='".$rJjg['kgpotsortasi']."'  /></td>";
	$table .="<td align=center><img id='detail_add' title='".$_SESSION['lang']['save']."' class=zImgBtn onclick=\"addDetail('".$a."')\" src='images/save.png'/></td>";
	$table.="</tr></tbody></table><input type=hidden id=jmlhBaris value=".$a." />";
	echo $table;
	break;
	case'loadDataDetail':
	echo"<div style=overflow:auto;>
		<table cellspacing=1 border=0 class=sortable>
	<thead>
	<tr class=rowheader>
	<td align=center>No.</td>
	<td align=center style=width:100px>".$_SESSION['lang']['noTiket']."</td>
	";
		$thn=substr($tgl,0,4);
		$bln=substr($tgl,4,2);
		$dt=substr($tgl,6,2);
		$tanggal=$thn."-".$bln."-".$dt;
	$qHead="select distinct kode,".$zz." from ".$dbname.".pabrik_5fraksi2 order by kode asc";
	$rHead=fetchData($qHead);
	$brs=count($rHead);
	foreach($rHead as $row =>$isi)
	{
		echo "<td  align=center style=width:80px>".$isi['keterangan']."</td>";

	}
	echo"<td align=center style=width:80px>".$_SESSION['lang']['sortasi']."(JJG)</td>
		 <td align=center style=width:80px>".$_SESSION['lang']['potongankg']."</td>
		 <td align=center style=width:30px>Action</td>
		 </tr>
		 </thead>
		 <tbody>";
	$qData="select * from ".$dbname.".pabrik_sortasi a left join ".$dbname.".pabrik_timbangan b on a.notiket=b.notransaksi 
		where substr(b.tanggal,1,10) = '".$tanggal."' and millcode='".$_POST['kdOrg']."' and kodebarang='40000003' and kodefraksi in (select distinct kodefraksi from ".$dbname.".pabrik_5fraksi2 order by kodefraksi)  ";
	//echo $qData;
	$rData=fetchData($qData);
	foreach($rData as $brs =>$dt)
	{
	   $listData[$dt['notiket']][$dt['kodefraksi']]=$dt['jumlah'];
	}

	$sNotiket="select notiket from ".$dbname.".pabrik_sortasi a left join ".$dbname.".pabrik_timbangan b on a.notiket=b.notransaksi 
		where substr(b.tanggal,1,10)= '".$tanggal."' and millcode='".$_POST['kdOrg']."' and kodebarang='40000003' and kodefraksi in (select distinct kodefraksi from ".$dbname.".pabrik_5fraksi2 order by kodefraksi)  group by `notiket` order by `notiket`  ";
        $qNotiket=$owlPDO->query($sNotiket) or die(print " Gagal: ".PDOException::getMessage());
        $qNotiket->setFetchMode(PDO::FETCH_ASSOC);
        while($rNotiket=$qNotiket->fetch())
        {
			$no+=1;
			$sJjg="select jjgsortasi,tanggal,persenBrondolan,kgpotsortasi from ".$dbname.".pabrik_timbangan where notransaksi='".$rNotiket['notiket']."'";
                       $qJjg=$owlPDO->query($sJjg) or die(print " Gagal: ".PDOException::getMessage());
                        $qJjg->setFetchMode(PDO::FETCH_ASSOC);
                        $rJjg=$qJjg->fetch();
			
			echo"<tr class=rowcontent onclick=\"editDet('".$rNotiket['notiket']."','".tanggalnormal((substr($rJjg['tanggal'],0,10)))."');\" style=\"cursor:pointer\">
				 <td align=center>".$no."</td>";
			echo"<td align=center>".$rNotiket['notiket']."</td>";
			$sKdFrak="select kodefraksi from ".$dbname.".pabrik_sortasi where notiket='".$rNotiket['notiket']."'";
			$rKdFrak=fetchData($sKdFrak);
			foreach($rHead as $row2 =>$isi2)
			{
				setIt($listData[$rNotiket['notiket']][$isi2['kode']],0);
				echo "<td  align=right>".number_format($listData[$rNotiket['notiket']][$isi2['kode']],2)."</td>";
			}

			echo"<td align=right>".number_format($rJjg['jjgsortasi'],2)."</td>";
			echo"<td align=right>".number_format($rJjg['kgpotsortasi'],2)."</td><td align=center >
			<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delDet('".$rNotiket['notiket']."');\"></td></tr>";
	}


	echo"</tbody></table></div>";
	break;
	case'getNetto':
                $netto=0;
		$str="select beratbersih from ".$dbname.".pabrik_timbangan where notransaksi='".$_POST['noticket']."'";
               $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
                $res->setFetchMode(PDO::FETCH_OBJ);
                while($bar=$res->fetch())
                {
			$netto=$bar->beratbersih;
		}
		echo $netto;  
	break;    
	default:
	break;
}