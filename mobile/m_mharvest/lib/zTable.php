<?php
include_once('lib/nangkoelib.php');
include_once('lib/zConfig.php');
/*
 * Function makeTable
 * Fungsi untuk membuat table standard
 * I : array header, array content, array footer
 * O : table dalam format HTML
 * U : $table = makeTable(arr,arr,arr,bin);
 */

 function pre_arr($arr){
	echo "<pre>";
	echo print_r($arr);
	echo "</pre>";
 }
 function makeTable($id,$bodyId='',$header=array(),$content=array(),$footer=array(),$sortable=false,$tr='tr',$click=null) {
       # Start Table
       if($sortable) {
	      # Sortable Table
	      $tables = "<table id='".$id."' name='".$id."' class='sortable' cellspacing='1' border='0'>";
       } else {
	      # Plain Table
	      $tables = "<table id='".$id."' name='".$id."' class='data' cellspacing='1' border='0'>";
       }
       
       # Create Header
       $tables .= "<thead><tr class='rowheader'>";
       foreach($header as $hName) {
	       $tables .= "<td>".$hName."</td>";
       }
       $tables .= "</tr></thead>";
       
       # Iterate Content
       $tables .= "<tbody id='".$bodyId."'>";
       foreach($content as $key=>$row) {
	      if($click!=null) {
		     $tables .= "<tr id='".$tr."_".$key."' class='rowcontent' onclick='".$click."(".$key.")'>";
	      } else {
		     $tables .= "<tr id='".$tr."_".$key."' class='rowcontent'>";
	      }
	      $i=0;
	      foreach($row as $c) {
		     $tables .= "<td id='col_".$header[$i]."_".$key."'>".$c."</td>";
		     $i++;
	      }
	      $tables .= "</tr>";
       }
       $tables .= "</tbody>";
       
       # Create Footer
       $tables .= "<tfoot>";
       foreach($footer as $fName) {
	       $tables .= "<td>".$hName."</td>";
       }
       $tables .= "</tfoot>";
       
       # End Table
       $tables .= "</table>";
       
       return $tables;
 }
 
 /*
 * Function makeCompleteTable
 * Fungsi untuk membuat table lengkap
 * I : array header, array content, array footer
 * O : table dalam format HTML
 * U : $table = makeTable(arr,arr,arr,bin);
 */
 function makeCompleteTable($id,$bodyId='',$header=array(),$content=array(),$footer=array(),$sortable=false,$tr='tr',$click=null) {
       # Start Table
       if($sortable) {
	      # Sortable Table
	      $tables = "<table id='".$id."' name='".$id."' class='sortable' cellspacing='1' border='0'>";
       } else {
	      # Plain Table
	      $tables = "<table id='".$id."' name='".$id."' class='data' cellspacing='1' border='0'>";
       }
       
       # Create Header
       $tables .= "<thead><tr class='rowheader'>";
       $field = array();
       foreach($header as $hField=>$hName) {
	       $tables .= "<td>".$hName."</td>";
	       $field[] = $hField;
       }
       $tables .= "</tr></thead>";
       
       # Iterate Content
       $tables .= "<tbody id='".$bodyId."'>";
       foreach($content as $key=>$row) {
	      if($click!=null) {
		     $tables .= "<tr id='".$tr."_".$key."' class='rowcontent' onclick='".$click."'>";
	      } else {
		     $tables .= "<tr id='".$tr."_".$key."' class='rowcontent'>";
	      }
	      $i=0;
	      foreach($row as $c) {
		     $tables .= "<td id='col_".$field[$i]."_".$key."'>".$c."</td>";
		     $i++;
	      }
	      $tables .= "</tr>";
       }
       $tables .= "</tbody>";
       
       # Create Footer
       $tables .= "<tfoot>";
       foreach($footer as $fName) {
	       $tables .= "<td>".$hName."</td>";
       }
       $tables .= "</tfoot>";
       
       # End Table
       $tables .= "</table>";
       
       return $tables;
 }
 
/*
 * Function masterTableConfig
 * Fungsi untuk membuat table master yang disederhanakan
 * @param	string	$dbname		Nama Database
 * @param	string	$table		Nama Table
 * @param	array	$config		Optional Config
 * @return	HTML				Table Master
 */
