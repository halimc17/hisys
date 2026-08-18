<?php
require_once('master_validation.php');
require_once('lib/nangkoelib.php');
require_once('config/connection.php');
include_once('lib/zLib.php');

$method=checkPostGet('method','');
$path = "fileupload/efilling/";

$uploadpath=checkPostGet('uploadpath','');

$id=checkPostGet('id','');
$foldername=checkPostGet('foldername','');
$lbldatapath=checkPostGet('lbldatapath','');
$induk=checkPostGet('induk','');
$level=checkPostGet('level','');
$title=checkPostGet('title','');

$oldname=checkPostGet('oldname','');
$newname=checkPostGet('newname','');

$createdby=$_SESSION['standard']['userid'];
$createdtime=date('Y-m-d H:i:s');
$updateby=$_SESSION['standard']['userid'];
$updatetime=date('Y-m-d H:i:s');

$ptx=checkPostGet('pt','');
$unitx=checkPostGet('unit','');
$periodex1=checkPostGet('periodex1','');
$periodex2=checkPostGet('periodex2','');
$supplierx=checkPostGet('supplierx','');
$namno=checkPostGet('namno','');
$novo=checkPostGet('novo','');

$hideuser = " and sourceid not in (select idfolder from ".$dbname.".fil_5hideuser where karyawanid='".$_SESSION['standard']['userid']."')";

