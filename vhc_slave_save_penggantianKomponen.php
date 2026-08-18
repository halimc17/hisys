<?php
session_start();
require_once('master_validation.php');
require_once('config/connection.php');
include_once('lib/nangkoelib.php');
include_once('lib/zLib.php');

$proses=checkPostGet('proses','');
$lokasi=$_SESSION['empl']['lokasitugas'];
$tglGanti=tanggalsystem(checkPostGet('tglGanti',''));
$kdJenis=checkPostGet('kdjenis','');
$usr_id=$_SESSION['standard']['userid'];
$notransaksi=checkPostGet('notrans','');
$codeOrg=checkPostGet('codeOrg','');
$descDmg=checkPostGet('descDmg','');
$dwnTime=checkPostGet('dwnTime','');
$statInp=checkPostGet('statInp','');
$kdTraksiDt=makeOption($dbname,'vhc_5master','kodevhc,kodetraksi');

switch($proses)
{
    case'generate_no':
		//lokasi tugas/y/m/no urut (4)
		if($notransaksi!='')
		{
			echo $notransaksi;
		}
		else
		{	
			$tgl=  date('Ymd');
			$bln = substr($tgl,4,2);
			$thn = substr($tgl,0,4);

			$notransaksi=$codeOrg."/".date('Y')."/".date('m')."/";
			$ql="select `notransaksi` from ".$dbname.".`vhc_penggantianht` where notransaksi like '%".$notransaksi."%' order by `notransaksi` desc limit 0,1";
			$qr=$owlPDO->query($ql) or die(print " Gagal: ".PDOException::getMessage());
			$qr->setFetchMode(PDO::FETCH_OBJ);
			$rp=$qr->fetch();
			setIt($rp->notransaksi,'');
			$awal=substr($rp->notransaksi,-4,4);
			$awal=intval($awal);
			$cekbln=substr($rp->notransaksi,-7,2);
			$cekthn=substr($rp->notransaksi,-12,4);
			if(($bln!=$cekbln)&&($thn!=$cekthn)) {
				$awal=1;
			} else {
                $awal++;
            }
			$counter=addZero($awal,4);
			$notransaksi=$codeOrg."/".$thn."/".$bln."/".$counter;
            echo $notransaksi;
        }
        break;

    case'load_data':
        OPEN_BOX();
        echo"<fieldset>
			<legend>".$_SESSION['lang']['list']."</legend>";
        echo"<table cellspacing=1 border=0 class=sortable>
            <thead>
			<tr class=rowheader>
			<td>".$_SESSION['lang']['notransaksi']."</td>
			<td>".$_SESSION['lang']['tanggal']."</td>
			<td>".$_SESSION['lang']['kodevhc']."</td>
			<td>".$_SESSION['lang']['jenisvch']."</td>
			<td>".$_SESSION['lang']['downtime']."</td>
			<td>Action</td>
			</tr>
			</thead>
			<tbody>";
		$limit=20;
		$page=0;
		if(isset($_POST['page']))
		{
			$page=$_POST['page'];
            if($page<0) $page=0;
        }
        $offset=$page*$limit;
		
		$cond="";
		if(($_SESSION['org']['tipeinduk']=='KANWIL')||($_SESSION['org']['tipeinduk']=='HOLDING'))
		{
			$cond.=" order by `tanggal` desc";
		}
		else
		{
			$cond.=" where updateby='".$_SESSION['standard']['userid']."' order by `tanggal` desc";
		}
		$sql2="select count(*) as jmlhrow from ".$dbname.".vhc_penggantianht ".$cond."";
		$query2=$owlPDO->query($sql2) or die(print " Gagal: ".PDOException::getMessage());
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while($jsl=$query2->fetch()){
			$jlhbrs= $jsl->jmlhrow;
		}
		$slvhc="select * from ".$dbname.".vhc_penggantianht ".$cond." limit ".$offset.",".$limit."";
		$qlvhc=$owlPDO->query($slvhc) or die(print " Gagal: ".PDOException::getMessage());
		$qlvhc->setFetchMode(PDO::FETCH_ASSOC);
		while($rlvhc=$qlvhc->fetch())
		{
			$pvhc="select kodevhc,jenisvhc from ".$dbname.".vhc_5master where kodevhc='".$rlvhc['kodevhc']."'";
			$qpvhc=$owlPDO->query($pvhc) or die(print " Gagal: ".PDOException::getMessage());
			$qpvhc->setFetchMode(PDO::FETCH_ASSOC);
			$rpvhc=$qpvhc->fetch();
			echo"
				<tr class=rowcontent>
				<td>". $rlvhc['notransaksi']."</td>
				<td>". tanggalnormal($rlvhc['tanggal'])."</td>
				<td>". $rlvhc['kodevhc']."</td>
				<td>". $rpvhc['jenisvhc']."</td>
				<td>". $rlvhc['downtime']."</td>";
			
			if($rlvhc['posting']=='0') {
				echo
				"
				<td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('". $rlvhc['kodeorg']."','". $rlvhc['notransaksi']."','".tanggalnormal($rlvhc['tanggal'])."','". $rlvhc['kodevhc']."','". $rlvhc['posting']."','". $rlvhc['downtime']."','". $rlvhc['kerusakan']."','".$kdTraksiDt[$rlvhc['kodevhc']]."');\">
				<img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"delData('". $rlvhc['notransaksi']."','". $rlvhc['kodevhc']."');\" >	
				<img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('vhc_penggantianht','".$rlvhc['notransaksi'].",".$rlvhc['kodevhc']."','','vhc_slave_penggunaanKomponen',event);\"></td>
				</tr>";
			} else {
				echo"<td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('vhc_penggantianht','".$rlvhc['notransaksi'].",".$rlvhc['kodevhc']."','','vhc_slave_penggunaanKomponen',event);\"></td>";
			}
        }
		echo"
		<tr><td colspan=5 align=center>
		".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
		<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
		<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
		</td>
		</tr>";
		echo"</table></fieldset>";
		CLOSE_BOX();
		break;
	
    case'delete':
		$sql="delete from ".$dbname.".vhc_penggantianht where notransaksi='".$notransaksi."'";
		try{
			$owlPDO->exec($sql);
			$sql2="delete from ".$dbname.".vhc_penggantiandt where notransaksi='".$notransaksi."'";
			try{
				$owlPDO->exec($sql2);
			}catch (PDOException $e){
				echo "error : ".$e->getMessage();
			}
		}catch (PDOException $e){
			echo "error : ".$e->getMessage();
		}
		break;
		case'cari_barang':
				$txtcari=$_POST['txtcari'];
		$str="select a.kodebarang,a.namabarang,a.satuan from ".$dbname.".log_5masterbarang a where a.namabarang like '%".$txtcari."%' or a.kodebarang like '%".$txtcari."' and kelompokbarang in (331,332,333,334,335,336,338,341,342,375)";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$numRowss = owlBaris($res);
		$res->setFetchMode(PDO::FETCH_OBJ);
		
		if($numRowss<1)
		{
			echo"Error: ".$_SESSION['lang']['tidakditemukan'];			
		}
		else
		{
			echo"
			<fieldset>
			<legend>".$_SESSION['lang']['result']."</legend>
			<div style=\"width:450px; height:300px; overflow:auto;\">
				<table class=sortable cellspacing=1 border=0>
					<thead>
						<tr class=rowheader>
							<td>No</td>
							<td>".$_SESSION['lang']['kodebarang']."</td>
							<td>".$_SESSION['lang']['namabarang']."</td>
							<td>".$_SESSION['lang']['satuan']."</td>
						</tr>
					</thead>
					<tbody>";
            $no=0;	 
			while($bar=$res->fetch()) {
				$no+=1;
				echo"<tr class=rowcontent style='cursor:pointer;' title='Click' onclick=\"throwThisRow('".$bar->kodebarang."','".$bar->namabarang."','".$bar->satuan."');\">
					<td>".$no."</td>
					<td>".$bar->kodebarang."</td>
					<td>".$bar->namabarang."</td>
					<td>".$bar->satuan."</td>
				</tr>";			   	
            }
			echo "
				</tbody>
				<tfoot></tfoot>
				</table></div></fieldset>";	
		}
        break;
	
    case'cek_entry_jenis_vhc':
        //Untuk Cek Kode Vehicle, menghindari error menginput satu kode vehicle dihari yang sama

		$sql_cek="select * from ".$dbname.".vhc_penggantianht where tanggal ='".$tglGanti."' and kodevhc='".$kdJenis."'";
		$query_cek=$owlPDO->query($sql_cek) or die(print " Gagal: ".PDOException::getMessage());
		$res=owlBaris($query_cek);

		if($res>0)
		{
				echo 'warning: duplicate entry';
				exit ();
		}
		if(($codeOrg=='')||($tglGanti=='')||($dwnTime=='')||($descDmg==''))
		{
				echo 'warning: Please complete form';
				exit ();
		}
        break;
	
    case 'cek_data_header' :
                if(($notransaksi!='')||($tglGanti!='')||($dwnTime!='')||($descDmg!=''))
                {

                    $sql="select * from ".$dbname.".vhc_penggantianht where notransaksi='".$_POST['notrans']."'";
                    $query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
                        $row=owlBaris($query);
                        //echo "warning:masuk <pre>".print_r($row)."</pre>";
                        if($row<1)
                        {
                                foreach($_POST['kdbrg'] as $brs => $isi)
                                {
                                        $kodebarang=$isi;
                                        $satuan=$_POST['satuan'][$brs];
                                        $jumlah=$_POST['jmlhMinta'][$brs];
                                        $keterangan=$_POST['ketrngn'][$brs];
                                        if(($kodebarang=='') || ($jumlah==''))
                                        {
                                                echo"warning: Please complete form";
                                                exit();
                                        }
                                        else
                                        {
                                                $sins="insert into ".$dbname.".vhc_penggantianht (`kodeorg`,`kodevhc`,`tanggal`,`updateby`,`notransaksi`,`downtime`, `kerusakan`) values 
                                                ('".$codeOrg."','".$kdJenis."','".$tglGanti."','".$usr_id."','".$notransaksi."','".$dwnTime."','".$descDmg."')";
                                                
												try{
													$owlPDO->exec($sins);
													$dins="insert into ".$dbname.".vhc_penggantiandt (`notransaksi`,`kodebarang`,`jumlah`,`satuan`,`keterangan`) values ('".$notransaksi."','".$kodebarang."','".$jumlah."','".$satuan."','".$keterangan."')";
													try{
														$owlPDO->exec($dins);
													}catch (PDOException $e){
														echo "error : ".$e->getMessage();
													}
												}catch (PDOException $e){
													echo "error : ".$e->getMessage();
												}

                                        }
                                }
           }
                }
                else
                {
                        echo"warning: Please complete form";
                        exit();
                }
                $test=count($_POST['kdbrg']);
                echo $test;
        break;
                case'insert':
                if(($notransaksi!='')||($tglGanti!='')||($dwnTime!='')||($descDmg!=''))
                {
                        $sql="select * from ".$dbname.".vhc_penggantianht where notransaksi='".$_POST['notrans']."'";
						$query=$owlPDO->query($sql) or die(print " Gagal: ".PDOException::getMessage());
                        $row=owlBaris($query);
                        if($row<1){
							$sins="insert into ".$dbname.".vhc_penggantianht (`kodeorg`,`kodevhc`,`tanggal`,`updateby`,`notransaksi`,`downtime`, `kerusakan`,`createdtime`) values ('".$codeOrg."','".$kdJenis."','".$tglGanti."','".$usr_id."','".$notransaksi."','".$dwnTime."','".$descDmg."','".date('Y-m-d H:i:s')."')";
							try{
								$owlPDO->exec($sins);
							}catch (PDOException $e){
								echo "error : ".$e->getMessage();
							}
                        }
                        else
                        {
                        echo"warning: Transaction Number already exist";
                        exit();
                        }

                }
                else
                {
                        echo"warning: Please complete form";
                        exit();
                }
                break;
                case 'delete_all':
                //echo "warning:masuk";
                $sql="delete from ".$dbname.".vhc_penggantianht where notransaksi='".$notransaksi."' and kodevhc='".$kdJenis."'";
				try{
					$owlPDO->exec($sql);
					$sqld="delete from ".$dbname.".vhc_penggantiandt where notransaksi='".$notransaksi."' ";
					try{
						$owlPDO->exec($sqld);
					}catch (PDOException $e){
						echo "error : ".$e->getMessage();
					}
				}catch (PDOException $e){
					echo "error : ".$e->getMessage();
				}
                break;
                case 'cari_transaksi':
                 OPEN_BOX();
                 echo"<fieldset>
<legend>".$_SESSION['lang']['result']."</legend>";
                        echo"<div style=\"width:600px; height:450px; overflow:auto;\">
                        <table cellspacing=1 border=0>
                <thead>
<tr class=rowheader>
<td>".$_SESSION['lang']['notransaksi']."</td>
<td>".$_SESSION['lang']['tanggal']."</td>
<td>".$_SESSION['lang']['kodevhc']."</td>
<td>".$_SESSION['lang']['jenisvch']."</td>
<td>".$_SESSION['lang']['downtime']."</td>
<td>Action</td>
</tr>
</thead>
<tbody>
";
                if(isset($_POST['txtSearch']))
                {
                        $txt_search=$_POST['txtSearch'];
                        $txt_tgl=tanggalsystem($_POST['txtTgl']);
                        $txt_tgl_a=substr($txt_tgl,0,4);
                        $txt_tgl_b=substr($txt_tgl,4,2);
                        $txt_tgl_c=substr($txt_tgl,6,2);
                        $txt_tgl=$txt_tgl_a."-".$txt_tgl_b."-".$txt_tgl_c;
                }
                else
                {
                        $txt_search='';
                        $txt_tgl='';			
                }
				$where="";
                        if($txt_search!='')
                        {
                                $where=" notransaksi LIKE  '%".$txt_search."%'";
                        }
                        elseif($txt_tgl!='')
                        {
                                $where.=" tanggal LIKE '".$txt_tgl."'";
                        }
                        elseif(($txt_tgl!='')&&($txt_search!=''))
                        {
                                $where.=" notransaksi LIKE '%".$txt_search."%' and tanggal LIKE '%".$txt_tgl."%'";
                        }
                //echo $strx; exit();
                if($txt_search==''&&$txt_tgl=='')
                {
                        $strx="select * from ".$dbname.".vhc_penggantianht where  ".$where." order by tanggal desc";

                }
                else
                {
                                $strx="select * from ".$dbname.".vhc_penggantianht where   ".$where." order by tanggal desc";

                }
                //echo "warning:".$strx; exit();

						$res=$owlPDO->query($strx) or die(print " Gagal: ".PDOException::getMessage());
						$numrows=owlBaris($res);
						$res->setFetchMode(PDO::FETCH_ASSOC);
                                
                                if($numrows<1)
                                {
                                        echo"<tr class=rowcontent><td colspan=5>Not Found</td></tr>";
                                }
                                else
                                {
                                        while($rlvhc=$res->fetch())
                                        {
                                                $pvhc="select kodevhc,jenisvhc from ".$dbname.".vhc_5master where kodevhc='".$rlvhc['kodevhc']."'";
												$qpvhc=$owlPDO->query($pvhc) or die(print " Gagal: ".PDOException::getMessage());
												$qpvhc->setFetchMode(PDO::FETCH_ASSOC);
                                                $rpvhc=$qpvhc->fetch();
                                        echo"
                                        <tr class=rowcontent>
                                        <td>". $rlvhc['notransaksi']."</td>
                                        <td>". tanggalnormal($rlvhc['tanggal'])."</td>
                                        <td>". $rlvhc['kodevhc']."</td>
                                        <td>". $rpvhc['jenisvhc']."</td>
                                        <td>". $rlvhc['downtime']."</td>";
                                        if($rlvhc['updateby']==$usr_id)
                                        {
                                        echo
                                        "
                                        <td><img src=images/application/application_edit.png class=resicon  title='Edit' onclick=\"fillField('". $rlvhc['kodeorg']."','". $rlvhc['notransaksi']."','".tanggalnormal($rlvhc['tanggal'])."','". $rlvhc['kodevhc']."');\">
                                        <img src=images/application/application_delete.png class=resicon  title='Delete' onclick=\"masterPDF('vhc_penggantianht','".$rlvhc['notransaksi'].",".$rlvhc['kodevhc']."','','vhc_slave_penggunaanKomponen',event);\">	
                                        <img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('vhc_penggantianht','".$rlvhc['notransaksi'].",".$rlvhc['kodevhc']."','','vhc_slave_penggunaanKomponen',event);\"></td>
                                        ";}
                                        else
                                        {
                                                echo"<td><img src=images/pdf.jpg class=resicon  title='Print' onclick=\"masterPDF('vhc_penggantianht','".$rlvhc['notransaksi'].",".$rlvhc['kodevhc']."','','vhc_slave_penggunaanKomponen',event);\"></td>";
                                        }
                                        echo"</tr>";
                                        }
                                        echo"</tbody></table></div></fieldset>";

                                }	
                        CLOSE_BOX();
                break;
                case 'update_header':
                //tanggal ='".$tglGanti."' and kodevhc='".$kdJenis."'
                $sql_cek="select * from ".$dbname.".vhc_penggantianht where notransaksi='".$notransaksi."'";
                $query_cek=$owlPDO->query($sql_cek) or die(print " Gagal: ".PDOException::getMessage());
				$query_cek->setFetchMode(PDO::FETCH_ASSOC);
                $res=$query_cek->fetch();
                if(($res['tanggal']!=$tglGanti)&&($res['kodevhc']!=$kdJenis))
                {
                        $sql_cek2="select * from ".$dbname.".vhc_penggantianht where tanggal ='".$tglGanti."' and kodevhc='".$kdJenis."'";
						$query_cek2=$owlPDO->query($sql_cek2) or die(print " Gagal: ".PDOException::getMessage());
                        $rCek=owlBaris($query_cek2);
                        if($rCek<1)
                        {
                                $sup="update ".$dbname.".vhc_penggantianht set kodevhc='".$kdJenis."',tanggal='".$tglGanti."' where notransaksi='".$notransaksi."'";
								try{
									$owlPDO->exec($sup);
								}catch (PDOException $e){
									echo "error : ".$e->getMessage();
								}
                        }
                        else
                        {
                                echo "warning: duplicate entry";
                                exit();
                        }
                }

                break;
				case'getVhc':
					$svhc="select distinct kodevhc from ".$dbname.".vhc_5master where kodetraksi='".$_POST['kdTraksi']."' order by kodevhc asc";
					$qvhc=$owlPDO->query($svhc) or die(print " Gagal: ".PDOException::getMessage());
					$qvhc->setFetchMode(PDO::FETCH_ASSOC);
					while($rvhc=$qvhc->fetch()){
						if($_POST['kdVhc']==$rvhc['kodevhc']){
							$optVhc.="<option value='".$rvhc['kodevhc']."' selected>".$rvhc['kodevhc']."</option>";
						}else{
							$optVhc.="<option value='".$rvhc['kodevhc']."'>".$rvhc['kodevhc']."</option>";
						}
					}
					echo $optVhc;
				break;
                default:
                break;
        }
?>