function masterTableConfig($dbname,$table,$config=array()) {
	$column			=isset($config['column'])? $config['column']: "*";
	$headerSetting	=isset($config['headerSetting'])? $config['headerSetting']: array();
	$dataSetting	=isset($config['dataSetting'])? $config['dataSetting']: array();
	$cond			=isset($config['cond'])? $config['cond']: array();
	$fForm			=isset($config['fForm'])? $config['fForm']: array();
	$printTo		=isset($config['printTo'])? $config['printTo']: null;
	$freezeField	=isset($config['freezeField'])? $config['freezeField']: null;
	$printShow		=isset($config['printShow'])? $config['printShow']: true;
	$postTo			=isset($config['postTo'])? $config['postTo']: null;
	$opt			=isset($config['opt'])? $config['opt']: array();
	$listName		=isset($config['listName'])? $config['listName']: null;
	$test			=isset($config['test'])? $config['test']: null;
	$changeLang		=isset($config['changeLang'])? $config['changeLang']: null;
	$hide			=isset($config['hide'])? $config['hide']: null;
	
	return masterTable($dbname,$table,$column,$headerSetting,$dataSetting,
					   $cond,$fForm,$printTo,$freezeField,$printShow,$postTo,
					   $opt,$listName,$test,$changeLang,$hide);
}

/*
 * Function masterTable
 * Fungsi untuk membuat table master
 * I : Nama DB, Nama Table, Kolom, Kondisi Query, Nama field pada Form, Halaman Tujuan, Setting untuk Header
 * O : table dalam format HTML
 * U : $table = masterTable(str,str,arr/str,str,arr,str,obj);
 * headerSetting[] = array(
 * 	'name'=>'string',
 * 	'align'=>'left/center/right',
 * 	'span'=>span_long
 * );
 */
