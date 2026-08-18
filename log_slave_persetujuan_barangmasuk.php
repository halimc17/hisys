<?php
	require_once('master_validation.php');
	include('lib/nangkoelib.php');
	require_once('config/connection.php');
	include_once('lib/zLib.php');
	$method=			$_POST['method'];
	$nopo=				isset($_POST['nopo'])? $_POST['nopo']: '';
	$user_id=			$_SESSION['standard']['userid'];
	$rlse_user_id=		isset($_POST['id_user'])? $_POST['id_user']: '';
	$comment_persetujuan=isset($_POST['cm_hasil'])? $_POST['cm_hasil']: '';
	$user_id_frwd=		isset($_POST['id_user_frwd'])? $_POST['id_user_frwd']: '';
	$kolom=				isset($_POST['kolom'])? $_POST['kolom']: '';
	$notransaksi=				isset($_POST['notransaksi'])? $_POST['notransaksi']: '';
	$alasan=				isset($_POST['alasan'])? $_POST['alasan']: '';
	$kolom_persetujuan=	'hasilpersetujuan'.$kolom;
	$this_date=			date("Y-m-d");
	$txtsearch = checkPostGet('txtsearch', '');
	$tgl_cari = tanggalsystem(checkPostGet('tgl_cari', ''));
	$optttd=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan');
	
	switch ($method)
	{
	case 'insert_forward_po':
	//echo "warning:masuk";exit();
	$sql="select statuspo,persetujuan1,persetujuan2,persetujuan3 from ".$dbname.".log_poht where `nopo`='".$nopo."'";
	$res=$owlPDO->query($sql);
	$res->setFetchMode(PDO::FETCH_ASSOC);
			
		if($res['statuspo']=='2')
		{
			echo"Warning:This No.PO :".$nopo." is Already Release";
			exit();
		}		
		elseif($res['statuspo']=='1')
		{			
			
			if($res['persetujuan1']!='')
			{		
					
					$a=1;
					for($i=2;$i<4;$i++)
					{	
						//echo "warning".$i;
						if($user_id_frwd==$res['persetujuan'.$a])
						{
							echo"Warning:Please Check Employee Name, Maybe Already Used It";
							exit();									
						}
						elseif($res['persetujuan'.$i]==''&&$res['hasilpersetujuan'.$a]=='')
						{
							//echo "warning masuk".$i.$a;exit();
							
							$strx="update ".$dbname.".log_poht set persetujuan".$i."='".$user_id_frwd."',".$kolom_persetujuan."='1',tglp".$a."='".$this_date."' where `nopo`='".$nopo."'"; 					
							//$stat="Verivication Yang Ke ".$i;
							try{
							   $owlPDO->exec($strx); //insert hedaer	
							   exit();
							}catch (PDOException $e){
								echo $strx;
								echo "Gagal : ".$e->getMessage();
								exit();
							}	
						}
						elseif(($res['persetujuan3']!='')&&($res['persetujuan3']==$user_id))
						{
							$strx="update ".$dbname.".log_poht set hasilpersetujuan3='1',statuspo='2',tglp3='".$this_date."' where `nopo`='".$nopo."'";	
							try{
							   $owlPDO->exec($strx); 
							   break;
							   exit();
							}catch (PDOException $e){
								echo $strx;
								echo "Gagal : ".$e->getMessage();
								exit();
							}				
						}
						$a++;
						
						
					}						
			}//echo "WARNING:".$strx; exit();
		}
		
	
	break;
	
	case 'insert_close_po':
		//echo "warning:masuk";exit();
		$sql="select* from ".$dbname.".log_poht where nopo='".$nopo."'";
		$res=$owlPDO->query($sql);
		$res->setFetchMode(PDO::FETCH_ASSOC);
	
		if(($res['persetujuan3']!='')&&($user_id==$res['persetujuan3']))
		{
			
			$sql2="update ".$dbname.".log_poht set `statuspo`='2',hasilpersetujuan3='1',tglp3='".$this_date."' where nopo='".$nopo."'";	//echo"warning".$sql2;
			try{
			   $owlPDO->exec($sql2); 
			   break;
			   exit();
			}catch (PDOException $e){
				echo $sql2;
				echo "Gagal : ".$e->getMessage();
				exit();
			}	
		}
		elseif($res['persetujuan3']=='')
		{
			
				if(($res['statuspo']==1)&&($res['purchaser']!=$user_id))
				{
					for($i=1;$i<4;$i++)
					{
						//echo "warning:masuk".$res['persetujuan'];exit();
						if(($res['persetujuan'.$i]!='')&&($res['hasilpersetujuan'.$i]==''))
						{
							$sql2="update ".$dbname.".log_poht set persetujuan".$i."='".$rlse_user_id."',hasilpersetujuan".$i."='1',`statuspo`='2',tglp".$i."='".$this_date."' where nopo='".$nopo."'";								
							try{
							   $owlPDO->exec($sql2); 
							   break;
							   exit();
							}catch (PDOException $e){
								echo $sql2;
								echo "Gagal : ".$e->getMessage();
								exit();
							}
						}
					}
				}
			}
			else
			{
				echo "Warning: You're not have authorized in this PP";
				exit();
			}
	break;
	case 'rejected_pp_ex':
	//exit('warning : masukk');
	$sql="select* from ".$dbname.".log_poht where nopo='".$nopo."'";
	// exit('warning : '.$sql);
	$rs=$owlPDO->query($sql);
	$rs->setFetchMode(PDO::FETCH_ASSOC);
	$res=$rs->fetch();
	if(($res['statuspo']==1)&&($res['purchaser']!=$user_id))
	{
		//exit('warning : '.$res['statuspo']."/".$res['purchaser']."/".$user_id);
					for($c=1;$c<4;$c++)
					{
	
						if($res['persetujuan'.$c]!='')
						{
							if(($res['hasilpersetujuan'.$c]=='')&&($res['persetujuan'.$c]==$user_id))
								{
											 //echo "warning:jamhari";
											  $sql2="update ".$dbname.".log_poht set statuspo='2',hasilpersetujuan".$c."='3',tglp".$c."='".$this_date."' where nopo='".$nopo."'" ;					//echo $sql2; exit();
					  							try{
												   $owlPDO->exec($sql2); 
												}catch (PDOException $e){
													echo $sql2;
													echo "Gagal : ".$e->getMessage();
													exit();
												}
													
								  }
								  elseif(($res['persetujuan'.$c]==$user_id)&&($bar['hasilpersetujuan'.$c]!=''))
								  {
												echo "Warning: You already proceccd this  PP";
												exit();
	
								   }
						}
					}
	  }
	  else
	  {
		echo "Warning: You don`t have Authorizde for this PP";
		exit();
	  }
	break;
	
	case 'list_new_data':
		$userid=$_SESSION['standard']['userid'];
		
		$where = "";
        if(!empty($txtsearch)) {
            $where .= " and notransaksi LIKE '%".$txtsearch."%'";
        }
        if(!empty($tgl_cari)) {
            $where.=" and tanggal LIKE '%".$tgl_cari."%'";
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
        $str="SELECT * FROM ".$dbname.".log_transaksiht
			where (persetujuan1='".$userid."' or
				   persetujuan2='".$userid."') and (post!='1') 
				   and (hasilpersetujuan1!='1' or hasilpersetujuan2!='1')
				   and tipetransaksi='1' ".$where;
				   
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=9 align=center>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
		$str="SELECT * FROM ".$dbname.".log_transaksiht
			where (persetujuan1='".$userid."' or
				   persetujuan2='".$userid."') and (post!='1') 
				   and (hasilpersetujuan1!='1' or hasilpersetujuan2!='1')
				   and tipetransaksi='1'
				   ".$where." ORDER BY `tanggal` asc, `notransaksi` DESC limit ".$offset.",".$limit."";
		$res=$owlPDO->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
			$no=0;
			while($bar=$res->fetch()) 
			{
				$no+=1;
				$optorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$bar['kodegudang']."'");
				$optsup=makeOption($dbname,'log_5supplier','supplierid,namasupplier',"supplierid='".$bar['idsupplier']."'");
				$tab.="<tr class=rowcontent >
					<td>".$no."</td>
					<td >".$bar['kodegudang']." - ".$optorg[$bar['kodegudang']]."</td>
					<td >".$bar['notransaksi']."</td>
					<td>".tanggalnormal($bar['tanggal'])."</td>
					<td>".$bar['nopo']."</td>    
					<td>".$optsup[$bar['idsupplier']]."</td>";
				
				$optstj1=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['persetujuan1']."'");
				$tab.="<td style='text-align:center'>".$optstj1[$bar['persetujuan1']]."<br>";
				if($bar['hasilpersetujuan1']=='1')
				{
					$tab.="(Disetujui)";
				}
				else
				{
					if($userid==$bar['persetujuan1'])
					{
						$tab.="<button class=mybutton onclick=\"get_data_transaksi('".$bar['notransaksi']."','1')\">".$_SESSION['lang']['disetujui']."</button>&nbsp;
						<button class=mybutton onclick=rejected_transaksi('".$bar['notransaksi']."','1')>".$_SESSION['lang']['ditolak']."</button>";
					}
					else
					{
						$tab.="(Menunggu Keputusan)";
					}
				}
				$tab.="</td>";
				
				$optstj2=makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['persetujuan2']."'");
				$tab.="<td style='text-align:center'>".$optstj2[$bar['persetujuan2']]."<br>";
				if($bar['hasilpersetujuan1']=='1')
				{
					if($bar['hasilpersetujuan2']=='1')
					{
						$tab.="(Disetujui)";
					}
					else
					{
						if($userid==$bar['persetujuan2'])
						{
							$tab.="<button class=mybutton onclick=\"get_data_transaksi('".$bar['notransaksi']."','2')\">".$_SESSION['lang']['disetujui']."</button>&nbsp;
							<button class=mybutton onclick=rejected_transaksi('".$bar['notransaksi']."','2')>".$_SESSION['lang']['ditolak']."</button>";
						}
						else
						{
							$tab.="(Menunggu Keputusan)";
						}
					}
				}
				else
				{
					$tab.="(Menunggu Persetujuan 1)";
				}
				
				$tab.="</td>";
				$tab.="<td align=center><img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"previewBapb('".$bar['notransaksi']."',event);\"></td></tr>";  
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
                <tr><td colspan=9 align=center>
                <button class=mybutton onclick=refresh_data(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=refresh_data(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
		break;
		
	case'get_form_approval':
		$str = "update ".$dbname.".log_transaksiht set 
			hasilpersetujuan".$kolom."='1',tanggalpersetujuan".$kolom."='".date('Y-m-d')."' 
			where notransaksi='".$notransaksi."'";
		try 
		{
			$owlPDO->exec($str);
        } 
		catch (PDOException $e) 
		{
			print " Gagal  !: " . $e->getMessage() . "\n";
            die();
        }
    break;
	
	case 'get_form_rejected':
		echo"<div id=rejected_form>
		<fieldset>
		<legend><input type=text readonly=readonly name=rnopo id=rnopo value=".$notransaksi."  /></legend>
		<table cellspacing=1 border=0>
				<tr>
					<td>Alasan</td>
					<td>:</td>
					<td><textarea id=alasan onkeypress=\"return tanpa_kutip(event);\" cols=30 rows=3></textarea></td>
				</tr>
				<td><td><td>
				<button class=mybutton onclick=ditolakpo() id=ditolak >".$_SESSION['lang']['ditolak']."</button>

				<button class=mybutton onclick=cancel_po()>".$_SESSION['lang']['cancel']."</button>
				</td></tr></table>
		</fieldset>
		</div>
		<input type=hidden name=user_id id=user_id value=".$user_id." />
		<input type=hidden name=nopo id=nopo value=".$notransaksi."  />
		<input type=hidden name=kolom id=kolom value='".$kolom."' />
		";
	break;
	
	case 'reject_po':
		$str = "insert into ".$dbname.".log_his_transaksiht(tipetransaksi,notransaksi,tanggal,kodept,untukpt,nopo,nosj,keterangan,statusjurnal,kodegudang,user,namapenerima,mengetahui,idsupplier,nofaktur,post,postedby,untukunit,subunit,notransaksireferensi,gudangx,persetujuan1,hasilpersetujuan1,tanggalpersetujuan1,persetujuan2,hasilpersetujuan2,tanggalpersetujuan2,lastupdate) select * from ".$dbname.".log_transaksiht where notransaksi='".$notransaksi."'";	
		try
		{
			$owlPDO->exec($str); 
			$str2 = "update ".$dbname.".log_his_transaksiht set catatan".$kolom."='".$alasan."' where notransaksi='".$notransaksi."'";
			try
			{
				$owlPDO->exec($str2); 
				$str3 = "insert into ".$dbname.".log_his_transaksidt select * from ".$dbname.".log_transaksidt where notransaksi='".$notransaksi."'";
				try
				{
					$owlPDO->exec($str3); 
					$str4 = "delete from ".$dbname.".log_transaksiht where notransaksi='".$notransaksi."'";
					try
					{
						$owlPDO->exec($str4); 
					}
					catch (PDOException $e)
					{
						echo "Gagal : ".$e->getMessage();
						exit();
					}
				}
				catch (PDOException $e)
				{
					echo "Gagal : ".$e->getMessage();
					exit();
				}
			}
			catch (PDOException $e)
			{
				echo "Gagal : ".$e->getMessage();
				exit();
			}
		}
		catch (PDOException $e)
		{
			echo "Gagal : ".$e->getMessage();
			exit();
		}
	break;
	
	case 'release_po' :
	//echo "warning:masuk";
	
	$sql="select * from ".$dbname.".log_poht where nopo='".$nopo."'";
	$res=$owlPDO->query($sql);
	$res->setFetchMode(PDO::FETCH_ASSOC);
	if(($res['persetujuan1']!='') || ($res['persetujuan2']!='')|| ($res['hasilpersetujuan1']!='') || ($res['hasilpersetujuan2']!='') || ($res['hasilpersetujuan3']!='')) 
	{
		//echo "warning:masuk";
		if(($res['stat_release']==0) && ($res['useridreleasae']=='0000000000'))
		{		
		//	echo "warning:masuk";
			//$unopo="update ".$dbname.".log_poht set stat_release='1',useridreleasae='".$rlse_user_id."',tglrelease='".$this_date."',tanggal='".$this_date."' where nopo='".$nopo."' ";
			$unopo="update ".$dbname.".log_poht set stat_release='1',useridreleasae='".$rlse_user_id."',tglrelease='".$this_date."',tanggal='".$this_date."' where nopo='".$nopo."' ";
			try{
		   		$owlPDO->exec($unopo); 
			}catch (PDOException $e){
				echo $unopo;
				echo "Gagal : ".$e->getMessage();
				exit();
			}
		}
		else
		{
			echo "warning:Already Release";
			exit();
		}
	}
	else
	{
		echo"warning:Can`t Release The PO Yet";
	}
	break;
	case 'un_release_po' :
	//echo "warning:masuk";
	$sql="select * from ".$dbname.".log_poht where nopo='".$nopo."'";
	$res=$owlPDO->query($sql);
	$res->setFetchMode(PDO::FETCH_ASSOC);

		if(($res['stat_release']=='1') && ($res['useridreleasae']==$rlse_user_id)&&($res['tglrelease']==$this_date))
		{		
			$unopo="update ".$dbname.".log_poht set stat_release='0', useridreleasae='0000000000',tglrelease='0000-00-00' where nopo='".$nopo."' ";
			try{
		   		$owlPDO->exec($unopo); 
			}catch (PDOException $e){
				echo $unopo;
				echo "Gagal : ".$e->getMessage();
				exit();
			}
		}
		else
		{
			echo "warning:You Don`t Have Autorize to Unrelease This PO No. ".$nopo;
			exit();
		}
	
	break;
	case 'list_new_data_release_po':
	//exit('warning : masss');
		$limit=10;
		$page=0;
		if(isset($_POST['page']))
		{
		$page=$_POST['page'];
		if($page<0)
		$page=0;
		}
		$offset=$page*$limit;
		
		$sql2="select count(*) as jmlhrow from ".$dbname.".log_poht where lokalpusat='0' ORDER BY nopo DESC";
		$query2=$owlPDO->query($sql2);
		$query2->setFetchMode(PDO::FETCH_OBJ);
		while($jsl=$query2->fetch()){
			$jlhbrs= $jsl->jmlhrow;
		}
	 
		$str="SELECT * FROM ".$dbname.".log_poht where lokalpusat='0' ORDER BY nopo DESC LIMIT ".$offset.",".$limit."";
		$res=$owlPDO->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch())
		{
			$no+=1;
			echo"<tr class=rowcontent id='tr_".$no."'>
				  <td>".$no."</td>
				  <td id=td_".$no.">".$bar['nopo']."</td>
				  <td>".tanggalnormal($bar['tanggal'])."</td>
				  <td align=center>".$kodeorg."</td>
				  <!--<td align=center><img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"masterPDF('log_poht','".$bar['nopo']."','','log_slave_print_log_po',event);\"></td>-->";                            
			for($i=1;$i<4;$i++)
			{
				//echo $bar['hasilpersetujuan'.$i];
				if($bar['persetujuan'.$i]!='')
				{
					if($bar['hasilpersetujuan'.$i]=='1')
					{
						$st=$_SESSION['lang']['disetujui'];
					}
					elseif($bar['hasilpersetujuan'.$i]=='2')
					{
						$st=$_SESSION['lang']['ditolak'];
					}
					else
					{
						$st=$_SESSION['lang']['wait_approve'];
					}
					$kr=$bar['persetujuan'.$i];
					$sql="select * from ".$dbname.".datakaryawan where karyawanid='".$kr."'";
					$yrs=$owlPDO->query($sql);
					$yrs->setFetchMode(PDO::FETCH_ASSOC);
					echo"<td align=center><a href=# onclick=\"cek_status_pp('".$bar['hasilpersetujuan'.$i]."')\">".$yrs['namakaryawan']."<br />(".$st.")</a></td>";
				}
				else
				{
					echo"<td>&nbsp;</td>";
				}
			}
				  
			if(($bar['statuspo']=='2'))
			{
				if(($bar['stat_release']=='1')&&($bar['useridreleasae']!='0000000000'))
				{
					$disbled="<td align=center>".tanggalnormal($bar['tglrelease'])."</td>";
				}
				else
				{
					$disbled="<td><button class=mybutton onclick=\"release_po('".$bar['nopo']."')\" >".$_SESSION['lang']['release_po']."</button></td>";
				}
				
				if(($bar['stat_release']=='0')&&($bar['useridreleasae']=='0000000000'))
				{
					$disbled2="<td><button class=mybutton onclick=\"un_release_po('".$bar['nopo']."') \" disabled>".$_SESSION['lang']['un_release_po']."</button></td>";
				}
				else
				{
					if($bar['tglrelease']==$this_date)
					{
						$disbled2="<td><button class=mybutton onclick=\"un_release_po('".$bar['nopo']."') \">".$_SESSION['lang']['un_release_po']."</button></td>";
					}
					else
					{
						$disbled2="<td>&nbsp;</td>";
					}
				}
				echo $disbled; 
				echo $disbled2; 
			}
			else 
			{
				echo"<td colspan='2' align='center'>".$_SESSION['lang']['wait_approval']."</td>";
			}
			echo"</tr><input type=hidden id=nopo_".$no." name=nopo_".$no." value='".$bar['nopo']."' />";
		}
		echo" <tr><td colspan=8 align=center>
				".(($page*$limit)+1)." to ".(($page+1)*$limit)." Of ".  $jlhbrs."<br />
				<button class=mybutton onclick=cariBast(".($page-1).");>".$_SESSION['lang']['pref']."</button>
				<button class=mybutton onclick=cariBast(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
				</td>
				</tr>";   	
	break;
	default:
	break;
	}
?>