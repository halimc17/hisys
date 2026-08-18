<?php
require_once('master_validation.php');
require_once('lib/zLib.php');
include_once('lib/HtmlExcel.php');

$method   = checkPostGet('method', '');
$param = $_POST;if(count($param)==0){$param = $_GET;}

$nmorg=makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi');
switch($method){
	case 'getfilter':
		#get Judul
		$judul='';
		$str="select * from ".$dbname.".tool_userdefinedreport where rnumber=".$param['rnumber'];
		$res = fetchData($str);
		foreach($res as $bar){
			$query         = $bar['query'];
			$judul         = $bar['namalaporan'];
			$param['html'] = $bar['html'];
			$param['pdf']  = $bar['pdf'];
			$param['excel']= $bar['speadsheet'];
			$kolomTampil   = explode(",",$bar['kolomtampil']);
		}
		#get parameter
		$tab="<fieldset><legend>Filter</legend>
		      <table border=0 id=flyTable><tbody>";
		$cou=0;
		$str="select * from ".$dbname.".tool_userdefinedreport_par where rnumber='".$param['rnumber']."' order by kolom asc";
		$res = fetchData($str);
		foreach($res as $bar){
			$tt=explode(".",$bar['kolom']);
			$tab.="<tr><td value='".$bar['kolom']."'>".strtolower($tt[1])."</td><td style=color:blue;>".strtolower($bar['operator'])."</td><td>&nbsp;";
			
			if ($bar['operator']=='='){
				$post= str_replace('#PARAMETER#',"",$query);
				$sel = strpos($post,"select");
				$frm = strpos($post,"from");
				$slct= substr($post,$sel+6,$frm-($sel+6));
				$sql = "select  distinct ".strtolower($tt[1])." as opt from ".$slct= substr($post,$frm+5,strlen($post))." order by opt asc";
				$q = fetchData($sql);
				$opte="<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
				foreach($q as $e){
					$opte.="<option value=".$e['opt'].">".$e['opt']."</option>";
				}
			}		
			
			$nullx=strpos($bar['operator'],"NULL");
			$betw =strpos($bar['operator'],"BETWEE");
			if ($bar['value']=='TEXT' && $nullx===false && $betw===false) {
				if ($bar['operator']=='='){
					$tab.="<select id=frmparam".$cou."  style=\"width:155px;\">'".$opte."'</select>";
				}else{					
					$tab.="<input type=text class=myinputtext id=frmparam".$cou." onkeypress='return tanpa_kutip(event);' style=width:150px></input>";
				}	
            }else if ($bar['value']=='DATE' && $nullx===false && $betw===false) {
                $tab.="<input type=text class=myinputtext id=frmparam".$cou." onkeypress='return false;' onmousemove=setCalendar(this.id) style=width:150px></input>"; $isi='2';
            }else if ($bar['value']=='NUMERIC' && $nullx===false && $betw===false) {
				if ($bar['operator']=='='){
					$tab.="<select id=frmparam".$cou."  style=\"width:155px;\">'".$opte."'</select>";
				}else{	
					$tab.="<input type=text class=myinputtextnumber id=frmparam".$cou." onkeypress='return angka_doang(event);' style=width:150px></input>";
				}
            }else if ($nullx!==false) {
                $tab.="<input type=text class=myinputtext id=frmparam".$cou." disabled style=width:150px value='".$bar['operator']."'></input>"; $isi='4';
            }else if ($betw!==false) {
                if ($bar['value']=='TEXT'){
                    $tab.="<input type=text class=myinputtext id=frmparam".$cou." onkeypress='return tanpa_kutip(event);' style=width:60px> and <input type=text class=myinputtext id=frmparama".$cou." onkeypress='return tanpa_kutip(event);' style=width:60px></input>"; $isi='5';
                }else if ($bar['value']=='NUMERIC') {
                    $tab.="<input type=text class=myinputtextnumber id=frmparam".$cou." onkeypress='return angka_doang(event);' style=width:60px> and <input type=text class=myinputtextnumber id=frmparama".$cou." onkeypress='return angka_doang(event);' style=width:60px></input>"; $isi='6';
                } else if ($bar['value']=='DATE') {
                    $tab.="<input type=text class=myinputtext id=frmparam".$cou." onkeypress='return false;' onmousemove=setCalendar(this.id) style=width:60px> and <input type=text class=myinputtext id=frmparama".$cou." onkeypress='return false;' onmousemove=setCalendar(this.id) style=width:60px></input>"; $isi='7';
                }
				$tab.="</td></tr>";
			}
			$cou++;
		}
		$tab.="</tody></table></fieldset>";
		$tab.="<fieldset id=fileterpivot><legend>Pivot</legend><table width=100%>";
		
		$optrpt="<option value=''></option>";
		$col=array();
		foreach($kolomTampil as $key => $val){
			$col[$val]+=1;
		}
		
		foreach($kolomTampil as $key => $val){
			if($col[$val]>1){
				$optrpt.="<option value=".$val.$key.">".$val.$key."</option>";
			}else{				
				$optrpt.="<option value=".$val.">".$val."</option>";
			}
		}
		
		$tab.="<tr><td style=font-style:italic>Baris</td><td><select id=row style=width:55px>".$optrpt."</select></td>
				   <td style=font-style:italic>Kolom</td><td><select id=col style=width:55px>".$optrpt."</select></td>
				   <td style=font-style:italic>Data</td><td><select id=val style=width:55px>".$optrpt."</select></td></tr>";
		
		$tab.="</table></fieldset>";
		echo $tab;
	break;
	case'pivot':
		
		$parameter = str_replace("\\","",$param['parameter']);
		$parameter = str_replace("::persen::","%",$parameter);
		if($parameter!=''){
			$parameter=" where ".$parameter;
		}
		$str="select * from ".$dbname.".tool_userdefinedreport where rnumber=".$param['jenislaporan'];
		$res = fetchData($str);
		foreach($res as $bar){
			$query      = $bar['query'];
			$kolomTampil= explode(",",$bar['kolomtampil']);
			$group      = explode(",",$bar['group']);
			$subtotal   = explode(",",$bar['subtotal']);
			$judul      = $bar['namalaporan'];
		}
		
		$col=array();
		foreach($kolomTampil as $key => $val){
			$col[$val]+=1;
		}
		
		foreach($kolomTampil as $key => $val){
			if($col[$val]>1){
				$data[0][]=$val.$key;
			}else{				
				$data[0][]=$val;
			}
		}
		
		
		// $row = array("unit");
		// $col = array("prd");
		// $val = array("kgwb");
		
		if($param['jenis']=='excel'){
			unset($data[0]);
			$no=0;
		}else{			
			$no=1;
		}
		$query=str_replace('#PARAMETER#',$parameter,$query);
		$res=$owlPDO->query($query) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_NUM);		
		while ($bar = $res->fetch()) {
			foreach($kolomTampil as $key1=>$val){
				if($group[$key1]!='0' && isset($group[$key1]) && $group[$key1]!=''){
					$data[$no][]=htmlentities($bar[$key1]);
				}else{
					$data[$no][]=htmlentities($bar[$key1]);
				}
			}
			$no+=1;
		}
		
		$post=$query;
		$sel = strpos($post,"select");
		$frm = strpos($post,"from");
		$slct= substr($post,$sel+6,$frm-($sel+6));
		$n = explode(",",$slct);
		foreach($n as $z => $e){
			$i = explode(".",trim($e));
			$dttb[]=$i[0];
		}
		
		foreach($kolomTampil as $z => $e){
			$dtc[$z]['kolom']=$e;
		}
		
		