switch ($method)
{
	case'openfolder':
		##GET INDUK
		$str="select * from ".$dbname.".filemanager where id='".$id."' and status='1' ".$hideuser."";
		$res=fetchData($str);
		$induk = $res[0]['induk'];
		$tab=$tab2="";
		
		if($induk!='')
		{
			$tab2.="<tr class='rowcontent' style='cursor:pointer' onclick=\"levelup()\">
				<td colspan=6>
					<img title=Expand class=arrow src='images/foldo1.png' height=15px style='cursor:normal'> 
					<label>..</label>
				</td>
			</tr>";
		}
		
		##GET UL
		$str="select * from ".$dbname.".filemanager where induk='".$id."' and status='1' ".$hideuser." order by formaticon desc, namafile asc";
		$res=fetchData($str);
		$no=0;
		foreach($res as $key=>$val)
		{
			$optcreate = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['createdby']."'");
			$optupdate = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['updateby']."'");
			
			if($val['formaticon']=='folder')
			{
				$valplus="none";
				$strplus="select * from ".$dbname.".filemanager where induk='".$val['id']."' and formaticon!='folder' and status='1' ".$hideuser."";
				$resplus=fetchData($strplus);
				$countplus = count($resplus);
				if($countplus > 0)
				{
					$valplus = "block";
				}
				$img = "images/foldc_.png";
				if($val['id']=='1'){
					$img = "images/archive.png";
				}
				$tab.="<li class=liefil>
					<table>
						<tr>
							<td style='width:10px'>
								<img title=Expand id='liplus_".$val['id']."' src='images/plus.gif' style='display:".$valplus."'>
							</td>
							<td>
								<img title=Expand id='imgfolder_".$val['id']."' class='imgfolder_".$val['induk']."' class=arrow src='".$img."' height=15px style='cursor:normal'>
							</td>
							<td>
								<label onclick=\"openfolder('".$val['id']."','".$val['sourceid']."')\" onmousedown=\"rightclick('".$val['id']."','lblfolder_',event,'".$val['sourceid']."')\" class='linklabel' title=Expand id='lblfolder_".$val['id']."'>".$val['namafile']."</label><input type='text' id='lblfolder_x".$val['id']."' class='csttext' style='display:none'>
							</td>
						</tr>
					</table>
					<ul id='ullevel_".$val['id']."' class='ullevel_".$val['induk']."' style='display:none'>
					</ul>
				</li>";
				
				$tab2.="<tr class='rowcontent'>
					<td>
						<img title=Expand class=arrow src='".$img."' height=15px style='cursor:normal'> 
						<label onclick=\"openfolder('".$val['id']."','".$val['sourceid']."')\" onmousedown=\"rightclick('".$val['id']."','lblfolderright_',event,'".$val['sourceid']."')\" class='linklabel' title=Expand id='lblfolderright_".$val['id']."'>".$val['namafile']."</label><input type='text' id='lblfolderright_x".$val['id']."' class='csttext' style='display:none'>
					</td>
					<td>".dateFilemanager($val['updatetime'])."</td>
					<td>Folder file</td>
					<td style='text-align:right'>".($val['size'] == 0?"":number_format(($val['size']/1000),2)." Kb")."</td>
					<td>".$optcreate[$val['createdby']]."</td>
					<td>".$optcreate[$val['updateby']]."</td>
				</tr>";
			}
			else
			{
				$no++;
				$tab.="<li class=liefil>
					<table>
						<tr>
							<td style='width:10px'></td>
							<td style='min-width:25px;'>
									".$no.". 
								</td>
							<td>
								<img title='View' id='imgfile_".$val['id']."_".$val['induk']."_".$val['level']."' class='imgfile_".$val['induk']."_".$val['level']."' class=arrow src='".seticonfile($val['formaticon'])."' height=15px style='cursor:normal'>
							</td>
							<td>
								<label onclick=\"downloadfile('".$val['id']."')\" onmousedown=\"rightclick('".$val['id']."','lblfileleft_',event,'".$val['sourceid']."')\" class='linklabel' title=View id='lblfileleft_".$val['id']."'>".$val['namafile']."</label><input type='text' id='lblfileleft_x".$val['id']."' class='csttext' style='display:none'>
							</td>
						</tr>
					</table>
				</li>";
				
				$tab2.="<tr class='rowcontent'>
					<td>
						<table>
							<tr>
								<td style='min-width:25px;'>
									".$no.". 
								</td>
								<td>
									<img title=View class=arrow src='".seticonfile($val['formaticon'])."' height=15px style='cursor:normal'>
								</td>
								<td>
									<label onclick=\"downloadfile('".$val['id']."')\" onmousedown=\"rightclick('".$val['id']."','lblfileright_',event,'".$val['sourceid']."')\" class='linklabel' title=View id='lblfileright_".$val['id']."'>".$val['namafile']."</label><input type='text' id='lblfileright_x".$val['id']."' class='csttext' style='display:none'>
								</td>
							</tr>
						</table>
					</td>
					<td>".dateFilemanager($val['updatetime'])."</td>
					<td>".str_replace('.','',ucfirst($val['formaticon']))." file</td>
					<td style='text-align:right'>".($val['size'] == 0?"":number_format(($val['size']/1000),2)." Kb")."</td>
					<td>".$optcreate[$val['createdby']]."</td>
					<td>".$optcreate[$val['updateby']]."</td>
				</tr>";
			}
		}
		
		$strplus="select * from ".$dbname.".filemanager where induk='".$id."' and formaticon!='folder' and status='1' ".$hideuser."";
		$resplus=fetchData($strplus);
		$countplus = count($resplus);
		
		echo $induk."####".$tab."####".$tab2."####".previd($id)."####".getcurrentfolder($id)."####".$countplus;
	break;
	case 'searchdatax':
		$notranskasbank='';
		
		$strrplc1 = str_replace('-','',$periodex1);
		$strrplc2 = str_replace('-','',$periodex2);
		
		if($strrplc2 < $strrplc1){
			exit("Warning, Periode not valid");
		}

		$wheeres='';
		if($ptx!='')
		{
			$wheeres=" and namafile like '".$ptx."' and level=0 and induk=1";
			if($unitx!='')
			{
				$wheeresup=" and a.kodeorg = '".$unitx."'";
			}
			else
			{
				$strc="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)='4' and tipe<>'PT' and induk='".$ptx."'";
				$resc=fetchData($strc);
				$jox=0;
				foreach($resc as $keyc=>$valc)
				{
					if($jox==0)
					{

						$wheeresup=" and a.kodeorg in ('".$valc['kodeorganisasi']."'";
					}
					else
					{

						$wheeresup.=",'".$valc['kodeorganisasi']."'";
					}
					$jox++;
				}
				$wheeresup.=") ";
			}
		}
		else
		{
			$wheeres=" and namafile like '%%' and level=0 and induk=1";
			if($unitx!='')
			{
				$wheeresup=" and a.kodeorg = '".$unitx."'";
			}
			else
			{
				$strc="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)='4' and tipe<>'PT' ";
				$resc=fetchData($strc);
				$jox=0;
				foreach($resc as $keyc=>$valc)
				{
					if($jox==0)
					{

						$wheeresup=" and a.kodeorg in ('".$valc['kodeorganisasi']."'";
					}
					else
					{

						$wheeresup.=",'".$valc['kodeorganisasi']."'";
					}
					$jox++;
				}
				$wheeresup.=") ";
			}

		}

		if($namno!='')
		{
			$strc="select concat(a.namabank,'',b.noakun) as namabankx, b.noakun from ".$dbname.".keu_5daftarbank a 
			left join ".$dbname.".keu_5akunbank b on a.kodebank=b.namabank 
			where b.noakun <> '' and concat(a.namabank,'',b.noakun) like '%".$namno."%'";
			$resc=fetchData($strc);
			$jox=0;
			foreach($resc as $keyc=>$valc)
			{
				if($jox==0)
				{

					$wheeresrek=" and a.rekening in ('".$valc['noakun']."'";
				}
				else
				{

					$wheeresrek.=",'".$valc['noakun']."'";
				}
				$jox++;
			}

			$wheeresrek.=") ";
		}
		else
		{
			
			$wheeresrek=" and a.rekening like '%%' ";

		}
		if($supplierx!='')
		{
			$strsup="select a.notransaksi from ".$dbname.".keu_kasbankdtht_vw a 
			left join  ".$dbname.".keu_5akunbank b  on a.rekening=b.noakun where  a.keterangan1 like '%".$noinvoice."%' 
			and a.kodesupplier='".$supplierx."' and substr(a.tanggal,1,7)>='".$periodex1."' and substr(a.tanggal,1,7)<='".$periodex2."' ".$wheeresup." ".$wheeresrek." group by notransaksi ";
			$resup=fetchData($strsup);
			$nox=1;
			foreach($resup as $keysup=>$valsup)
			{
				if($nox==1)
				{
					$notranskasbank="'".$valsup['notransaksi']."'";
				}
				else
				{

					$notranskasbank.=",'".$valsup['notransaksi']."'";
				}
					$nox++;
			}
		}
		else
		{
			$strsup="select a.notransaksi from ".$dbname.".keu_kasbankdtht_vw a 
			left join  ".$dbname.".keu_5akunbank b  on a.rekening=b.noakun where  a.keterangan1 like '%".$noinvoice."%'  and substr(a.tanggal,1,7)>='".$periodex1."' and substr(a.tanggal,1,7)<='".$periodex2."' ".$wheeresup." ".$wheeresrek." group by notransaksi ";
			$resup=fetchData($strsup);
			$nox=1;
			foreach($resup as $keysup=>$valsup)
			{
				if($nox==1)
				{
					$notranskasbank="'".$valsup['notransaksi']."'";
				}
				else
				{

					$notranskasbank.=",'".$valsup['notransaksi']."'";
				}
					$nox++;
			}
		}

		if(intval(str_replace($periodex1, '-', '01'))>intval(str_replace($periodex2, '-', '01')))
		{
			exit("Warning : Periode 1 tidak boleh lebih besar dari periode 2");
		}

		
		$str="select * from ".$dbname.".filemanager where status='1' ".$wheeres." ".$hideuser." order by formaticon desc, namafile asc";
		//exit('Error'. $str);
		$res=fetchData($str);
		foreach($res as $key=>$val)
		{
			$wheeres='';
			if($unitx!='')
			{
				$wheeres=" and namafile = '".$unitx."' and level='1' and induk='".$val['id']."'";
			}
			else
			{
				$wheeres=" and namafile like '%%' and level='1' and induk='".$val['id']."'";
			}
			$str2="select * from ".$dbname.".filemanager where status='1' ".$wheeres." ".$hideuser." order by formaticon desc, namafile asc";
			$res2=fetchData($str2);
			foreach($res2 as $key2=>$val2)
			{
				$indkcol='';
				$strindk="select id from ".$dbname.".filemanager where induk='".$val2['id']."' ".$hideuser."";
				$resindk=fetchData($strindk);
				$nox=1;
				foreach($resindk as $keyindk=>$valindk)
				{
					if($nox==1)
					{
						$indkcol="'".$valindk['id']."'";
					}
					else
					{

						$indkcol.=",'".$valindk['id']."'";
					}
					$nox++;
				}

				$wheeres=" and  namafile >= '".$periodex1."' and namafile <= '".$periodex2."' and level='3' and induk in (".$indkcol.")";

				$str3="select * from ".$dbname.".filemanager where status='1' ".$wheeres." ".$hideuser." order by formaticon desc, namafile asc";
				$res3=fetchData($str3);
				foreach($res3 as $key3=>$val3)
				{
					$str4="select * from ".$dbname.".filemanager where status='1' and namafile in (".$notranskasbank.") and level in ('5','6') ".$hideuser." order by formaticon desc, namafile asc";
					$res4=fetchData($str4);
					foreach($res4 as $key4=>$val4)
					{
						$optcreate = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val4['createdby']."'");
						$optupdate = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val4['updateby']."'");
					//exit('Error : '.$str4);
						if($val4['formaticon']=='folder')
						{
							$valplus="none";
							$strplus="select * from ".$dbname.".filemanager where induk='".$val4['id']."' and formaticon!='folder' and status='1' ".$hideuser."";
							$resplus=fetchData($strplus);
							$countplus = count($resplus);
							if($countplus > 0)
							{
								$valplus = "block";
							}
							$img = "images/foldc_.png";
							if($val4['id']=='1'){
								$img = "images/archive.png";
							}
							$tab.="<li class=liefil>
								<table>
									<tr>
										<td style='width:10px'>
											<img title=Expand id='liplus_".$val4['id']."' src='images/plus.gif' style='display:".$valplus."'>
										</td>
										<td>
											<img title=Expand id='imgfolder_".$val4['id']."' class='imgfolder_".$val4['induk']."' class=arrow src='".$img."' height=15px style='cursor:normal'>
										</td>
										<td>
											<label onclick=\"openfolder('".$val4['id']."','".$val4['sourceid']."')\" onmousedown=\"rightclick('".$val4['id']."','lblfolder_',event,'".$val4['sourceid']."')\" class='linklabel' title=Expand id='lblfolder_".$val4['id']."'>".$val4['namafile']."</label><input type='text' id='lblfolder_x".$val4['id']."' class='csttext' style='display:none'>
										</td>
									</tr>
								</table>
								<ul id='ullevel_".$val4['id']."' class='ullevel_".$val4['induk']."' style='display:none'>
								</ul>
							</li>";
							
							$tab2.="<tr class='rowcontent'>
								<td>
									<img title=Expand class=arrow src='".$img."' height=15px style='cursor:normal'> 
									<label onclick=\"openfolder('".$val4['id']."','".$val4['sourceid']."')\" onmousedown=\"rightclick('".$val4['id']."','lblfolderright_',event,'".$val4['sourceid']."')\" class='linklabel' title=Expand id='lblfolderright_".$val4['id']."'>".$val4['namafile']."</label><input type='text' id='lblfolderright_x".$val4['id']."' class='csttext' style='display:none'>
								</td>
								<td>".dateFilemanager($val4['updatetime'])."</td>
								<td>Folder file</td>
								<td style='text-align:right'>".($val4['size'] == 0?"":number_format(($val4['size']/1000),2)." Kb")."</td>
								<td>".$optcreate[$val4['createdby']]."</td>
								<td>".$optcreate[$val4['updateby']]."</td>
							</tr>";
						}
						else
						{
							$no++;
							$tab.="<li class=liefil>
								<table>
									<tr>
										<td style='width:10px'></td>
										<td style='min-width:25px;'>
												".$no.". 
											</td>
										<td>
											<img title='View' id='imgfile_".$val4['id']."_".$val4['induk']."_".$val4['level']."' class='imgfile_".$val4['induk']."_".$val4['level']."' class=arrow src='".seticonfile($val4['formaticon'])."' height=15px style='cursor:normal'>
										</td>
										<td>
											<label onclick=\"downloadfile('".$val4['id']."')\" onmousedown=\"rightclick('".$val4['id']."','lblfileleft_',event,'".$val4['sourceid']."')\" class='linklabel' title=View id='lblfileleft_".$val4['id']."'>".$val4['namafile']."</label><input type='text' id='lblfileleft_x".$val4['id']."' class='csttext' style='display:none'>
										</td>
									</tr>
								</table>
							</li>";
							
							$tab2.="<tr class='rowcontent'>
								<td>
									<table>
										<tr>
											<td style='min-width:25px;'>
												".$no.". 
											</td>
											<td>
												<img title=View class=arrow src='".seticonfile($val4['formaticon'])."' height=15px style='cursor:normal'>
											</td>
											<td>
												<label onclick=\"downloadfile('".$val4['id']."')\" onmousedown=\"rightclick('".$val4['id']."','lblfileright_',event,'".$val4['sourceid']."')\" class='linklabel' title=View id='lblfileright_".$val4['id']."'>".$val4['namafile']."</label><input type='text' id='lblfileright_x".$val4['id']."' class='csttext' style='display:none'>
											</td>
										</tr>
									</table>
								</td>
								<td>".dateFilemanager($val4['updatetime'])."</td>
								<td>".str_replace('.','',ucfirst($val4['formaticon']))." file</td>
								<td style='text-align:right'>".($val4['size'] == 0?"":number_format(($val4['size']/1000),2)." Kb")."</td>
								<td>".$optcreate[$val4['createdby']]."</td>
								<td>".$optcreate[$val4['updateby']]."</td>
							</tr>";
						}
					}
				}
			}
		}

		echo $tab."####".$tab2;
		//echo $tab2;
	break;
	case'loadright':
		##GET UL
		$tab='';
		
		if($id!='0')
		{
			$tab.="<tr class='rowcontent' style='cursor:pointer' onclick=\"levelup()\">
				<td colspan=6>
					<img title=Expand class=arrow src='images/foldo1.png' height=15px style='cursor:normal'> 
					<label>..</label>
				</td>
			</tr>";
		}
		
		$str="select * from ".$dbname.".filemanager where induk='".$id."' and status='1' ".$hideuser." order by formaticon desc, namafile asc";
		$res=fetchData($str);
		foreach($res as $key=>$val)
		{
			$optcreate = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['createdby']."'");
			$optupdate = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['updateby']."'");
			if($val['formaticon']=='folder')
			{
				$img = "images/foldc_.png";
				if($val['id']=='1'){
					$img = "images/archive.png";
				}
				$tab.="<tr class='rowcontent'>
					<td>
						<img title=Expand class=arrow src='".$img."' height=15px style='cursor:normal'> 
						<label onclick=\"openfolder('".$val['id']."','".$val['sourceid']."')\" onmousedown=\"rightclick('".$val['id']."','lblfolderright_',event,'".$val['sourceid']."')\" class='linklabel' title=Expand id='lblfolderright_".$val['id']."'>".$val['namafile']."</label><input type='text' id='lblfolderright_x".$val['id']."' class='csttext' style='display:none'>
					</td>
					<td>".dateFilemanager($val['updatetime'])."</td>
					<td>Folder file</td>
					<td style='text-align:right'>".($val['size'] == 0?"":number_format(($val['size']/1000),2)." Kb")."</td>
					<td>".$optcreate[$val['createdby']]."</td>
					<td>".$optcreate[$val['updateby']]."</td>
				</tr>";
			}
			else
			{
				$tab.="<tr class='rowcontent'>
					<td>
						<img title=View class=arrow src='".seticonfile($val['formaticon'])."' height=15px style='cursor:normal'> 
						<label onclick=\"downloadfile('".$val['id']."')\" onmousedown=\"rightclick('".$val['id']."','lblfileright_',event,'".$val['sourceid']."')\" class='linklabel' title=View id='lblfileright_".$val['id']."'>".$val['namafile']."</label><input type='text' id='lblfileright_x".$val['id']."' class='csttext' style='display:none'></td>
					<td>".dateFilemanager($val['updatetime'])."</td>
					<td>".str_replace('.','',ucfirst($val['formaticon']))." file</td>
					<td style='text-align:right'>".($val['size'] == 0?"":number_format(($val['size']/1000),2)." Kb")."</td>
					<td>".$optcreate[$val['createdby']]."</td>
					<td>".$optcreate[$val['updateby']]."</td>
				</tr>";
			}
		}
		echo $tab;
	break;
	
	case'newfolder':
		$tab = "";
		
		$tab.="<table cellspacing=5>
			<tr>
				<td>Enter the name of the new folder</td>
				<td>:</td>
				<td>
					<input type=text id=foldername size=25 style=width:150px class=myinputtext>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button type='button' onclick=\"cancelpopup()\">&nbsp;Cancel&nbsp;</button>
					<button type='button' onclick=\"createfolder()\">&nbsp;Create Folder&nbsp;</button>
				</td>
			</tr>
		</table>";
		
		echo $tab;
	break;
	
	case'upload':
		$tab = "";
		
		$tab.="<table cellspacing=5>
			<tr>
				<td>Current folder</td>
				<td>:</td>
				<td>
					/<label id='uploadpath'></label>
				</td>
			</tr>
			<tr>
				<td>File Upload</td>
				<td>:</td>
				<td>
					<input type=file id=upload>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td></td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button type='button' onclick=\"cancelpopup()\">&nbsp;Cancel&nbsp;</button>
					<button type='button' onclick=\"uploadfile()\">&nbsp;Upload&nbsp;</button>
				</td>
			</tr>
		</table>";
		
		echo $tab;
	break;

	case'gantiunit':

	$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)='4' and tipe<>'PT' and induk='".$ptx."'";
	if($ptx==''){
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)='4' and tipe<>'PT'";
	}
	//exit('Error : '.$str);
	
		$res=fetchData($str);
		foreach($res as $key=>$val)
	{
			$optChange.="<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";
	}
	echo $optChange;
	break;
	case'opensearch':
		$tab = "";
		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where tipe='PT'";
		$res=fetchData($str);
		foreach($res as $key=>$val)
		{
			$optPT.="<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";
		}

		$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where LENGTH(kodeorganisasi)='4' and tipe<>'PT'";
		$res=fetchData($str);
		foreach($res as $key=>$val)
		{
			$optUnit.="<option value='".$val['kodeorganisasi']."'>".$val['namaorganisasi']."</option>";
		}

		$str="select a.kodesupplier,b.namasupplier from ".$dbname.".keu_kasbankdt a 
		left join ".$dbname.".log_5supplier b on a.kodesupplier=b.supplierid where a.kodesupplier<>'' and b.namasupplier<>'' group by a.kodesupplier order by a.kodesupplier";
		$res=fetchData($str);
		$optSupp="<option value=''>".$_SESSION['lang']['all']."</option>";
		foreach($res as $key=>$val)
		{
			$optSupp.="<option value='".$val['kodesupplier']."'>".$val['namasupplier']."</option>";
		}


		$str="select periode from ".$dbname.".setup_periodeakuntansi  group by periode order by periode";
		$res=fetchData($str);
		$optPeriode="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
		foreach($res as $key=>$val)
		{
			$optPeriode.="<option value='".$val['periode']."'>".$val['periode']."</option>";
		}

		$tab.="<table cellspacing=5>
			<tr>
				<td>Perusahaan</td>
				<td>:</td>
				<td>
					<select id='pt' onchange='gantiunit()'>".$optPT."</select>
					<img id='ptx' onclick=z.elSearch('pt',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				</td>
			</tr>
			<tr>
				<td>Unit</td>
				<td>:</td>
				<td>
					<select id='unit' >".$optUnit."</select>
					<img id='unitx' onclick=z.elSearch('unit',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				</td>
			</tr>


			<tr>
				<td>Supplier</td>
				<td>:</td>
				<td>
					<select id='sup' >".$optSupp."</select>
					<img id='supx' onclick=z.elSearch('sup',event) class='resicon' src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>
				</td>
			</tr>


			<tr>
				<td>Periode</td>
				<td>:</td>
				<td>
					<select id='periodex1' >".$optPeriode."</select>
				s/d
					<select id='periodex2' >".$optPeriode."</select>
				</td>
			</tr>


			<tr>
				<td>Nama/No.Rekening</td>
				<td>:</td>
				<td>
					<input type=text id=namno size=25 style=width:150px class=myinputtext>
				</td>
			</tr>

			<tr>
				<td>No.Voucher</td>
				<td>:</td>
				<td>
					<input type=text id=novo size=25 style=width:150px class=myinputtext>
				</td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td></td>
			</tr>
			<tr>
				<td colspan=2></td>
				<td>
					<button type='button' onclick=\"cancelpopup()\">&nbsp;Cancel&nbsp;</button>
					<button type='button' onclick=\"search()\">&nbsp;Search&nbsp;</button>
				</td>
			</tr>
		</table>";
		
		echo $tab;
	break;

	case'uploadfile':
		$tgl = date("YmdHis");
		$data = $_POST;
		
		if($data['fileupload']!='')
		{
			if($_FILES['file']['error']==0)
			{
				$filetype = strtolower('.'.substr($_FILES['file']['name'],strripos($_FILES['file']['name'],'.')+1));
				$newfilename = str_replace($filetype,'',$_FILES['file']['name']);
				$filename = $newfilename."_".$tgl."".$filetype;
				$file_tmpname = $_FILES['file']['tmp_name'];		
				
				if(($filetype=='.jpeg')||($filetype=='.jpg')||($filetype=='.png')||($filetype=='.pdf')||($filetype=='.xls')||($filetype=='.xlsx')||($filetype=='.doc')||($filetype=='.docx'))
				{
					if($_FILES['file']['size'] <= 250000)
					{
						$filesize = $_FILES['file']['size'];
						if($id=='')
						{
							$level = 0;
						}
						else
						{
							$optLevel = makeOption($dbname,'filemanager','id,level',"id='".$id."'");
							$level = ($optLevel[$id])+1;
						}
						$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,size,status,createdby,createdtime,updateby,updatetime) values('".$id."','".$level."','".$filename."','".$filetype."','".$filesize."','1','".$createdby."','".$createdtime."','".$updateby."','".$updatetime."')";
						try
						{
							$owlPDO->exec($str);
							move_uploaded_file($file_tmpname,$path."".$uploadpath."".$filename);
						}
						catch(PDOException $e)
						{
							echo " Gagal," . addslashes($e->getMessage());
						}
					}
					else
					{
						exit("warning : Ukuran file upload maksimal 250kb");
					}
				}else{
					exit("Warning : Format file upload harus .jpg atau .jpeg");
				}
			}
		}
		
		echo getinduk($id);
	break;
	
	case'renamefile':
		$str="select * from ".$dbname.".filemanager where id='".$id."' and status='1' ".$hideuser."";
		$res=fetchData($str);
		$formaticon = $res[0]['formaticon'];
		$induk = $res[0]['induk'];
		
		$str="select namafile from ".$dbname.".filemanager where induk='".$induk."' and LOWER(namafile)!='".strtolower($oldname)."' and formaticon='".$formaticon."' and status='1' ".$hideuser."";
		$res=fetchData($str);
		foreach($res as $key=>$val)
		{
			if(strtolower($val['namafile'])==strtolower($newname))
			{
				if($formaticon=='folder')
				{
					exit("Error : There is already a folder with same name '".$newname."' in this location.");
				}
				else
				{
					exit("Error : There is already a file with same name '".$newname."' in this location");
				}
			}
		}
		
		$pathold = setlocationfile($id);
		$exppathold = explode('/',$pathold);
		$temppathnew = "";
		$no=0;
		foreach($exppathold as $key)
		{
			$no++;
			if($no!=count($exppathold))
			{
				$temppathnew .= $key."/";
			}
		}
		$pathnew = $temppathnew."".$newname;
		// exit("error : ".$pathnew);	
		if($formaticon=='folder')
		{
			$valname = $newname;
		}
		else
		{
			$valname = $newname."".$formaticon;
			$pathnew = $temppathnew."".$valname;
		}
		
		$str="update ".$dbname.".filemanager set namafile='".$valname."', updateby='".$updateby."', updatetime='".$updatetime."' where id='".$id."'";
		try
		{
			$owlPDO->exec($str);
			
			rename($pathold,$pathnew);
		}
		catch(PDOException $e)
		{
			print $e->getMessage();die();exit(0);
		}
		echo $induk;
	break;
	
	case'createfolder':
		$induk='0';
		$formaticon='folder';
		if($lbldatapath=='')
		{
			$induk = '0';
			$structure = $path."".$foldername;
		}
		else
		{
			$optLevel = makeOption($dbname,'filemanager','id,level',"id='".$id."' ".$hideuser."");
			$level = ($optLevel[$id])+1;
			$induk = $id;
			$structure = $path."".$lbldatapath."".$foldername;
		}
		
		$str="select namafile from ".$dbname.".filemanager where induk='".$induk."' and formaticon='folder'  and status='1' ".$hideuser."";
		$res=fetchData($str);
		foreach($res as $key=>$val)
		{
			if(strtolower($val['namafile'])==strtolower($foldername))
			{
				exit("Error : This destination already contains a folder name '".$foldername."'");
			}
		}
		
		if (!mkdir($structure, 0777, true)) 
		{
			exit("Error : Failed to create folders...");
		}
		
		$str="insert into ".$dbname.".filemanager (induk,level,namafile,formaticon,status,createdby,createdtime,updateby,updatetime) values('".$induk."','".$level."','".$foldername."','".$formaticon."','1','".$createdby."','".$createdtime."','".$updateby."','".$updatetime."')";
		try
		{
			$owlPDO->exec($str);
		}
		catch(PDOException $e)
		{
			print $e->getMessage();die();exit(0);
		}
	break;	
	
	case'loadall':
		echo loadall($id,$induk,$level);
	break;
	
	case'downloadfile':
		echo setlocationfile($id);
	break;
	
	case'deletefile':
		$induk = getinduk($id);
		$str="select namafile from ".$dbname.".filemanager where id='".$id."' and status='1' ".$hideuser."";
		$res=fetchData($str);
		$namafile = $res[0]['namafile'];
		
		$str="update ".$dbname.".filemanager set status = '0',namafile='".$namafile."(deleted)' where id='".$id."'";
		try
		{
			$owlPDO->exec($str);
			
			$pathold = setlocationfile($id);
			$exppathold = explode('/',$pathold);
			$temppathnew = "";
			$no=0;
			foreach($exppathold as $key)
			{
				$no++;
				if($no!=count($exppathold))
				{
					$temppathnew .= $key."/";
				}
			}
			$pathnew = $temppathnew."".$namafile."(deleted)";
			rename($pathold."".$namafile,$pathnew);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
		echo $induk;
	break;
	
	case'deletefilerb':
		$str="select * from ".$dbname.".filemanager where id='".$id."'";
		$res=fetchData($str);
		$namafile = $res[0]['namafile'];
		$str="delete from ".$dbname.".filemanager where id='".$id."'";
		try
		{
			$owlPDO->exec($str);
			delete_directory($namafile);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
		echo $induk;
	break;
	
	case'restorefile':
		$induk = getinduk($id);
		$str="select namafile from ".$dbname.".filemanager where id='".$id."'";
		$res=fetchData($str);
		$namafile = str_replace('(deleted)','',$res[0]['namafile']);
		
		$str="select * from ".$dbname.".filemanager where induk='".$induk."' and lower(namafile)='".strtolower($namafile)."' and status='1'";
		$res=fetchData($str);
		$countfile = count($res);
		
		if($countfile>0){
			exit("Error : There is already a folder with same name '".$namafile."' in this location.");
		}
		
		$str="update ".$dbname.".filemanager set status = '1',namafile='".$namafile."' where id='".$id."'";
		try
		{
			$owlPDO->exec($str);
			
			$pathold = setlocationfile($id);
			$exppathold = explode('/',$pathold);
			$temppathnew = "";
			$no=0;
			foreach($exppathold as $key)
			{
				$no++;
				if($no!=count($exppathold))
				{
					$temppathnew .= $key."/";
				}
			}
			$pathnew = $temppathnew."".$namafile;
			rename($pathold."".$namafile,$pathnew);
		}
		catch(PDOException $e)
		{
			echo " Gagal," . addslashes($e->getMessage());
		}
	break;
	
	case'loadrightrb':
		$tab='';
		
		$str="select * from ".$dbname.".filemanager where status='0' and sourceid='0' ".$hideuser." order by formaticon desc";
		$res=fetchData($str);
		foreach($res as $key=>$val)
		{
			$optcreate = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['createdby']."'");
			$optupdate = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['updateby']."'");
			if($val['formaticon']=='folder')
			{
				$tab.="<tr class='rowcontent'>
					<td>
						<img title=Expand class=arrow src='images/foldc_.png' height=15px style='cursor:normal'> 
						<label onmousedown=\"rightclick('".$val['id']."','lblfolderright_',event,'rb')\" class='linklabel' title=Expand id='lblfolderright_".$val['id']."'>".str_replace('(deleted)','',$val['namafile'])."</label><input type='text' id='lblfolderright_x".$val['id']."' class='csttext' style='display:none'>
					</td>
					<td>".str_replace($val['namafile'].'/','',getcurrentfolder($val['id']))."</td>
					<td>".dateFilemanager($val['updatetime'])."</td>
					<td style='text-align:right'>".($val['size'] == 0?"":number_format(($val['size']/1000),2)." Kb")."</td>
					<td>Folder file</td>
					<td>".dateFilemanager($val['updatetime'])."</td>
				</tr>";
			}
			else
			{
				$tab.="<tr class='rowcontent'>
					<td>
						<img title=View class=arrow src='".seticonfile($val['formaticon'])."' height=15px style='cursor:normal'> 
						<label onmousedown=\"rightclick('".$val['id']."','lblfileright_',event,'rb')\" class='linklabel' title=View id='lblfileright_".$val['id']."'>".str_replace('(deleted)','',$val['namafile'])."</label><input type='text' id='lblfileright_x".$val['id']."' class='csttext' style='display:none'></td>
					<td>".str_replace($val['namafile'].'/','',getcurrentfolder($val['id']))."</td>
					<td>".dateFilemanager($val['updatetime'])."</td>
					<td style='text-align:right'>".($val['size'] == 0?"":number_format(($val['size']/1000),2)." Kb")."</td>
					<td>".str_replace('.','',ucfirst($val['formaticon']))." file</td>
					<td>".dateFilemanager($val['updatetime'])."</td>
				</tr>";
			}
		}
		echo $tab;
	break;
	
	case'opendc':
		##GET INDUK
		$str="select * from ".$dbname.".filemanager where id='".$id."' and status='1' and sourceid!='0' ".$hideuser."";
		$res=fetchData($str);
		$induk = $res[0]['induk'];
		$tab=$tab2="";
		
		if($induk!='')
		{
			$tab2.="<tr class='rowcontent' style='cursor:pointer' onclick=\"levelup()\">
				<td colspan=6>
					<img title=Expand class=arrow src='images/foldo1.png' height=15px style='cursor:normal'> 
					<label>..</label>
				</td>
			</tr>";
		}
		
		##GET UL
		$str="select * from ".$dbname.".filemanager where induk='".$id."' and status='1' and sourceid!='0' ".$hideuser." order by formaticon desc";
		$res=fetchData($str);
		foreach($res as $key=>$val)
		{
			$optcreate = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['createdby']."'");
			$optupdate = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['updateby']."'");
			
			if($val['formaticon']=='folder')
			{
				$valplus="none";
				$strplus="select * from ".$dbname.".filemanager where induk='".$val['id']."' and formaticon!='folder' and status='1' ".$hideuser."";
				$resplus=fetchData($strplus);
				$countplus = count($resplus);
				if($countplus > 0)
				{
					$valplus = "block";
				}
				$tab.="<li class=liefil>
					<table>
						<tr>
							<td style='width:10px'>
								<img title=Expand id='liplus_".$val['id']."' src='images/plus.gif' style='display:".$valplus."'>
							</td>
							<td>
								<img title=Expand id='imgfolder_".$val['id']."' class='imgfolder_".$val['induk']."' class=arrow src='images/foldc_.png' height=15px style='cursor:normal'>
							</td>
							<td>
								<label onclick=\"opendc('".$val['id']."')\" onmousedown=\"rightclick('".$val['id']."','lblfolder_',event)\" class='linklabel' title=Expand id='lblfolder_".$val['id']."'>".$val['namafile']."</label><input type='text' id='lblfolder_x".$val['id']."' class='csttext' style='display:none'>
							</td>
						</tr>
					</table>
					<ul id='ullevel_".$val['id']."' class='ullevel_".$val['induk']."' style='display:none'>
					</ul>
				</li>";
				
				$tab2.="<tr class='rowcontent'>
					<td>
						<img title=Expand class=arrow src='images/foldc_.png' height=15px style='cursor:normal'> 
						<label onclick=\"opendc('".$val['id']."')\" onmousedown=\"rightclick('".$val['id']."','lblfolderright_',event)\" class='linklabel' title=Expand id='lblfolderright_".$val['id']."'>".$val['namafile']."</label><input type='text' id='lblfolderright_x".$val['id']."' class='csttext' style='display:none'>
					</td>
					<td>".dateFilemanager($val['updatetime'])."</td>
					<td>Folder file</td>
					<td style='text-align:right'>".($val['size'] == 0?"":number_format(($val['size']/1000),2)." Kb")."</td>
					<td>".$optcreate[$val['createdby']]."</td>
					<td>".$optcreate[$val['updateby']]."</td>
				</tr>";
			}
			else
			{
				$tab.="<li class=liefil>
					<table>
						<tr>
							<td style='width:10px'></td>
							<td>
								<img title='View' id='imgfile_".$val['id']."_".$val['induk']."_".$val['level']."' class='imgfile_".$val['induk']."_".$val['level']."' class=arrow src='".seticonfile($val['formaticon'])."' height=15px style='cursor:normal'>
							</td>
							<td>
								<label onclick=\"downloadfile('".$val['id']."')\" onmousedown=\"rightclick('".$val['id']."','lblfileleft_',event)\" class='linklabel' title=View id='lblfileleft_".$val['id']."'>".$val['namafile']."</label><input type='text' id='lblfileleft_x".$val['id']."' class='csttext' style='display:none'>
							</td>
						</tr>
					</table>
				</li>";
				
				$tab2.="<tr class='rowcontent'>
					<td>
						<img title=View class=arrow src='".seticonfile($val['formaticon'])."' height=15px style='cursor:normal'> 
						<label onclick=\"downloadfile('".$val['id']."')\" onmousedown=\"rightclick('".$val['id']."','lblfileright_',event)\" class='linklabel' title=View id='lblfileright_".$val['id']."'>".$val['namafile']."</label><input type='text' id='lblfileright_x".$val['id']."' class='csttext' style='display:none'></td>
					<td>".dateFilemanager($val['updatetime'])."</td>
					<td>".str_replace('.','',ucfirst($val['formaticon']))." file</td>
					<td style='text-align:right'>".($val['size'] == 0?"":number_format(($val['size']/1000),2)." Kb")."</td>
					<td>".$optcreate[$val['createdby']]."</td>
					<td>".$optcreate[$val['updateby']]."</td>
				</tr>";
			}
		}
		
		$strplus="select * from ".$dbname.".filemanager where induk='".$id."' and formaticon!='folder' and status='1' ".$hideuser."";
		$resplus=fetchData($strplus);
		$countplus = count($resplus);
		
		echo $induk."####".$tab."####".$tab2."####".previd($id)."####".getcurrentfolder($id)."####".$countplus;
	break;
	
	case'loadrightdc':
		##GET UL
		$tab='';
		
		if($id!='0')
		{
			$tab.="<tr class='rowcontent' style='cursor:pointer' onclick=\"levelup()\">
				<td colspan=6>
					<img title=Expand class=arrow src='images/foldo1.png' height=15px style='cursor:normal'> 
					<label>..</label>
				</td>
			</tr>";
		}
		
		$str="select * from ".$dbname.".filemanager where status='1' and (sourceid!='0' and sourceid!='x') and level='0' ".$hideuser." order by formaticon desc";
		$res=fetchData($str);
		foreach($res as $key=>$val)
		{
			$optcreate = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['createdby']."'");
			$optupdate = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['updateby']."'");
			if($val['formaticon']=='folder')
			{
				$tab.="<tr class='rowcontent'>
					<td>
						<img title=Expand class=arrow src='images/foldc_.png' height=15px style='cursor:normal'> 
						<label onclick=\"openfolder('".$val['id']."','".$val['sourceid']."')\" onmousedown=\"rightclick('".$val['id']."','lblfolderright_',event)\" class='linklabel' title=Expand id='lblfolderright_".$val['id']."'>".$val['namafile']."</label><input type='text' id='lblfolderright_x".$val['id']."' class='csttext' style='display:none'>
					</td>
					<td>".dateFilemanager($val['updatetime'])."</td>
					<td>Folder file</td>
					<td style='text-align:right'>".($val['size'] == 0?"":number_format(($val['size']/1000),2)." Kb")."</td>
					<td>".$optcreate[$val['createdby']]."</td>
					<td>".$optcreate[$val['updateby']]."</td>
				</tr>";
			}
			else
			{
				$tab.="<tr class='rowcontent'>
					<td>
						<img title=View class=arrow src='".seticonfile($val['formaticon'])."' height=15px style='cursor:normal'> 
						<label onclick=\"downloadfile('".$val['id']."')\" onmousedown=\"rightclick('".$val['id']."','lblfileright_',event)\" class='linklabel' title=View id='lblfileright_".$val['id']."'>".$val['namafile']."</label><input type='text' id='lblfileright_x".$val['id']."' class='csttext' style='display:none'></td>
					<td>".dateFilemanager($val['updatetime'])."</td>
					<td>".str_replace('.','',ucfirst($val['formaticon']))." file</td>
					<td style='text-align:right'>".($val['size'] == 0?"":number_format(($val['size']/1000),2)." Kb")."</td>
					<td>".$optcreate[$val['createdby']]."</td>
					<td>".$optcreate[$val['updateby']]."</td>
				</tr>";
			}
		}
		echo $tab;
	break;
}   

