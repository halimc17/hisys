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
	// exit('warning : masukk');
		$userid=$_SESSION['standard']['userid'];
		
		// Search Query
		// $txt_search='';
		// $txt_tgl='';
		// if((isset($_POST['txtSearch']))||(isset($_POST['tglCari']))) {
		// 	$txt_search=!empty($_POST['txtSearch'])? $_POST['txtSearch']: '';
		// 	if(!empty($_POST['tglCari'])) {
		// 		$txt_tgl=tanggalsystem($_POST['tglCari']);
		// 		$txt_tgl_a=substr($txt_tgl,0,4);
		// 		$txt_tgl_b=substr($txt_tgl,4,2);
		// 		$txt_tgl_c=substr($txt_tgl,6,2);
		// 		$txt_tgl=$txt_tgl_a."-".$txt_tgl_b."-".$txt_tgl_c;
		// 	}
  //       }
		
		$where = "";
        if(!empty($txtsearch)) {
            $where .= " and nopo LIKE '%".$txtsearch."%'";
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
        $str="SELECT * FROM ".$dbname.".log_poht
			where (persetujuan1='".$_SESSION['standard']['userid']."' or
				   persetujuan2='".$_SESSION['standard']['userid']."' or
				   persetujuan3='".$_SESSION['standard']['userid']."') and (statuspo!='3' and statuspo!='2') ".$where;
		// exit('warning : '.$str);
        $res=fetchdata($str);
        $jlhbrs=count($res);
        if($jlhbrs==0){
            $tab.="<tr class=rowcontent>";
            $tab.="<td colspan=7>".$_SESSION['lang']['dataempty']."</td>";
            $tab.="</tr>";
        }else{
		$str="SELECT * FROM ".$dbname.".log_poht
			where (persetujuan1='".$_SESSION['standard']['userid']."' or
				   persetujuan2='".$_SESSION['standard']['userid']."' or
				   persetujuan3='".$_SESSION['standard']['userid']."') and (statuspo!='3' and statuspo!='2')
				   ".$where." ORDER BY `nopo` DESC limit ".$offset.",".$limit."";
		$res=$owlPDO->query($str);
		$res->setFetchMode(PDO::FETCH_ASSOC);
			$no=0;
			while($bar=$res->fetch()) 
			{
				// $tab="";
				$kodeorg=$bar['kodeorg'];
				$spr="select * from  ".$dbname.".organisasi where  kodeorganisasi='".$kodeorg."' or induk='".$kodeorg."'"; //echo $spr;
				$bas=$owlPDO->query($spr);
				$bas->setFetchMode(PDO::FETCH_OBJ);
				$br=$bas->fetch();	
				$no+=1;
				$tab.="<tr class=rowcontent >
					<td>".$no."</td>
					<td >".$bar['nopo']."</td>
					<td>".tanggalnormal($bar['tanggal'])."</td>
					<td>".$br->namaorganisasi."</td>
					<td align=center><img src=images/pdf.jpg class=resicon width='30' height='30' title='Print' onclick=\"masterPDF('log_poht','".$bar['nopo']."','','log_slave_print_detail_po',event);\"></td>";       

        			for($a=1;$a<3;$a++)
					{
						if($bar['statuspo']=='1')
						{
							$tb=$optttd[$bar['persetujuan'.$a]];
							$tb.="<br>";
							$s = $a - 1;
							
							if($s > 0)
							{
								if (($bar['hasilpersetujuan'.$s]=='')||($bar['hasilpersetujuan'.$s]==0))
								{
									$tb.="Proses Persetujuan ".$s;
								}
								else if($bar['hasilpersetujuan'.$s]=='1')
								{
									if($bar['persetujuan'.$a]==$_SESSION['standard']['userid'])
									{
										$tb.="<button class=mybutton onclick=\"get_data_po('".$bar['nopo']."','".$a."')\">".$_SESSION['lang']['disetujui']."</button>&nbsp;
										<button class=mybutton onclick=rejected_po('".$bar['nopo']."','".$a."')>".$_SESSION['lang']['ditolak']."</button>";
									}
									else
									{
										$tb.="(Menunggu Keputusan)";
									}
								}
								else
								{
									$tb.="(Ditolak)";
								}
							}
							else
							{
								if (($bar['hasilpersetujuan'.$a]=='')||($bar['hasilpersetujuan'.$a]==0))
								{
									if($bar['persetujuan'.$a]==$_SESSION['standard']['userid'])
									{
										$tb.="<button class=mybutton onclick=\"get_data_po('".$bar['nopo']."','".$a."')\">".$_SESSION['lang']['disetujui']."</button>&nbsp;
										<button class=mybutton onclick=rejected_po('".$bar['nopo']."','".$a."')>".$_SESSION['lang']['ditolak']."</button>";
									}
									else
									{
										$tb.="(Menunggu Keputusan)";
									}
								}
								else if($bar['hasilpersetujuan'.$a]=='1')
								{
									$tb.="(Disetujui)";
								}
								else
								{
									$tb.="(Ditolak)";
								}
							}
						}
						else
						{
							$tb="Proses Pengajuan";
						}
						
						$tab.="<td align=center>".$tb."</td>";
								
                    }      
                    $tab.="</tr>";  
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
                <tr><td colspan=7 align=center>
                <button class=mybutton onclick=refresh_data(".($page-1).");>".$_SESSION['lang']['pref']."</button>
                <select id=\"pages\" name=\"pages\" style=\"width:50px\" onchange=\"getPage()\">".$isiRow."</select>
                <button class=mybutton onclick=refresh_data(".($page+1).");>".$_SESSION['lang']['lanjut']."</button>
                </td>
                </tr>";
        }
        echo $tab."####".$footd;
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