// echo"<pre>";
// print_r($slct);
// print_r($tes);
// print_r($dtc);
// echo"</pre>";
// exit("error");

		if($param['jenis']=='excel'){
			foreach($kolomTampil as $key => $val){
				$jenis = getColType($dttb,$dtc[$key]['kolom']);
				$jn[]=$val;
				if($jenis=='double'){
					$angka[]=$key;
				}
				if($col[$val]>1){
					$datae[0][]=$val.$key;
				}else{				
					$datae[0][]=$val;
				}
			}
			$tab.="<table id=pvtTable cellpadding=1 cellspacing=1 border=0 class='sortable' width='100%' data-scroll-x='true' scroll-collapse='false'>
				<thead>
					<tr>";
					foreach($datae as $key => $var){
						foreach($var as $val){
							if($key==0){					
								$tab.="<th>".$val."</th>";
								$jlhcolhead++;
							}
						}
					}
				
				$tab.="</tr>
				</thead><tbody>";
				
				// foreach($data as $key => $var){
					// if($key>0){					
						// //$tab.="<tr>";
						// foreach($var as $key => $val){
							// $st=$do="";
							// $jenis = getColType($dtc[$key]['table'],$dtc[$key]['kolom']);
							// //$jn[$dtc[$key]['kolom']][$key]=$jenis;
							
							// // if($jenis=='double' ){ #or is_numeric($val)
								// // $angka[$key]=$key;
							// // }
							// //$tab.="<td ".$st.">".$val."</td>";
						// }
						// //$tab.="</tr>";
					// }
				// }
				
			$tab.="</tbody><tfoot>
				<tr>";
				foreach($datae as $key => $var){
					foreach($var as $val){
						if($key==0){					
							$tab.="<th>".$val."</th>";
						}
					}
				}
			$tab.="</tr></tfoot>";	
			$tab.="</table>";
			$tab.="<fieldset style=float:left;><legend>Show/Hide</legend><div>";
				$e=0;
				foreach($datae as $key => $var){
					foreach($var as $val){
						if($key==0){
							$tab.="<button class=\"dt-button\" data-column=".$e.">".$val."</button>";
							$e++;
						}
					}
				}
			$tab.="</div></fieldset>";
			
			echo $tab."####".json_encode($data)."####".json_encode($angka);
		}else{	
			echo json_encode($data)."####".$param['row']."####".$param['col']."####".$param['val'];
		}
	break;
}

function getColType($table,$kolom){
	global $dbname;
	global $conn;
	global $owlPDO;
	
	foreach($table as $tbl){		
		$str="SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$dbname."' and table_name = '".$tbl."' AND COLUMN_NAME = '".$kolom."';";
		$res = fetchData($str);
		foreach($res as $bar){
			$type = $bar['DATA_TYPE'];
		}
	}
	
	
	return $type;
}

// echo"<pre>";
// print_r($data);
// echo"</pre>";
// exit("error".$str);


?>