################
### FUNCTION ###
################
function delete_directory($dirname) 
{
	global $path;
	
	$dirname = $path."".$dirname;
	
	if (is_dir($dirname))
		$dir_handle = opendir($dirname);
	
	if (!$dir_handle)
		return false;
	
	while($file = readdir($dir_handle)) 
	{
		if ($file != "." && $file != "..") 
		{
			if (!is_dir($dirname."/".$file))
				unlink($dirname."/".$file);
			else
				delete_directory($dirname.'/'.$file);
	       }
	 }
	 closedir($dir_handle);
	 rmdir($dirname);
	 return true;
}

function getinduk($id)
{
	global $dbname;
	global $owlPDO;
	
	$optInduk = makeOption($dbname,'filemanager','id,induk',"id='".$id."'");
	
	return $optInduk[$id];
}

function previd($id)
{
	global $dbname;
	global $owlPDO;
	
	$val=0;
	
	$str="select * from ".$dbname.".filemanager where id='".$id."' and status='1'";
	$res=fetchData($str);
	$val = $res[0]['induk'];
	
	if($val=='')
	{
		$val = '';
	}
	
	return $val;
}

function loadall($id,$induk,$level)
{
	global $dbname;
	global $owlPDO;
	global $hideuser;
	
	$tab=$tab2=$where="";
	if($id==''){$where=" and induk='0'";}else{$where=" and id='".$id."'";}
	$str="select * from ".$dbname.".filemanager where induk='".$induk."' and level='".$level."' and status='1' ".$hideuser." order by formaticon desc";
	$res=fetchData($str);
	foreach($res as $key=>$val)
	{
		$optcreate = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['createdby']."'");
		$optupdate = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$val['updateby']."'");
		if($val['formaticon']=='folder')
		{
			$valplus="none";
			$strplus="select * from ".$dbname.".filemanager where induk='".$val['id']."' and formaticon!='folder' and status='1' ".$hideuser."";
			$resplus=fetchData($strplus);
			$countplus = count($resplus);
			if($countplus > 0)
			{
				$valplus = "block";
			}
			
			$img = "images/foldc_.png";
			if($val['id']=='1'){
				$img = "images/archive.png";
			}
			
			$tab.="<li class='liefil'>
				<table>
					<tr>
						<td style='width:10px'>
							<img title=Expand id='liplus_".$val['id']."' src='images/plus.gif' style='display:".$valplus."'>
						</td>
						<td>
							<img title=Expand id='imgfolder_".$val['id']."' class='imgfolder_".$val['induk']."' class=arrow src='".$img."' height=15px style='cursor:normal'>
						</td>
						<td>
							<label onclick=\"openfolder('".$val['id']."','".$val['sourceid']."')\" onmousedown=\"rightclick('".$val['id']."','lblfolder_',event,'".$val['sourceid']."')\" class='linklabel' title=Expand id='lblfolder_".$val['id']."'>".$val['namafile']."</label><input type='text' id='lblfolder_x".$val['id']."' class='csttext' style='display:none'>
						</td>
					</tr>
				</table>
					
				<ul id='ullevel_".$val['id']."' class='ullevel_".$val['induk']."' style='display:none'>
				</ul>
			</li>";
			
			$tab2.="<tr class='rowcontent'>
				<td>
					<img title=Expand class=arrow src='".$img."' height=15px style='cursor:normal'> 
					<label onclick=\"openfolder('".$val['id']."','".$val['sourceid']."')\" onmousedown=\"rightclick('".$val['id']."','lblfolderright_',event,'".$val['sourceid']."')\" class='linklabel' title=Expand id='lblfolderright_".$val['id']."'>".$val['namafile']."</label><input type='text' id='lblfolderright_x".$val['id']."' class='csttext' style='display:none'>
				</td>
				<td>".dateFilemanager($val['updatetime'])."</td>
				<td>Folder file</td>
				<td style='text-align:right'>".($val['size'] == 0?"":number_format(($val['size']/1000),2)." Kb")."</td>
				<td>".$optcreate[$val['createdby']]."</td>
				<td>".$optcreate[$val['updateby']]."</td>
			</tr>";
		}
		else
		{
			$tab.="<li class='liefil'>
				<table>
					<tr>
						<td style='width:10px'></td>
						<td>
							<img title='View' id='imgfile_".$val['id']."_".$val['induk']."_".$val['level']."' class='imgfile_".$val['induk']."_".$val['level']."' class=arrow src='".seticonfile($val['formaticon'])."' height=15px style='cursor:normal'>
						</td>
						<td>
							<label onclick=\"downloadfile('".$val['id']."')\" onmousedown=\"rightclick('".$val['id']."','lblfileleft_',event,'".$val['sourceid']."')\" class='linklabel' title=View id='lblfileleft_".$val['id']."'>".$val['namafile']."</label><input type='text' id='lblfileleft_x".$val['id']."' class='csttext' style='display:none'>
						</td>
					</tr>
				</table>
			</li>";
			
			$tab2.="<tr class='rowcontent'>
				<td>
					<img title=View class=arrow src='".seticonfile($val['formaticon'])."' height=15px style='cursor:normal'> 
					<label onclick=\"downloadfile('".$val['id']."')\" onmousedown=\"rightclick('".$val['id']."','lblfileright_',event,'".$val['sourceid']."')\" class='linklabel' title=View id='lblfileright_".$val['id']."'>".$val['namafile']."</label><input type='text' id='lblfileright_x".$val['id']."' class='csttext' style='display:none'></td>
				<td>".dateFilemanager($val['updatetime'])."</td>
				<td>".str_replace('.','',ucfirst($val['formaticon']))." file</td>
				<td style='text-align:right'>".($val['size'] == 0?"":number_format(($val['size']/1000),2)." Kb")."</td>
				<td>".$optcreate[$val['createdby']]."</td>
				<td>".$optcreate[$val['updateby']]."</td>
			</tr>";
		}
	}
	
	return $tab."####".$tab2;
}

function getcurrentfolder($id)
{
	global $dbname;
	global $owlPDO;
	
	$val = "";
	$tempval = "";
	$curid = $id;
	
	$level = makeOption($dbname,'filemanager','id,level',"id='".$id."'");
	for($i=0;$i<=$level[$id];$i++)
	{
		$str="select * from ".$dbname.".filemanager where id='".$curid."'";
		$res=fetchData($str);
		$val=$res[0]['namafile']."/".$tempval;
		$tempval=$val;
		$curid = $res[0]['induk'];
	}
	
	return $val;
}
?>
