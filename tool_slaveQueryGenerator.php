<?
ini_set('display_errors',0);
error_reporting(0);

require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/admin_validation.php');
$param=$_POST;
switch($param['action']){
	case'getField':
	#default field result from show columns:
	#| Field      | Type     | Null | Key | Default | Extra          |
	$row=substr($param['targetid'],-1);
			if($param['tablename']!=''){
				$str="show columns from ".$dbname.".".$param['tablename'];
				$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
				$res->setFetchMode(PDO::FETCH_ASSOC);
				$x=0;
				while ($bar = $res->fetch()) {
				  $x++;
				  $ret.="<span id=".$param['tablename'].$bar['Field']." class=myButton".$row." draggable='true' ondragstart='drag(event)' >".$param['tablename'].".".$bar['Field']."</span>";
				  if($x%3==0){
				  $ret.="<br/><br/>";
				  }
				}
			}else{
			 $ret='';
			}
			echo $ret;
	break;

	case "preview":
		$tableList=$param['table'];
		$join=$param['join'];
		$judul=$param['judul'];
		$kolomTampil=explode(",",$param['kolomTampil']);
		$parameter=str_replace("\\","",$param['parameter']);
		$parameter=str_replace("::persen::","%",$parameter);
		if($parameter!=''){
			$parameter=" where ".$parameter;
		}
		$kolomSelect=explode(",",$param['kolomSelect']);
		$group=explode(",",$param['grouping']);
		$subtotal=explode(",",$param['subtotal']);
		$order=explode(",",$param['order']);
		//prepare select
		$select='';
		$grouped='';
		$ordered='';
		$defGroup=Array();
		for($x=0;$x<count($kolomTampil);$x++){
			if($x==0){
				if($group[$x]!='0'){
					$select.=" ".$group[$x]."(".$kolomSelect[$x].") as opr".$x;
					array_push($defGroup,$kolomSelect[$x]);
				}else{
					$select.=$kolomSelect[$x];
					if($grouped==''){
						$grouped.=$kolomSelect[$x];
					}else{
						$grouped.=",".$kolomSelect[$x];
					}					
				}
			}else{
				if($group[$x]!='0'){
					$select.=", ".$group[$x]."(".$kolomSelect[$x].") as opr".$x;
					array_push($defGroup,$kolomSelect[$x]);
				}else{
					$select.=",".$kolomSelect[$x];
					if($grouped==''){
						$grouped.=$kolomSelect[$x];
					}else{
						$grouped.=",".$kolomSelect[$x];
					}					
				}				
			}
			if($order[$x]=='1'){
				if($ordered==''){
					$ordered.=$kolomSelect[$x];
				}else{
					$ordered.=",".$kolomSelect[$x];
				}
			}
		}
		//validation
		
		if($grouped!=''){
			$grouped=" group by ".$grouped;
		}
		if(count($defGroup)==0){
			$grouped='';
		}
		if($ordered!=''){
			$ordered=" order by ".$ordered;
		}
		//prepare table
		$table=explode(",",$tableList);
        $mainTable=$table[0];
		array_shift($table);
		$join=explode(",",$join);
		if(count($table)>0){//means join
			foreach($table as $key=>$val){
				$joinTabel.=" left join ". $dbname.".".$val;
				$sw=0;
				foreach($join as $k1=>$v1){
					$tabname=explode("=",$v1);
					$tralala=explode(".",$tabname[1]);
					if($val==$tralala[0]){
						if($sw==0){
							$joinTabel.=" on ".$v1;
							$sw++;
						}else{
							$joinTabel.=" and ".$v1;
						}
					}	
				}
			}
			//$joinTabel=implode(",",$table);
			$query="select ".$select." from ".$dbname.".".$mainTable." ".$joinTabel."
			        ".$parameter." ".$grouped." ".$ordered." limit 100";
		}else{//means query on single table
			$query="select ".$select." from ".$dbname.".".$mainTable." 
			        ".$parameter." ".$grouped." ".$ordered." limit 100";		
		}
		
		if($param['jenis']=='save'){
			if(count($table)>0){
				$ins="select ".$select." from ".$dbname.".".$mainTable." ".$joinTabel." #PARAMETER# ".$grouped." ".$ordered;
			}else{
				$ins="select ".$select." from ".$dbname.".".$mainTable." #PARAMETER# ".$grouped." ".$ordered;
			}
			#ambil nomor terakhir:
			$str="select max(rnumber) as last from ".$dbname.".tool_userdefinedreport";
			$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
			$res->setFetchMode(PDO::FETCH_OBJ);
			$num=0;
			while ($bar = $res->fetch()) {
				$num=$bar->last;
			}
			$num++;			
			$err='';
			$str="insert into ".$dbname.".tool_userdefinedreport(
					rnumber,
					namalaporan,
					query,
					html,
					pdf,
					speadsheet,
					createdate,
					owner,
					status,
					kolomtampil,
					subtotal,
					`group`) values(".
					$num.",
					'".$param['judul']."',
					'".$ins."',
					1,0,0,
					'".date('Y-m-d')."',
					'".$_SESSION['standard']['username']."',
					0,'".$param['kolomTampil']."',
					'".$param['subtotal']."',
					'".$param['grouping']."')";
			try{
			   $owlPDO->exec($str); //insert hedaer	
			}catch (PDOException $e){
				$err=" Error insert header: ";
			}	
			if($err=='' && $param['parameter']!=''){
			#extract parameter:
				$para=explode(",",$param['parameter']);
				foreach($para as $kunc=>$valP){
					$parNo=explode("##",$valP);
					$str2="insert into ".$dbname.".tool_userdefinedreport_par(rnumber, kolom, value,operator)
						values(".$num.",'".$parNo[0]."','".$parNo[2]."','".$parNo[1]."')";
					try{
					   $owlPDO->exec($str2); //insert hedaer	
					}catch (PDOException $e){
						$err=" Error insert detail: ";
						$stRol="delete from ".$dbname.".tool_userdefinedreport where rnumber=".$num;
						$owlPDO->exec($stRol);
					}					
				}
			}else{
				echo $err;
			}
			if($err==''){
				echo "Done";
			}
		#must exit here;==================	
			exit(); #beibeh
		#=================================
		}		
		$res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_NUM);
		$tab="<fieldset><table class=sortable cellspacing=1 cellpadding=5 border=0>
		      <thead>
			  <tr class=rowheader><td>No</td>";
			  foreach($kolomTampil as $key=>$vall){
				$tab.="<td>".$vall."</td>";
			  }
		$tab.="</tr></thead><tbody>";
		$total=Array();
		$subVal=Array();
		$prevVal=Array();
		$printSubtotal=Array();
		$avg=Array();
		$totalAvg=Array();
		$no=0;
		while ($bar = $res->fetch()) {
			$no++;
			//get Subtotal
			foreach($bar as $key1=>$val){
				if($subtotal[$key1]=='1'){
					if($prevVal[$key1]!=$bar[$key1] && $no!=0){
						$printSubtotal=$subVal;
						$subVal=Array();
						$subAvg=$avg;
						$avg=Array();
					}
					foreach($group as $kk=>$kval){
					  if($kval!='0'){
						if($kval=='sum'){
							$subVal[$kk]+=$bar[$kk];
						}
						 else{
							$avg[]=$bar[$kk];	
						 }
					  }
					}
				}
			}
			//print Subtotal
			if(count($printSubtotal)>0){
				
				$tab.="<tr class=rowcontent><td></td>";
				foreach($kolomTampil as $key1=>$val){
					if($key1=='0'){
						$tab.="<td><b>Subtotal</b></td>";
					}else{
						if($group[$key1]=='sum'){
							if($printSubtotal[$key1]!=''){							
								$tab.="<td align=right><b>".number_format($printSubtotal[$key1],2)."</b></td>";
								$total[$key1]+=$printSubtotal[$key1];
							}else{
								$tab.="<td align=right></b></td>";
								$total[$key1]+='0';
							}  
						}else if($group[$key1]=='avg'){
							$tab.="<td align=right><b>".number_format(array_sum($subAvg)/count($subAvg),2)."</b></td>";
							$totalAvg[0]+=array_sum($subAvg);
							$totalAvg[1]+=count($subAvg);
						}else{
							$tab.="<td align=right></b></td>";
							$total[$key1]+='0';
						}
					}
				}
				$tab.="</tr>";
				$printSubtotal=Array();
			}
			
			//print regular row
			$tab.="<tr class=rowcontent><td>".$no."</td>";
			foreach($kolomTampil as $key1=>$val){
				if($group[$key1]!='0' && isset($group[$key1]) && $group[$key1]!=''){
					$tab.="<td align=right>".number_format($bar[$key1],2)."</td>";
				}else{
					$tab.="<td>".$bar[$key1]."</td>";
				}
				$prevVal[$key1]=$bar[$key1];
			}
			$tab.="</tr>";
		}
			//print last Subtotal
			if(count($subVal)>0){
				$tab.="<tr class=rowcontent><td></td>";
				foreach($group as $key1=>$val){
					if($key1=='0'){
						$tab.="<td><b>Subtotal</b></td>";
					}else{
						if($val=='sum'){  
							if($subVal[$key1]!=''){							
								$tab.="<td align=right><b>".number_format($subVal[$key1],2)."</b></td>";
								$total[$key1]+=$subVal[$key1];
							}else{
								$tab.="<td align=right></b></td>";
								$total[$key1]+='0';
							}
						}else if($val=='avg'){
								$tab.="<td align=right><b>".number_format(array_sum($avg)/count($avg),2)."</b></td>";
								$totalAvg[0]+=array_sum($avg);
								$totalAvg[1]+=count($avg);
							
						}else{
							$tab.="<td align=right></b></td>";
							$total[$key1]+='0';
						}
					}
				}
				$tab.="</tr>";
				//print total
				$tab.="<tr class=rowcontent><td></td>";
				foreach($kolomTampil as $key1=>$val){
					if($key1=='0'){
						$tab.="<td><b>Grand Total</b></td>";
					}else{
							if($group[$key1]=='sum'){				
								if($total[$key1]!=''){
									$tab.="<td align=right><b>".number_format($total[$key1],2)."</b></td>";
								}else{
									$tab.="<td align=right></b></td>";
								}
							}else if($group[$key1]=='avg'){
								$tab.="<td align=right><b>".number_format(($totalAvg[0]/$totalAvg[1]),2)."</b></td>";
							}else{
							$tab.="<td align=right></b></td>";
							}
						}
				}
			}

		$tab.="</tr>";		
		
		$tab.="</tbody></table></fieldset>";
		echo $tab;
	break;
    
	case 'updateTable':
		$str="update ".$dbname.".tool_userdefinedreport set ".$param['column']."=".$param['status']." where rnumber=".$param['rnumber'];
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
				echo "Gagal : ".$e->getMessage();
			}
	break;
	case 'getUser':
		#get Judul
		$judul='';
		$str="select namalaporan from ".$dbname.".tool_userdefinedreport where rnumber=".$param['rnumber'];
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
		   $judul=$bar->namalaporan;
		}
		#get all tool user
		$str="select * from ".$dbname.".tool_userdefinedreport_user where rnumber=".$param['rnumber']." order by username";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$tooUser[$bar->username]=$bar->status;
		}
		#get all user active 
		$str="select b.namauser,c.lokasitugas from ".$dbname.".user b
			  left join ".$dbname.".datakaryawan c on b.karyawanid=c.karyawanid
			  where b.status=1";
		$tab="<fieldset><div style='height:250px;width:500px;overflow:auto;'>User for ".$judul."<br>Report Number:".$param['rnumber']."
		      <table class=sortable cellspacing=1 border=0 width=100%>
			  <thead><tr class=rowheader><td align=center>No</td><td align=center>Username</td><td align=center>Location</td><td align=center width=30px>Action</td>
			  </tr></thead><tbody>";
		$no=0;	  
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$no++;
			if($tooUser[$bar->namauser]=='1'){
				$mark="checked";
			}else{
				$mark="";
			}    
			$cek="<input type=checkbox value='".$bar->namauser."' onclick=updateTollUser(this,'".$param['rnumber']."','".$bar->namauser."') ".$mark."></input>";
		  	$tab.="<tr class=rowcontent><td align=center>".$no."</td><td>".$bar->namauser."</td><td>".$bar->lokasitugas."</td><td align=center>".$cek."</td></tr>";
		}
	    $tab.="</tbody></tfoot></tfoot></table>
		       </div></fieldset>";
		echo $tab;	   
	break;
	case 'updateUser':
		if($param['val']==1){
			$str="insert into ".$dbname.".tool_userdefinedreport_user(rnumber,username,status)
			    values(".$param['rnumber'].",'".$param['user']."',1)";
		}else{
			$str="delete from ".$dbname.".tool_userdefinedreport_user where username='".$param['user']."'
				  and rnumber=".$param['rnumber'];
		}
		try{
			$owlPDO->exec($str);
		}catch (PDOException $e){
				echo "Gagal : ".$e->getMessage();
			}
	break;
	case 'browseReport':
			
		#get Judul
		$judul='';
		$str="select * from ".$dbname.".tool_userdefinedreport where rnumber=".$param['rnumber'];
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$judul=$bar->namalaporan;
		   	$param['html']=$bar->html;
			$param['pdf']=$bar->pdf;
			$param['excel']=$bar->speadsheet;
		}
		#get parameter
		$str="select * from ".$dbname.".tool_userdefinedreport_par where rnumber=".$param['rnumber'];
		$cou=0;
		$tab="<fieldset><legend>Title:".$judul."</legend>
		      <table id=flyTable class=rounded5><tbody>";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_OBJ);
		while ($bar = $res->fetch()) {
			$tt=explode(".",$bar->kolom);
			$tab.="<tr><td value='".$bar->kolom."'>".ucfirst($tt[1])."</td><td>".$bar->operator."</td><td>";
			
			$nullx=strpos($bar->operator,"NULL");
            $betw=strpos($bar->operator,"BETWEE");
			if ($bar->value=='TEXT' && $nullx===false && $betw===false) {
                $tab.="<input type=text class=myinputtext id=frmparam".$cou." onkeypress='return tanpa_kutip(event);' style=width:150px></input>";
            }else if ($bar->value=='DATE' && $nullx===false && $betw===false) {
                $tab.="<input type=text class=myinputtext id=frmparam".$cou." onkeypress='return false;' onmousemove=setCalendar(this.id) style=width:150px></input>";
            }else if ($bar->value=='NUMERIC' && $nullx===false && $betw===false) {
                $tab.="<input type=text class=myinputtextnumber id=frmparam".$cou." onkeypress='return angka_doang(event);' style=width:150px></input>";
            }else if ($nullx!==false) {
                $tab.="<input type=text class=myinputtext id=frmparam".$cou." disabled style=width:150px value='".$bar->operator."'></input>";
            }else if ($betw!==false) {
                if ($bar->value=='TEXT'){
                    $tab.="<input type=text class=myinputtext id=frmparam".$cou." onkeypress='return tanpa_kutip(event);' style=width:60px> and <input type=text class=myinputtext id=frmparama".$cou." onkeypress='return tanpa_kutip(event);' style=width:60px></input>";
                }else if ($bar->value=='NUMERIC') {
                    $tab.="<input type=text class=myinputtextnumber id=frmparam".$cou." onkeypress='return angka_doang(event);' style=width:60px> and <input type=text class=myinputtextnumber id=frmparama".$cou." onkeypress='return angka_doang(event);' style=width:60px></input>";
                } else if ($bar->value=='DATE') {
                    $tab.="<input type=text class=myinputtext id=frmparam".$cou." onkeypress='return false;' onmousemove=setCalendar(this.id) style=width:60px> and <input type=text class=myinputtext id=frmparama".$cou." onkeypress='return false;' onmousemove=setCalendar(this.id) style=width:60px></input>";
                }
				$tab.="</td></tr>";
			}	
			$cou++;
		}
		$tab.="</tody></table>";
		echo $tab;
		$butt='';
		#html
		if($param['html']=='1'){
			$butt.="<button class=mybutton onclick=displayReportUser('html',".$param['rnumber'].",event) title='Report in HTML' style='cursor:pointer;'>HTML</button>";
		}
		#excel
		if($param['excel']=='1'){
			$butt.="<button class=mybutton onclick=displayReportUser('excel',".$param['rnumber'].",event) title='Report in SpreadSheet' style='cursor:pointer;'>SpreadSheet</button>";
		}
		#pdf
		if($param['pdf']=='1'){
			$butt.="<button class=mybutton onclick=displayReportUser('pdf',".$param['rnumber'].",event) title='Report in PDF' style='cursor:pointer;'>PDF</button>";
		}
		echo "<center>".$butt."</center></fieldset>";
		
	break;	
	default:
	break;
}
?>