function masterTable($dbname,$table,$column="*",$headerSetting=array(),
	$dataSetting=array(),$cond=array(),$fForm=array(),$printTo=null,
	$freezeField=null,$printShow=true,$postTo=null,$opt=array(),$listName=null,
	$test=null,$changeLang=array(),$hide=null) {
	$hide=explode(',',$hide);
	#====================== Prep
	if($postTo==null) {
	   $postTo = 'null';
	}
	//if($printTo==null) {
	//   $printTo = 'null';
	//}
	
	#====================== Select Query
	$query = "select ";
	# Column
	$colStr = "";
	if(is_array($column) and $column!=array()) {
		for($i=0;$i<count($column);$i++) {
			if($i==0) {
				$query .= $column[$i];
				$colStr .= $column[$i];
			} else {
				$query .= ",".$column[$i];
				$colStr .= ",".$column[$i];
			}
		}
	} else {
		$query .= "*";
		$colStr .= "*";
	}
	# From Table
	$query .= " from ".$dbname.".".$table;
	
	# Condition
	if($cond!=null) {
		$condStr = "";
		if(is_array($cond)) {
			$condPdf = $cond['sep']."^^";
			unset($cond['sep']);
			foreach($cond as $row) {
				foreach($row as $attr=>$val) {
					if($row==end($cond)) {
						$condPdf .= $attr."**".$val;
						if(is_string($val)) {
						   $condStr .= $attr."='".$val."'";
						} else {
						   $condStr .= $attr."=".$val;
						}
					} else {
						$condPdf .= $attr."**".$val."~~";
						if(is_string($val)) {
							$condStr .= $attr."='".$val."' OR ";
						} else {
							$condStr .= $attr."=".$val." OR ";
						}
					}
				}
			}
		} else {
			$condPdf = $cond;
			$condStr = $cond;
		}
		$query .= " where ".$condStr;
	} else {
		$condPdf = null;
	}
	global $owlPDO;
	#======================= Execute Query
	$res=$owlPDO->query($query);
	$res->setFetchMode(PDO::FETCH_ASSOC);
	#======================= Extract Field Related
	$j = ($res)?$res->columnCount(): 0;
	$i = 0;
	$field = array();
	$fieldStr = "";
	$primary = array();
	$primaryStr = "";
	
                    for($i=0;$i<$j;$i++){
                    $test=$res->getColumnMeta($i);
                    $field[] = strtolower($test['name']);
                    $fieldStr .= "##".strtolower($test['name']);
                        if(is_array($test['flags'])){
                                    foreach($test['flags'] as $key=>$val){
                                           $val=strtolower($val);
                                           $pos = strpos($val, 'primary');
                                           if ($pos !== false) {
                                                $primary[]=$test['name'];
                                                $primaryStr .= "##".strtolower($test['name']);
                                           }
                                    }
                        }
                    } 
	
	if($fForm==array()) {
		$fForm = $field;
	}
	
	#======================= Rearrange Result and Extract Values
	$result = array();
	if($res) {
                        while($bar=$res->fetch()) {
                                $result[] = $bar;
                        }
	}
	
	/**
	 * PDF Config
	 */
	$pdfConfig = array(
		'table' => $table,
		'column' => $colStr,
		'cond' => $condPdf,
		'page' => $printTo
	);
	$pdfConfig = htmlspecialchars(stripcslashes(json_encode($pdfConfig)));
	
	#======================= Create Print
	$tables = "<fieldset><legend><b>".$_SESSION['lang']['list']." : ";
	if(is_null($listName)) {
		$tmpName = str_replace('_',' ',$table);
		$tmpName = str_replace(range(0,9),'',$tmpName);
		$tmpName = ucwords($tmpName);
		$tables .= $tmpName;
	} else {
		$tables .= $listName;
	}
	$tables .= "</b></legend>";
	$tables .= "<img src='images/pdf.jpg' title='PDF Format'
	  style='width:20px;height:20px;cursor:pointer' onclick=\"zMaster.printPdf(event,".$pdfConfig.")\">&nbsp;";
	$tables .= "<img src='images/printer.png' title='Print Page'
	  style='width:20px;height:20px;cursor:pointer' onclick='javascript:print()'>";
	if($test=='1')
	{
		$tables.="&nbsp<img title=\"MS.Excel\" class=\"resicon\" src=\"images/excel.jpg\" onclick=\"dataKeExcel(event)\">";
	}
	
	#======================= Create Table
	# Start Table
	if($printShow) {
	  $tables .= "<div style='height:370px;overflow:auto'>";
	}
	$tables .= "<table id='masterTable' class='sortable' cellspacing='1' border='0'>";
	
	# Create Header
	$tables .= "<thead><tr class='rowheader'>";
	if($headerSetting==null) {
		foreach($field as $hName) {
			if(isset($_SESSION['lang'][$hName])) {
				//$tables .= "<td>".$_SESSION['lang'][$hName]."</td>";
				if($hName==@$hide[0] || $hName==@$hide[1] || $hName==@$hide[2]){
					$tables .= "<td hidden>".$_SESSION['lang'][$hName]."</td>";
				}else{
					$tables .= "<td>".$_SESSION['lang'][$hName]."</td>";
				}
			} elseif(isset($changeLang[$hName])) {
				$tables .= "<td>".$_SESSION['lang'][$changeLang[$hName]]."</td>";
			} else {
				$tables .= "<td>".ucfirst($hName)."</td>";
			}
		}
	} else {
	   foreach($headerSetting as $hSet) {
		  if(!isset($hSet['span'])) {
			 $hSet['span'] = '0';
		  }
		  if(!isset($hSet['align'])) {
			 $hSet['align'] = 'left';
		  }
		  $tables .= "<td colspan='".$hSet['span']."' align='".$hSet['align']."'>".$hSet['name']."</td>";
	   }
	}
	
	$tables .= "<td colspan='2'>".$_SESSION['lang']['action']."</td>";
	$tables .= "</tr></thead>";
	
	# Iterate Content
	$tables .= "<tbody id='mTabBody'>";
	$i=0;
	foreach($result as $row) {
		$tables .= "<tr id='tr_".$i."' class='rowcontent'>";
		$tmpVal = "";
		$tmpKey = "";
		$j=0;
		foreach($row as $b=>$c) {
			# For Tipe Tanggal
			$tmpC = explode("-",$c);
			if(count($tmpC)==3 and strlen($c)==10) {
			   $c = $tmpC[2]."-".$tmpC[1]."-".$tmpC[0];
			}
			if(!isset($dataSetting[$b]['type'])) {
			   $dataSetting[$b]['type'] = 'default';
			}
			if(isset($opt[$fForm[$j]])) {
				$theVal = $opt[$fForm[$j]][$c];
			} else {
				$theVal = $c;
			}
			switch($dataSetting[$b]['type']) {
				case 'numeric':
					$tables .= "<td id='".$fForm[$j]."_".$i."' align='right' value='".$c."'>".number_format($theVal,0)."</td>";
					break;
				case 'currency':
					$tables .= "<td id='".$fForm[$j]."_".$i."' align='right' value='".$c."'>".number_format($theVal,2)."</td>";
					break;
				case 'string':
					$tables .= "<td id='".$fForm[$j]."_".$i."' align='left' value='".$c."'>".$theVal."</td>";
					break;
				default:
					if(@$fForm[$j]==@$hide[0] || @$fForm[$j]==@$hide[1] || @$fForm[$j]==@$hide[2]){
						$tables .= "<td hidden id='".$fForm[$j]."_".$i."' value='".$c."'>".$theVal."</td>";
					}else{
						$tables .= "<td id='".$fForm[$j]."_".$i."' value='".$c."'>".$theVal."</td>";
					}
					
					break;
			}
		  $tmpVal .= "##".$c;
		  if(in_array($fForm[$j],$primary)) {
			 $tmpKey .= "##".$c;
		  }
		  $j++;
	   }
		# Edit, Delete Row
		if($freezeField!=null) {
		   $tables .= "<td><img id='editRow".$i."' title='Edit' onclick=\"editRow(".$i.",'".$fieldStr."','".$tmpVal."','".$freezeField."')\"
		   class='zImgBtn' src='images/001_45.png' /></td>";
		} else {
		   $tables .= "<td><img id='editRow".$i."' title='Edit' onclick=\"editRow(".$i.",'".$fieldStr."','".$tmpVal."')\"
		   class='zImgBtn' src='images/001_45.png' /></td>";
		}
		if($table!='keu_5akun'){//daftar perkiraan tombol deletenya dihilangkan
                    if($postTo=='null') {
                       $tables .= "<td><img id='delRow".$i."' title='Hapus' onclick=\"delRow(".$i.",'".$primaryStr."','".$tmpKey."',null,'".$table."')\"
                              class='zImgBtn' src='images/delete_32.png' /></td>";
                    } else {
                       $tables .= "<td><img id='delRow".$i."' title='Hapus' onclick=\"delRow(".$i.",'".$primaryStr."','".$tmpKey."','".$postTo."','".$table."')\"
                              class='zImgBtn' src='images/delete_32.png' /></td>";
                    }
                }
	   $tables .= "</tr>";
	   $i++;
	}
   
	$tables .= "</tbody>";
	
	# Create Footer
	$tables .= "<tfoot>";
	#foreach($footer as $fName) {
	#	$tables .= "<td>".$hName."</td>";
	#}
	$tables .= "</tfoot>";
	
	# End Table
	$tables .= "</table>";
	if($printShow) {
	  $tables .= "</div>";
	}
	$tables .= "</fieldset>";
	
	return $tables;
}

 /*
 * Function masterTable
 * Fungsi untuk membuat table master
 * I : Nama DB, Nama Table, Kolom, Kondisi Query, Nama field pada Form, Halaman Tujuan, Setting untuk Header
 * O : table dalam format HTML
 * U : $table = masterTable(str,str,arr/str,str,arr,str,obj);
 * headerSetting[] = array(
 * 	'name'=>'string',
 * 	'align'=>'left/center/right',
 * 	'span'=>span_long
 * );
 */
 function masterTableBlok($dbname,$table,$tot,$column="*",$headerSetting=array(),
      $dataSetting=array(),$cond=array(),$fForm=array(),$printTo=null,
      $freezeField=null,$printShow=true,$postTo=null,$opt=array(),$listName=null) {
      
       #====================== Prep
       if($postTo==null) {
	      $postTo = 'null';
       }
       if($printTo==null) {
	      $printTo = 'null';
       }
       
       #====================== Select Query
       $query = "select ";
       # Column
       $colStr = "";
       if(is_array($column) and $column!=array()) {
	      for($i=0;$i<count($column);$i++) {
		     if($i==0) {
			    $query .= $column[$i];
			    $colStr .= $column[$i];
		     } else {
			    $query .= ",".$column[$i];
			    $colStr .= ",".$column[$i];
		     }
	      }
       } else {
	      $query .= "*";
       }
       
       # From Table
       $query .= " from ".$dbname.".".$table;
       # Condition
       if($cond!=null) {
	      $condStr = "";
	      if(is_array($cond)) {
		     $condPdf = $cond['sep']."^^";
		     unset($cond['sep']);
		     foreach($cond as $row) {
			    foreach($row as $attr=>$val) {
				   if($row==end($cond)) {
					  $condPdf .= $attr."**".$val;
					  if(is_string($val)) {
						 $condStr .= $attr."='".$val."'";
					  } else {
						 $condStr .= $attr."=".$val;
					  }
					  
				   } else {
					  $condPdf .= $attr."**".$val."~~";
					  if(is_string($val)) {
						 $condStr .= $attr."='".$val."' OR ";
					  } else {
						 $condStr .= $attr."=".$val." OR ";
					  }
				   }
			    }
		     }
	      } else {
		     $condPdf = $cond;
		     $condStr = $cond;
	      }
	      $query .= " where ".$condStr;
       } else {
	      $condPdf = null;
       }
     global $owlPDO;
    #======================= Execute Query
    $res=$owlPDO->query($query);
    $res->setFetchMode(PDO::FETCH_ASSOC);
    #======================= Extract Field Related
    $j = ($res)?$res->columnCount(): 0;
    $i = 0;
    $field = array();
    $fieldStr = "";
    $primary = array();
    $primaryStr = "";

                for($i=0;$i<$j;$i++){
                $test=$res->getColumnMeta($i);
                $field[] = strtolower($test['name']);
                $fieldStr .= "##".strtolower($test['name']);
                    if(is_array($test['flags'])){
                                foreach($test['flags'] as $key=>$val){
                                       $val=strtolower($val);
                                       $pos = strpos($val, 'primary');
                                       if ($pos !== false) {
                                            $primary[]=$test['name'];
                                            $primaryStr .= "##".strtolower($test['name']);
                                       }
                                }
                    }
                } 

    if($fForm==array()) {
            $fForm = $field;
    }

    #======================= Rearrange Result and Extract Values
    $result = array();
    if($res) {
                    while($bar=$res->fetch()) {
                            $result[] = $bar;
                    }
    }
       #======================= Create Print
       //$tables = "<b>".$_SESSION['lang']['list']." : ";
       $tables = "";
       if(is_null($listName)) {
	    //$tables .= $table;
       } else {
	    //$tables .= $listName;
       }
      // $tables .= "</b>";
       $tools = "<button type=button class='mybutton'><img class='icon'  src='images/pdf.jpg' title='PDF Format' onclick=\"masterPDF('".$table."','".$colStr."','".$condPdf."','".$printTo."',event)\"></button>";
       $tools .= "<button type=button class='mybutton'><img class='icon'  src='images/printer.png' title='Print Page' onclick='javascript:print()'></button>";
       
       #======================= Create Table
       # Start Table
       if($printShow) {
	     //$tables .= "<div style='overflow:auto'>";
       }
       $tables .= "<table id='masterTable' class='sortable' cellspacing='1' border='0' style='position:sticky;top:0px;'>";
       
       # Create Header
       $tables .= "<thead style='top: 0px; position: sticky;'><tr class='rowheader'>";
       $th = "";
	    $colSpanTools = count($field);
       if($headerSetting==null) {
	      foreach($field as $hName) {
		     $th .= "<th>".$_SESSION['lang'][$hName]."</th>";
	      }
       } else {
	      foreach($headerSetting as $hSet) {
		     if(!isset($hSet['span'])) {
			    $hSet['span'] = 1;
		     }else{
				 $colSpanTools +=((int)$hSet['span']-1);
			 }
		     if(!isset($hSet['align'])) {
			    $hSet['align'] = 'left';
		     }
		     $th .= "<th colspan='".$hSet['span']."' align='".$hSet['align']."'>".$hSet['name']."</th>";
	      }
       }
       $tables .= "<tr class='tools'>";
	   $tables .= "<th colspan='".$colSpanTools."' align='left' name='tools'>".$tools."</th>";
       $tables .= "</tr>";
       $tables .=$th;
      //$tables .= "<td colspan='2'>".$_SESSION['lang']['action']."</td>";
       $tables .= "</tr></thead>";
       
       # Iterate Content
       $tables .= "<tbody id='mTabBody'>";
       $i=0;
       foreach($result as $row) {
	      $tables .= "<tr id='tr_".$i."' class='rowcontent'>";
	      $tmpVal = "";
	      $tmpKey = "";
	      $j=0;
	      foreach($row as $b=>$c) {
		     # For Tipe Tanggal
		     $tmpC = explode("-",$c);
		     if(count($tmpC)==3) {
			    $c = $tmpC[2]."-".$tmpC[1]."-".$tmpC[0];
		     }
		     if(!isset($dataSetting[$b]['type'])) {
			    $dataSetting[$b]['type'] = 'default';
		     }
		     if(isset($opt[$fForm[$j]])) {
				$theVal = $opt[$fForm[$j]][$c];
		     } else {
				$theVal = $c;
		     }
			 $total = array();
		     switch($dataSetting[$b]['type']) {
			    case 'numeric':
				   $tables .= "<td id='".$fForm[$j]."_".$i."' align='right' value='".$c."'>".number_format($theVal,0)."</td>";
				   if(!isset($total[$b])) $total[$b]=0;
                    $total[$b]+=$theVal;
				   break;
			    case 'currency':
				   $tables .= "<td id='".$fForm[$j]."_".$i."' align='right' value='".$c."'>".number_format($theVal,2)."</td>";
				   if(!isset($total[$b])) $total[$b]=0;
                    $total[$b]+=$theVal;
				   break;
			    case 'string':
			    	if ($fForm[$j] == 'kodeorg') {
			    		$isi = getorgsap($theVal);
			    	} else {
			    		$isi = $theVal;
			    	}
				   $tables .= "<td id='".$fForm[$j]."_".$i."' align='left' value='".$c."'>".$isi."</td>";
				   break;
                            case'month':
                                 $tables .= "<td id='".$fForm[$j]."_".$i."' align='left' value='".$c."'>".numToMonth($theVal)."</td>";
                            break;
			    default:
					$tmpJenisTanah = readLst("./config/jenistanah.lst");
					$optJenisTanah = lst2opt($tmpJenisTanah,0,1);
					$tmpKlsTanah = readLst("./config/kelastanah.lst");
					$optKlsTanah = lst2opt($tmpKlsTanah,0,1);
					$optBlokStat = getEnum($dbname,'setup_blok','statusblok');
					$optIP = getEnum($dbname,'setup_blok','intiplasma');
					$optTopografi = makeOption($dbname,'setup_topografi','topografi,keterangan');
					if(isset($optJenisTanah[$theVal]))
					{
						$newval = $optJenisTanah[$theVal];
					}
					else
					{
						if(isset($optKlsTanah[$theVal]))
						{
							$newval = $optKlsTanah[$theVal];
						}
						else
						{
							if(isset($optBlokStat[$theVal]))
							{
								$newval = $optBlokStat[$theVal];
							}
							else
							{
								if(isset($optIP[$theVal]))
								{
									$newval = ($optIP[$theVal]=='I'?'Inti':'Plasma');
								}
								else
								{
									if(isset($optTopografi[$theVal]))
									{
										$newval = $optTopografi[$theVal];
									}
									else
									{
										$newval = $theVal;
									}
								}
							}
						}
					}
					if ($fForm[$j] == 'kodeorg') {
			    		$isi = getorgsap($newval);
			    	} else {
			    		$isi = $newval;
			    	}
				   $tables .= "<td id='".$fForm[$j]."_".$i."' value='".$c."'>".$isi."</td>";
				   break;
		     }
		     $tmpVal .= "##".$c;
		     if(in_array($fForm[$j],$primary)) {
			    $tmpKey .= "##".$c;
		     }
		     $j++;
	      }
	      # Edit, Delete Row
	      /* if($freezeField!=null) {
		     $tables .= "<td><img id='editRow".$i."' title='Edit' onclick=\"editRow(".$i.",'".$fieldStr."','".$tmpVal."','".$freezeField."')\"
		     class='zImgBtn' src='images/001_45.png' /></td>";
	      } else {
		     $tables .= "<td><img id='editRow".$i."' title='Edit' onclick=\"editRow(".$i.",'".$fieldStr."','".$tmpVal."')\"
		     class='zImgBtn' src='images/001_45.png' /></td>";
	      }
	      if($postTo=='null') {
		     $tables .= "<td><img id='delRow".$i."' title='Hapus' onclick=\"delRow(".$i.",'".$primaryStr."','".$tmpKey."',null,'".$table."')\"
			    class='zImgBtn' src='images/delete_32.png' /></td>";
	      } else {
		     $tables .= "<td><img id='delRow".$i."' title='Hapus' onclick=\"delRow(".$i.",'".$primaryStr."','".$tmpKey."','".$postTo."','".$table."')\"
			    class='zImgBtn' src='images/delete_32.png' /></td>";
	      } */
	      $tables .= "</tr>";
	      $i++;
       }
       if($tot==1)
       {
           $rt=count($column);
//           echo"<pre>";
//           print_r($column);
//           echo $rt;
//           echo"</pre>";
           //exit();
           $tables.="<thead><tr class=rowheader>";
           foreach($column as $brsDt)
           {
               if(empty($total[$brsDt]))
               {
                  $tables.="<td>&nbsp;</td>";
               }
               else
               {
                    $tables.="<td align=right>".number_format($total[$brsDt],2)."</td>";
               }
           }
           //$tables.="<td colspan=2>&nbsp;</td>";
           $tables.="</tr></thead>";
       }
       $tables .= "</tbody>";
       
       # Create Footer
       $tables .= "<tfoot>";
       #foreach($footer as $fName) {
       #	$tables .= "<td>".$hName."</td>";
       #}
       $tables .= "</tfoot>";
       
       # End Table
       $tables .= "</table>";
       if($printShow) {
	     //$tables .= "</div>";
       }
       $tables .= "";
       
       return $tables;
 }
 /*
 * Function masterTable
 * Fungsi untuk membuat table master
 * I : Nama DB, Nama Table, Kolom, Kondisi Query, Nama field pada Form, Halaman Tujuan, Setting untuk Header
 * O : table dalam format HTML
 * U : $table = masterTable(str,str,arr/str,str,arr,str,obj);
 * headerSetting[] = array(
 * 	'name'=>'string',
 * 	'align'=>'left/center/right',
 * 	'span'=>span_long
 * );
 */
 function masterTableGapok($dbname,$table,$column="*",$headerSetting=array(),
      $dataSetting=array(),$cond=array(),$fForm=array(),$printTo=null,
      $freezeField=null,$printShow=true,$postTo=null,$opt=array()) {
      
       #====================== Prep
       if($postTo==null) {
	      $postTo = 'null';
       }
       if($printTo==null) {
	      $printTo = 'null';
       }
       
       #====================== Select Query
       $query = "select ";
       # Column
       $colStr = "";
       if(is_array($column) and $column!=array()) {
	      for($i=0;$i<count($column);$i++) {
		     if($i==0) {
			    $query .= $column[$i];
			    $colStr .= $column[$i];
		     } else {
			    $query .= ",".$column[$i];
			    $colStr .= ",".$column[$i];
		     }
	      }
       } else {
	      $query .= "*";
       }
       
       # From Table
       $query .= " from ".$dbname.".".$table;
       
       # Condition
       if($cond!=null) {
	      $condStr = "";
	      if(is_array($cond)) {
		     $condPdf = $cond['sep']."^^";
		     unset($cond['sep']);
		     foreach($cond as $row) {
			    foreach($row as $attr=>$val) {
				   if($row==end($cond)) {
					  $condPdf .= $attr."**".$val;
					  if(is_string($val)) {
						 $condStr .= $attr."='".$val."'";
					  } else {
						 $condStr .= $attr."=".$val;
					  }
					  
				   } else {
					  $condPdf .= $attr."**".$val."~~";
					  if(is_string($val)) {
						 $condStr .= $attr."='".$val."' OR ";
					  } else {
						 $condStr .= $attr."=".$val." OR ";
					  }
				   }
			    }
		     }
	      } else {
		     $condPdf = $cond;
		     $condStr = $cond;
	      }
	      $query .= " where ".$condStr;
       } else {
	      $condPdf = null;
       }
                    global $owlPDO;
	  // echo $query;exit();
       	#======================= Execute Query
	$res=$owlPDO->query($query);
	$res->setFetchMode(PDO::FETCH_ASSOC);
	#======================= Extract Field Related
	$j = ($res)?$res->columnCount(): 0;
	$i = 0;
	$field = array();
	$fieldStr = "";
	$primary = array();
	$primaryStr = "";
	
                    for($i=0;$i<$j;$i++){
                    $test=$res->getColumnMeta($i);
                    $field[] = strtolower($test['name']);
                    $fieldStr .= "##".strtolower($test['name']);
                        if(is_array($test['flags'])){
                                    foreach($test['flags'] as $key=>$val){
                                           $val=strtolower($val);
                                           $pos = strpos($val, 'primary');
                                           if ($pos !== false) {
                                                $primary[]=$test['name'];
                                                $primaryStr .= "##".strtolower($test['name']);
                                           }
                                    }
                        }
                    } 
	
	if($fForm==array()) {
		$fForm = $field;
	}
	
	#======================= Rearrange Result and Extract Values
	$result = array();
	if($res) {
                        while($bar=$res->fetch()) {
                                $result[] = $bar;
                        }
	}
       
       #======================= Create Print
       $tables = "<fieldset><legend><b>".$_SESSION['lang']['list']." : ".$table."</b></legend>";
       $tables .= "<img src='images/pdf.jpg' title='PDF Format'
	     style='width:20px;height:20px;cursor:pointer' onclick=\"masterPDF('".$table."','".$colStr."','".$condPdf."','slave_master_pdf_2',event)\">&nbsp;";
       $tables .= "<img src='images/printer.png' title='Print Page'
	     style='width:20px;height:20px;cursor:pointer' onclick='javascript:print()'>";
       
       #======================= Create Table
       # Start Table
       if($printShow) {
	     $tables .= "<div style='height:170px;overflow:auto'>";
       }
       $tables .= "<table id='masterTable' class='sortable' cellspacing='1' border='0'>";
       
       # Create Header
       $tables .= "<thead><tr class='rowheader'>";
       if($headerSetting==null) {
	      foreach($field as $hName) {
		     $tables .= "<td>".$_SESSION['lang'][$hName]."</td>";
	      }
       } else {
	      foreach($headerSetting as $hSet) {
		     if(!isset($hSet['span'])) {
			    $hSet['span'] = '0';
		     }
		     if(!isset($hSet['align'])) {
			    $hSet['align'] = 'left';
		     }
		     $tables .= "<td colspan='".$hSet['span']."' align='".$hSet['align']."'>".$hSet['name']."</td>";
	      }
       }
       
       $tables .= "<td colspan='2'>".$_SESSION['lang']['action']."</td>";
       $tables .= "</tr></thead>";
       
       # Iterate Content
       $tables .= "<tbody id='mTabBody'>";
       $i=0;
       foreach($result as $row) {
	      $tables .= "<tr id='tr_".$i."' class='rowcontent'>";
	      $tmpVal = "";
	      $tmpKey = "";
	      $j=0;
	      foreach($row as $b=>$c) {
		     # For Tipe Tanggal
			/* print"<pre>";
			 print_r($row['karyawanid']);
			 print"</pre>";*/
		     $tmpC = explode("-",$c);
		     if(count($tmpC)==3) {
			    $c = $tmpC[2]."-".$tmpC[1]."-".$tmpC[0];
		     }
		     if(!isset($dataSetting[$b]['type'])) {
			    $dataSetting[$b]['type'] = 'default';
		     }
		     if(isset($opt[$fForm[$j]])) {
			$theVal = $opt[$fForm[$j]][$c];
		     } else {
			$theVal = $c;
		     }
		     switch($dataSetting[$b]['type']) {
			    case 'numeric':
				   $tables .= "<td id='".$fForm[$j]."_".$i."' align='right' value='".$c."'>".number_format($theVal,0)."</td>";
				   break;
			    case 'currency':
				   $tables .= "<td id='".$fForm[$j]."_".$i."' align='right' value='".$c."'>".number_format($theVal,2)."</td>";
				   break;
			    case 'string':
				 
				   $tables .= "<td id='".$fForm[$j]."_".$i."' align='left' value='".$c."'>".$theVal."</td>";
				   break;
			    default:
				if($row['karyawanid'])
				{
				  $sDt="select namakaryawan,karyawanid,lokasitugas from ".$dbname.".datakaryawan  where karyawanid='".$row['karyawanid']."'";
				   //echo "warning".$sDt;exit();
				   $qDt=$owlPDO->query($sDt);
				   $qDt->setFetchMode(PDO::FETCH_ASSOC);
                                                                                   $rDt=$qDt->fetch();
				   if($rDt['karyawanid']==$c)
				   {
					   $theVal=$rDt['namakaryawan']."[".$rDt['lokasitugas']."]";
				   }
				}
				
				   $tables .= "<td id='".$fForm[$j]."_".$i."' value='".$c."'>".$theVal."</td>";
				   break;
		     }
		     $tmpVal .= "##".$c;
		     if(in_array($fForm[$j],$primary)) {
			    $tmpKey .= "##".$c;
		     }
		     $j++;
	      }
	      # Edit, Delete Row
	      if($freezeField!=null) {
		     $tables .= "<td><img id='editRow".$i."' title='Edit' onclick=\"editRow(".$i.",'".$fieldStr."','".$tmpVal."','".$freezeField."')\"
		     class='zImgBtn' src='images/001_45.png' /></td>";
	      } else {
		     $tables .= "<td><img id='editRow".$i."' title='Edit' onclick=\"editRow(".$i.",'".$fieldStr."','".$tmpVal."')\"
		     class='zImgBtn' src='images/001_45.png' /></td>";
	      }
	      if($postTo=='null') {
		     $tables .= "<td><img id='delRow".$i."' title='Hapus' onclick=\"delRow(".$i.",'".$primaryStr."','".$tmpKey."',null,'".$table."')\"
			    class='zImgBtn' src='images/delete_32.png' /></td>";
	      } else {
		     $tables .= "<td><img id='delRow".$i."' title='Hapus' onclick=\"delRow(".$i.",'".$primaryStr."','".$tmpKey."','".$postTo."','".$table."')\"
			    class='zImgBtn' src='images/delete_32.png' /></td>";
	      }
	      $tables .= "</tr>";
	      $i++;
       }
       $tables .= "</tbody>";
       
       # Create Footer
       $tables .= "<tfoot>";
       #foreach($footer as $fName) {
       #	$tables .= "<td>".$hName."</td>";
       #}
       $tables .= "</tfoot>";
       
       # End Table
       $tables .= "</table>";
       if($printShow) {
	     $tables .= "</div>";
       }
       $tables .= "</fieldset>";
       
       return $tables;
 }
?>