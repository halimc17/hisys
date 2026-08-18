<?php
/*
 * Function makeForm
 * Fungsi untuk membuat form standard
 * I : string id, string url action tanpa .php, array of elements, form method
 * O : form dalam format HTML
 * U : $form = makeForm(str,str,arr,str);
 */
 function makeForm($id,$action,$elements=array(),$method="POST") {
	# Start Form
	$form = "<form id='".$id."' name='".$id."' method='".$method."' action='".$action.".php'>";
	
	# Show Form Elements
	foreach($elements as $el) {
		$form .= $el;
	}
	
	# End Form
	$form .= "</form>";
	
	return $form;
 }
 
 /*
  * Function genElement
  * Fungsi untuk generate list of element dalam layout tabel
  * I : array of array of elements
  * O : element dalam layout tabel
  * U : $els = genElement(arr of arr)
  */
 function genElement($elements=null,$padding='1') {
       if($elements==null){
	      return NULL;
       } else {
	      $els = "<table border='0' cellspacing='0' cellpadding='".$padding."'>";
	      $maxL = 0;
	      foreach($elements as $key=>$row1) {
		     if(count($row1)>$maxL) {
			    $maxL = count($row1);
		     }
		     $els .= "<tr>";
		     foreach($row1 as $row2) {
			    if($key==='submit' or $key==='button' or $key==='btn') {
				   $els .= "<td align='left' colspan='".$maxL."'>".$row2."</td>";
			    } else {
				   $els .= "<td align='left'>".$row2."</td>";
			    }
		     }
		     $els .= "</tr>";
	      }
	      $els .= "</table>";
	      
	      return $els;
       }
 }
 
 /*
  * Function genElTitle
  * Fungsi untuk generate list of element dalam layout tabel
  * I : array of array of elements
  * O : element dalam layout tabel
  * U : $els = genElement(arr of arr)
  */

  /* 
 function genElTitle($title='Form',$elements=null,$padding='1') {
       if($elements==null){
	      return NULL;
       } else {
	      $els = "<fieldset style='float:left'><legend><b>".$title."</b></legend>";
	      $els .= "<table border='0' cellspacing='0' cellpadding='".$padding."'>";
	      $maxL = 0;
	      foreach($elements as $key=>$row1) {
		     if(count($row1)>$maxL) {
			    $maxL = count($row1);
		     }
		     $els .= "<tr>";
		     foreach($row1 as $row2) {
			    if($key==='submit' or $key==='button' or $key==='btn') {
				   $els .= "<td align='left' colspan='".$maxL."'>".$row2."</td>";
			    } else {
				   $els .= "<td align='left'>".$row2."</td>";
			    }
		     }
		     $els .= "</tr>";
	      }
	      $els .= "</table></fieldset>";
	      
	      return $els;
       }
 }
 */
 
	// Hasil modifikasi indra tanggal 18 Jan 2016
	function genElTitle($title='Form',$elements=null,$padding='1') {
      if($elements==null){
     return NULL;
      } else {
     $els = "<fieldset style='float:left'><legend><b>".$title."</b></legend>";
     $els .= "<table border='0' cellspacing='0' cellpadding='".$padding."'>";
     $maxL = 0;
 
     foreach($elements as $key=>$row1) {
		if(count($row1)>$maxL) {
	   $maxL = count($row1);
		}
		$els .= "<tr>";
		foreach($row1 as $row2) {


	   if($key==='submit' or $key==='button' or $key==='btn') {
	  $els .= "<td></td><td></td><td align='left' >".$row2."</td>";
	   } else {
		@$x++;
		if($x%2!=0){
		$els .= "<td align='left'>".$row2."</td>";
		$els .= "<td align='left'>:</td>";
		}else{
		$els .= "<td align='left'>".$row2."</td>";
		}
	}
		}
		$els .= "</tr>";
		 }
		 $els .= "</table></fieldset>";
		 
		 return $els;
		  }
	}
	
 
  /*
  * Function genElementMultiDim
  * Fungsi untuk generate list of element dalam layout tabel
  * I : array of array of elements
  * O : element dalam layout tabel
  * U : $els = genElement(arr of arr)
  */
 function genElementMultiDim($title='Form',$elements,$width=1,$height=null,$padding='1',$plain=false,$floatstyle=left) {
       #======== Extract Button =============
       if(isset($elements['submit']) and $elements['submit']!=null) {
	      $button = $elements['submit'][0];
       }
       if(isset($elements['button']) and $elements['button']!=null) {
	      $button = $elements['button'][0];
       }
       if(isset($elements['btn']) and $elements['btn']!=null) {
	      $button = $elements['btn'][0];
       }
       unset($elements['submit']);
       unset($elements['button']);
       unset($elements['btn']);
       
       #========= Calculate Width-Height ============
       $numEls = count($elements);
       
       # Case 1 : h = null
       if($height==null) {
	      $height = ceil($numEls/$width);
       }
       
       # Case 2 : w*h < numEls
       while($width*$height < $numEls) {
	      $height++;
       }
       
       #========= Rearrange Array ===============
       $resEls = array();
       $w = 0; $h = 0;
       foreach($elements as $el) {
	      if($h==$height) {
		     $w++;$h=0;
	      }
	      $resEls[$h][$w] = $el;
	      $h++;
       }
       #echo "<pre>";
       #print_r($button);
       
       #=========== Create Layout ===========
       $els = "";
       if(!$plain) {
	      $els .= "<fieldset style='float:".$floatstyle."'><legend id='title_Form'><b>".$title."</b></legend>";
       }
       $els .= "<div id='".$title."'><table border='0' cellspacing='0' cellpadding='".$padding."'>";
       $maxL = 0;
       foreach($resEls as $h=>$in1) {
	      $els .= "<tr>";
	      foreach($in1 as $w=>$content) {
		     foreach($content as $ni) {
			    $els .= "<td style='padding-right:20px;font-size:12px'>".$ni."</td>";
		     }
	      }
	      $els .= "</tr>";
       }
       # Add Button
       if(isset($button)) {
	      $els .= "<tr><td colspan='".($width*2)."'>".$button."</td></tr>";
       }
       $els .= "</table></div>";
       if(!$plain) {
	      $els .= "</fieldset>";
       }
       
       return $els;
 }
 
/*
 * Function genFormBtnConfig
 * Fungsi untuk generate set of button untuk form yang telah dioptimasi
 * @param	string	$field		List Field Element yang akan diambil (##field1##field2)
 * @param	string	$table		Nama Table
 * @param	string	$id			List ID yang tidak bisa diedit (##id1##id2)
 * @param	array	$config		Optional Parameter
 * @return	HTML				Button Add, Edit dan Cancel
 */
function genFormBtnConfig($field,$table,$id,$config = array()) {
	$page		=isset($config['page'])? $config['page']: null;
	$freeze		=isset($config['freeze'])? $config['freeze']: null;
	$empty		=isset($config['empty'])? $config['empty']: null;
	$pageEdit	=isset($config['pageEdit'])? $config['pageEdit']: null;
	$emptyField	=isset($config['emptyField'])? $config['emptyField']: '##';
	$disabled	=isset($config['disabled'])? $config['disabled']: '##';
	$opt		=isset($config['opt'])? $config['opt']: array();
	$barang		=isset($config['barang'])? $config['barang']: array();
	
	return genFormBtn($field,$table,$id,$page,$freeze,$empty,$pageEdit,
					  $emptyField,$disabled,$opt,$barang);
}
 
/*
 * Function genFormBtn
 * Fungsi untuk generate set of button untuk form
 * I : 
 * O : standar button untuk form
 * U : $ = genFormBtn()
 */
function genFormBtn($field,$table,$id,$page=null,$freeze=null,$empty=null,
					$pageEdit=null,$emptyField='##',$disabled='##',$opt='{}',$barang=array()) {
	if($empty==null) {
		$empty = ",false";
	} else {
		$empty = ",true";
	}
	
	// Default Value
	$page = (!is_null($page))? "'".$page."'": 'null';
	$freeze = (!is_null($freeze))? "'".$freeze."'": 'null';
	$pageEdit = (!is_null($pageEdit))? "'".$pageEdit."'": 'null';
	
	// Untuk field dengan type searchBarang
	if(!empty($barang)) {
		if(!is_array($opt)) {
			$opt = json_decode($opt);
		}
		$opt->searchBarang = array();
		foreach($barang as $row) {
			$opt->searchBarang[] = $row;
		}
		$opt = json_encode($opt);
	}
	
	if(is_array($opt)) {
		$opt = json_encode($opt);
		$opt = htmlspecialchars(stripslashes($opt));
	} else {
		$opt = htmlspecialchars(stripslashes($opt));
	}
	
	$formBtn = makeElement('add','btn',$_SESSION['lang']['save'],array('onclick'=>"addData('".$field."','".$id."','".$table."',".$page.",".$freeze.$empty.",'".$emptyField."','".$opt."','".$disabled."')")).
			   makeElement('edit','btn',$_SESSION['lang']['save'],array('style'=>'display:none','onclick'=>"editData('".$field."','".$id."','".$table."',".$pageEdit.",'".$opt."')")).
			   makeElement('cancel','btn',$_SESSION['lang']['cancel'],array('onclick'=>"clearData('".$field."','".$disabled."')"));
	
    $formBtn .= makeElement('currRow','hidden','0');
    return $formBtn;
}
 
/*
 * Function makeElement
 * Fungsi untuk membuat element form standard
 * I : string id, string element type, string value, array option, array atribut
 * O : element form dalam format HTML
 * U : $el = makeElement(str,str,str,arr,arr);
 */
function makeElement($id,$type,$value="",$attr=array(),$options=array(),
					 $nameValue=null,$targetSatuan=null,$targetHarga=null,
					 $idGudang=null,$targetSaldo=null) {
	# Init
	$el = "";
	is_null($targetSatuan) ? $targetSatuan='' : null;
	is_null($targetHarga) ? $targetHarga='' : null;
	
	# Type?
	switch($type) {
		# Tipe Label
		case 'label' :
			$el .= "<label for='".$id."'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			$el .= ">".$value."</label>";
			break;
			
		# Tipe Date
		case 'tanggal' :
		case 'date' :
			$el .= "<input id='".$id."' name='".$id."' class='myinputtext' type='text'";
			$el .= " onmousemove='setCalendar(this.id)' readonly='readonly' value='".$value."'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			$el .= " style='cursor:pointer' />";
			break;
		   
		# Tipe Range Period
		case 'rangedate' :
		case 'period' :
		case 'periode' :
			$el .= "<input id='".$id."_from' name='".$id."_from' class='myinputtext' type='text'";
			$el .= " onmousemove='setCalendar(this.id)' readonly='readonly' value='".$value."'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			$el .= " style='cursor:pointer' />";
			$el .= " s/d ";
			$el .= "<input id='".$id."_until' name='".$id."_until' class='myinputtext' type='text'";
			$el .= " onmousemove='setCalendar(this.id)' readonly='readonly' value='".$value."'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			$el .= " style='cursor:pointer' />";
			break;
		
		// Tipe Textarea
		case 'textarea':
			$el .= "<textarea id='".$id."' name='".$id."'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			$el .= ">".$value."</textarea>";
			break;
		
		# Tipe Text Standard
		case 'txt' :
		case 'text' :
			$el .= "<input id='".$id."' name='".$id."' class='myinputtext' type='text'";
			$el .= " onkeypress='return tanpa_kutip(event)' value='".$value."'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			$el .= "/>";
			break;
		
		# Tipe Text Uppercase
		case 'textupper' :
			$el .= "<input id='".$id."' name='".$id."' class='myinputtextuppercase' type='text' value='".$value."'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			$el .= "/>";
			break;
			
		# Tipe Text Lowercase
		case 'textlower' :
			$el .= "<input id='".$id."' name='".$id."' class='myinputtextlowercase' type='text' value='".$value."'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			$el .= "/>";
			break;
	    # Tipe file Standard
		case 'file' :
			$el .= "<input id='".$id."' name='".$id."' class='mybutton' type='file'";
			$el .= "  value='".$value."'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			$el .= "/>";

			$el .= "</td><tr><td><img id='".$id."_refresh' ";
			$el .= "class=resicon src='images/refresh.png' style='position:relative;top:5px' onclick='load_".$id."()'>";

			$el .= "</td><td><button id='".$id."' name='".$id."' class='mybutton'";
			$el .= " onclick='save_".$id."()'; >Upload File</button>";

			

			$el .= "</td><tr><td colspan=2><div id=dataupload_".$id.">";

			$el.= "<table cellspacing=1 cellpadding=0 width=100%>";
	        $el.= "<thead>";
	        $el.= "<tr>";
	        $el.= "<th>File Type</th>";
	        $el.= "<th>Kriteria</th>";
	        $el.= "<th>File Name</th>";
	        $el.= "<th>".$_SESSION['lang']['action']."</th>";
	        $el.= "<tr>";
	        $el.= "</thead>";
	        $el.= "<tbody id='tbody_".$id."'>";
	        //echo "<script language=javascript1.2 src='js/keu_kasbank.js?v=1.5'>load_filex</script>";
	        $el.="</tbody></table></div>";

			break;
			
		# Tipe Text Number/Numeric
		case 'textnum' :
		case 'textnumber' :
		case 'textnumeric' :
			$el .= "<input id='".$id."' name='".$id."' class='myinputtextnumber' ";
			$el .= "onkeypress='return angka_doang(event)' type='text' value='".$value."'";
			if(is_array($attr) and $attr!=array()) {
			   foreach($attr as $key=>$row) {
				  $el .= " ".$key."=\"".$row."\"";
			   }
			}
			$el .= "/>";
			break;
		# Tipe Text Number/Numeric wint minus sign for spesific cash/bank transaktion
		case 'textnumw-' :
			$el .= "<input id='".$id."' name='".$id."' class='myinputtextnumber' ";
			$el .= "onkeypress='return tanpa_kutip_dan_sepasi(event)' type='text' value='".$value."'";
			if(is_array($attr) and $attr!=array()) {
			   foreach($attr as $key=>$row) {
				  $el .= " ".$key."=\"".$row."\"";
			   }
			}
			$el .= "/>";
			break;
		
		# Tipe Text Number/Numeric with Combo Plus/Minus
		case 'textnumplusminus' :
			$el .= "<select id='".$id."_pm' name='".$id."_pm'>";
			$el .= "<option value='+'>+</option>";
			$el .= "<option value='-'>-</option>";
			$el .= "</select>";
			$el .= "<input id='".$id."' name='".$id."' class='myinputtextnumber' ";
			$el .= "onkeypress='return angka_doang(event)' type='text' value='".$value."'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			$el .= "/>";
			break;
	    
		# Tipe Text Number/Numeric dengan Satuan(Span)
		case 'textnumwsatuan' :
			$el .= "<input id='".$id."' name='".$id."' class='myinputtextnumber' ";
			$el .= "onkeypress='return angka_doang(event)' type='text' value='".$value."'";
			if(is_array($attr) and $attr!=array()) {
			   foreach($attr as $key=>$row) {
				  $el .= " ".$key."=\"".$row."\"";
			   }
			}
			$el .= "/>&nbsp;";
			$el .= "<input id='".$id."_satuan' name='".$id."_satuan' class='myinputtext' type='text'";
			$el .= " disabled='disabled' value='".$nameValue."' style='width:30px'";
			$el .= "/>";
			break;
		
                        
		case 'selectwithsearch' : 
		//print_r($attr);exit("Error");
			$el .= "<select id='".$id."' name='".$id."'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					if($key=='onclick') {
						$el .= "";
					}else{
						$el .= " ".$key."=\"".$row."\"";
					}
					
				}
			}
			$el .=">";
			foreach($options as $val=>$name) {
				if($value==$val) {
					$el .= "<option value='".$val."' selected>".$name."</option>";
				} else {
					$el .= "<option value='".$val."'>".$name."</option>";
				}
			}
			$el .= "</select>";
			
			$el .= "<img id='".$id."_find' ";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					if($key=='onclick') {
						$el .= " ".$key."=\"".$row."\"";
					}
				}
			}
			$el .= "class=resicon src='images/onebit_02.png' style='position:relative;top:5px'>";
		break;       
                        
		# Tipe Text dengan Icon Search
		case 'textsearch' :
			$el .= "<input id='".$id."' name='".$id."' class='myinputtext' ";
			$el .= "onkeypress='return tanpa_kutip(event)' type='text' value='".$value."'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					if($key!='onclick') {
						$el .= " ".$key."=\"".$row."\"";
					}
				}
			}
			$el .= "/>";
			$el .= "<img id='".$id."_find' ";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					if($key=='onclick') {
						$el .= " ".$key."=\"".$row."\"";
					}
				}
			}
			$el .= "class=resicon src='images/onebit_02.png' style='position:relative;top:5px'>";
			break;
		
		# Tipe Select
		case 'select' :
		case 'dropdown' :
			$el .= "<select id='".$id."' name='".$id."'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			$el .=">";
			foreach($options as $val=>$name) {
				if($value==$val) {
					$el .= "<option value='".$val."' selected>".$name."</option>";
				} else {
					$el .= "<option value='".$val."'>".$name."</option>";
				}
			}
			$el .= "</select>";
			break;
                        
                        
		
		# Tipe Select with search
		case 'selectsearch' :
			$el .= "<select id='".$id."' name='".$id."'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			$el .=">";
			foreach($options as $val=>$name) {
				if($value==$val) {
					$el .= "<option value='".$val."' selected>".$name."</option>";
				} else {
					$el .= "<option value='".$val."'>".$name."</option>";
				}
			}
			$el .= "</select>";
			$el .= "<img id='".$id."_find' onclick='z.elSearch(\"".$id."\",event";
			if(isset($parentEl) and !is_null($parentEl)) {
				$el .= ',"'.$parentEl.'"';
			}
			$el .= ")' ";
			$el .= "class=resicon src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>";
			break;
		
		# Tipe Debet Kredit
		case 'dk' :
		   $el .= "<select id='".$id."_dk' name='".$id."_dk' style='width:70px'>";
		   $el .= "<option value='D' selected>".$_SESSION['lang']['debet']."</option>";
		   $el .= "<option value='K' selected>".$_SESSION['lang']['kredit']."</option>";
		   $el .= "</select>";
		   $el .= "<input id='".$id."_nilai' name='".$id."_nilai' class='myinputtextnumber' ";
		   $el .= "onkeypress='return angka_doang(event)' type='text' value='".$value."'";
		   if(is_array($attr) and $attr!=array()) {
			  foreach($attr as $key=>$row) {
				 $el .= " ".$key."=\"".$row."\"";
			  }
		   }
		   $el .= "/>";
		   break;
			
		# Tipe Checkbox
		case 'chk' :
		case 'check' :
		case 'checkbox' :
			$el .= "<input id='".$id."' name='".$id."' type='checkbox'";
			$el .= " value='".$value."'";
					if($value==1) $el .= " checked";
			if(is_array($attr) and $attr!=array()) {
			   foreach($attr as $key=>$row) {
				  $el .= " ".$key."=\"".$row."\"";
			   }
			}
			$el .= "/>";
			break;
		
		# Tipe Multi Checkbox
		case 'multichk' :
		case 'multicheck' :
		case 'multicheckbox' :
			$el .= "<input id='".$id."' name='".$id."' type='hidden' value='".
				$value."' />";
			$el .= "<input id='".$id."_size' name='".$id."' type='hidden' ".
				"value='".count($options)."' />";
				
			foreach($options as $val=>$name) {
				$el .= "<input id='".$id.$val."' name='".$id.$val."' type='checkbox'";
				$el .= " value='0' onchange='updateMultiChk(\"".$id."\")'/>";
				$el .= "<label for='".$id.$val."'>".$name."</label>";
			}
			break;
		
		# Tipe Password
		case 'password' :
		case 'pwd' :
			$el .= "<input id='".$id."' name='".$id."' type='password' value='".$value."'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			$el .= "/>";
			break;
	      
		# Tipe Button
		case 'button' :
		case 'btn' :
			$el .= "<button id='".$id."' name='".$id."' class='mybutton'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			$el .= ">".$value."</button>";
			break;
	      
		# Tipe Submit
		case 'submit' :
			$el .= "<input id='".$id."' name='".$id."' type='submit' value='".$value."'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			$el .= "/>";
			break;
	    
		# Tipe Hidden
		case 'hidden' :
		case 'hid' :
			$el .= "<input id='".$id."' name='".$id."' type='hidden' value='".$value."'";
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			$el .= "/>";
			break;
	      
		# Tipe Search Barang
		case 'searchBarang':
			$el .= "<input id='".$id."' name='".$id."' class='myinputtext' ";
			$el .= "type='text' style='width:50px' value='".$value."' disabled />&nbsp;";
			$el .= "<input id='".$id."_name' name='".$id."_name' class='myinputtext' ";
			$el .= "value='".$nameValue."' type='text' style='width:150px' disabled />";
			$el .= "<img id='".$id."' name='".$id."' onclick=\"getInvName(event,'".$id."','".$targetSatuan."','".$targetHarga."')\" ";
			$el .= "class=zImgBtn src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>";
			break;
		
		# Tipe Search Barang dari Gudang, return beserta saldo
		# Backup fungsi aselinya------------------------------
		// case 'searchBarangGudang':
			// $el .= "<input id='".$id."' name='".$id."' class='myinputtext' ";
			// $el .= "type='text' style='width:65px' value='".$value."' disabled />&nbsp;";
			// $el .= "<input id='".$id."_name' name='".$id."_name' class='myinputtext' ";
			// $el .= "value='".$nameValue."' type='text' style='width:100px' disabled />";
			// $el .= "<button id='".$id."' name='".$id."' class='mybutton' ";
			// if(isset($attr['disabled']) and $attr['disabled']=='disabled') {
			   // $el .= "disabled ";
			// }
			// $el .= "onclick=\"getInvNameSaldo(event,'".$id."','".$idGudang."','".$targetSaldo."')\"/>".$_SESSION['lang']['find']."</button>";
			// break;
		
		case 'searchBarangGudang':
			$el .= "<input id='".$id."' name='".$id."' class='myinputtext' ";
			$el .= "type='text' style='width:65px' value='".$value."' disabled />&nbsp;";
			$el .= "<input id='".$id."_name' name='".$id."_name' class='myinputtext' ";
			$el .= "value='".$nameValue."' type='text' style='width:100px' disabled />";
			$el .= "<img id='".$id."' name='".$id."' onclick=\"getInvNameSaldo(event,'".$id."','".$idGudang."','".$targetSaldo."')\" ";
			$el .= "class=zImgBtn src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>";
			break;
	      
		# Tipe Search Kegiatan
		case 'searchKegiatan':
		case 'searchKeg':
			$el .= "<input id='".$id."' name='".$id."' class='myinputtext' ";
			$el .= "type='text' style='width:50px' value='".$value."' disabled />&nbsp;";
			$el .= "<input id='".$id."_name' name='".$id."_name' class='myinputtext' ";
			$el .= "value='".$nameValue."' type='text' style='width:150px' disabled />";
			$el .= "<button id='".$id."' name='".$id."' class='mybutton' ";
			if(isset($attr['disabled']) and $attr['disabled']=='disabled') {
			   $el .= "disabled ";
			}
			$el .= "onclick=\"getSearch(event,'".$id."','kegiatan')\"/>".$_SESSION['lang']['find']."</button>";
			break;
	      
		# Tipe Search Asset
		case 'searchAsset':
			$el .= "<input id='".$id."' name='".$id."' class='myinputtext' ";
			$el .= "type='text' style='width:50px' value='".$value."' disabled />&nbsp;";
			$el .= "<input id='".$id."_name' name='".$id."_name' class='myinputtext' ";
			$el .= "value='".$nameValue."' type='text' style='width:150px' disabled />";
			$el .= "<button id='".$id."' name='".$id."' class='mybutton' ";
			if(isset($attr['disabled']) and $attr['disabled']=='disabled') {
			   $el .= "disabled ";
			}
			$el .= "onclick=\"getSearch(event,'".$id."','asset')\"/>".$_SESSION['lang']['find']."</button>";
			break;
	      
		# Tipe Search Customer
		case 'searchCustomer':
		case 'searchCust':
			$el .= "<input id='".$id."' name='".$id."' class='myinputtext' ";
			$el .= "type='text' style='width:50px' value='".$value."' disabled />&nbsp;";
			$el .= "<input id='".$id."_name' name='".$id."_name' class='myinputtext' ";
			$el .= "value='".$nameValue."' type='text' style='width:150px' disabled />";
			$el .= "<button id='".$id."' name='".$id."' class='mybutton' ";
			if(isset($attr['disabled']) and $attr['disabled']=='disabled') {
				$el .= "disabled ";
			}
			$el .= "onclick=\"getSearch(event,'".$id."','customer')\"/>".$_SESSION['lang']['find']."</button>";
			break;
	      
		# Tipe Search Supplier
		case 'searchSupplier':
		case 'searchSupl':
			$el .= "<input id='".$id."' name='".$id."' class='myinputtext' ";
			$el .= "type='text' style='width:50px' value='".$value."' disabled />&nbsp;";
			$el .= "<input id='".$id."_name' name='".$id."_name' class='myinputtext' ";
			$el .= "value='".$nameValue."' type='text' style='width:150px' disabled />";
			$el .= "<button id='".$id."' name='".$id."' class='mybutton' ";
			if(isset($attr['disabled']) and $attr['disabled']=='disabled') {
			   $el .= "disabled ";
			}
			$el .= "onclick=\"getSearch(event,'".$id."','supplier')\"/>".$_SESSION['lang']['find']."</button>";
			break;
	    
		# Tipe Search Segment
		case 'searchSegment':
			$el .= "<input id='".$id."' name='".$id."' class='myinputtext' ";
			$el .= "type='text' style='width:65px' value='".$value."' disabled />&nbsp;";
			$el .= "<input id='".$id."_name' name='".$id."_name' class='myinputtext' ";
			$el .= "value='".$nameValue."' type='text' style='width:118px' disabled />";
			$el .= "<img id='".$id."' name='".$id."' onclick=\"getSearch(event,'".$id."','segment')\" ";
			$el .= "class=zImgBtn src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>";
			break;
			
		# Tipe Search Segment
		case 'searchSegment2':
			$el .= "<input id='".$id."' name='".$id."' class='myinputtext' ";
			$el .= "type='text' style='width:65px' value='".$value."' disabled />&nbsp;";
			$el .= "<input id='".$id."_name' name='".$id."_name' class='myinputtext' ";
			$el .= "value='".$nameValue."' type='text' style='width:118px' disabled />";
			$el .= "<img id='".$id."' name='".$id."' onclick=\"getSearch(event,'".$id."','segment')\" ";
			$el .= "class=zImgBtn src='images/onebit_02.png' style='position:relative;top:3px;left:3px;'>";
			break;
		
		case 'jammenit':
			$optJam = array();$optMenit = array();
			$tmpVal = explode(":",$value);
			$valueJam = $tmpVal[0];
			if(count($tmpVal)>1) {
			   $valueMenit = $tmpVal[1];
			} else {
			   $valueMenit = "00";
			}
			for($i=0;$i<60;$i++) {
			   if($i<24) {
				  $optJam[addZero($i,2)] = addZero($i,2);
			   }
			   $optMenit[addZero($i,2)] = addZero($i,2);
			}
			# Jam
			$el .= "<select id='".$id."_jam' name='".$id."'_jam";
			//buka tambahan atribut
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			//tutup tambahan atribut
			$el .=">";
			foreach($optJam as $val) {
			   if($valueJam==$val) {
				  $el .= "<option value='".$val."' selected>".$val."</option>";
			   } else {
				  $el .= "<option value='".$val."'>".$val."</option>";
			   }
			}
			$el .= "</select>";
			$el .= ":";
			# Menit
			$el .= "<select id='".$id."_menit' name='".$id."'_menit";
			//buka tambahan atribut
			if(is_array($attr) and $attr!=array()) {
				foreach($attr as $key=>$row) {
					$el .= " ".$key."=\"".$row."\"";
				}
			}
			//tutup tambahan atribut
			$el .=">";
			foreach($optMenit as $val) {
			   if($valueMenit==$val) {
				  $el .= "<option value='".$val."' selected>".$val."</option>";
			   } else {
				  $el .= "<option value='".$val."'>".$val."</option>";
			   }
			}
			$el .= "</select>";
			break;
		
	    case 'bulantahun':
			# Make Bulan
			$optBulan = array();
			for($i=1;$i<13;$i++) {
			   $optBulan[$i] = numToMonth($i,substr($_SESSION['language'],0,1),'long');
			}
			
			# Value
			$tmpVal = explode("-",$value);
			$valueBulan = $tmpVal[0];
			if(count($tmpVal)>1) {
			   $valueTahun = $tmpVal[1];
			} else {
			   $valueTahun = date('Y');
			}
			
			# Bulan
			$el .= "<select id='".$id."_bulan' name='".$id."'_bulan";
			$el .=">";
			foreach($optBulan as $val=>$text) {
			   if($valueBulan==$val) {
				  $el .= "<option value='".$val."' selected>".$text."</option>";
			   } else {
				  $el .= "<option value='".$val."'>".$text."</option>";
			   }
			}
			$el .= "</select>";
			
			# Tahun
			$el .= "<input id='".$id."_tahun' name='".$id."_tahun' class='myinputtextnumber' ";
			$el .= "onkeypress='return angka_doang(event)' type='text' value='".$valueTahun."' ";
			$el .= "maxlength='4' style='width:40px'";
			$el .= "/>";
			break;
		default :
			break;
	}
	return $el;
}
 
/**
 * Function makeOption
 * Fungsi untuk membentuk array untuk acuan drop down field
 * I : Nama DB, Nama Table, Kolom dalam format 'kolom a, kolom b', Jenis Option
 * O : array result
 * U : $option = makeOption(str,str,str,str);
 * mode = 0 : nama => sort by nama
 * mode = 1 : nama(kode) => sort by nama
 * mode = 2 : kode - nama => sort by kode
 */
function makeOption($dbname,$tableName,$column,$where=null,$mode='0',$empty=false, $stringMode=false) {
	global $owlPDO;
	
	# Get Data
	$cols = explode(',',$column);
	
	# Init Options
	if($empty==true) {
		$option = array(''=>'');
	} else {
		$option = array();
	}
	
	# Iterate make Option
	switch($mode) {
		case '1':
			$query = selectQuery($dbname,$tableName,$column,$where,$cols[1],true);
			$data = fetchData($query);
			foreach($data as $row) {
			   $option[$row[$cols[0]]] = $row[$cols[1]]." (".$row[$cols[0]].")";
			}
			break;
		case '2':
			$query = selectQuery($dbname,$tableName,$column,$where,$cols[0],true);
			$data = fetchData($query);
			foreach($data as $row) {
			   $option[$row[$cols[0]]] = $row[$cols[0]]." - ".$row[$cols[1]];
			   /*echo "<pre>";
			   print_r($option);
			   echo "</pre>";*/
			}
			break;
		case '3':
			foreach($column as $row=>$isi) {
			   $option[$row] = $isi;
			}
			break;
		case '4':
			$query = selectQuery($dbname,$tableName,$column,$where,$cols[1],true);
					$data = fetchData($query);
			foreach($data as $row) {
			   $option[$row[$cols[0]]] = $row[$cols[1]]." (".$row[$cols[2]].")";
			}
			break;
		case '5':
			$query = selectQuery($dbname,$tableName,$column,$where,$cols[1],true);
					$data = fetchData($query);
			foreach($data as $row) {
			   $option[$row[$cols[0]]] = $row[$cols[1]]." - ".$row[$cols[2]];
			}
			break;
		case '6':
			$query = selectQuery($dbname,$tableName,$column,$where,$cols[3],true);
					$data = fetchData($query);
			foreach($data as $row) {
			   $option[$row[$cols[0]]] = $row[$cols[3]]." - ".$row[$cols[1]]." (".$row[$cols[2]].")";
			}
			break;
		case'7':
			$query = $owlPDO->query(" SHOW COLUMNS FROM `".$dbname."`.`".$tableName."` LIKE '".$cols[0]."' ");
                                                            $query->setFetchMode(PDO::FETCH_NUM);
			$row = $query->fetch();
			#extract the values
			#the values are enclosed in single quotes
			#and separated by commas
			$regex = "/'(.*?)'/";
			preg_match_all( $regex , $row[1], $enum_array );
			$enum_fields = array();
			foreach($enum_array[1] as $row) {
				$enum_fields[$row] = $row;
			}
			foreach($enum_fields as $row){
				$option[$row] = $row;
			}
			break;
		default:
			$query = selectQuery($dbname,$tableName,$column,$where,$cols[1],true);
			//print_r($query);
			$data = fetchData($query);
			foreach($data as $row) {
			   $option[$row[$cols[0]]] = $row[$cols[1]];
			}
			break;
	}
	if($stringMode) {
		$strOpt = "";
		foreach($option as $key=>$value) {
			$strOpt .= "<option value='".$key."'>".$value."</option>";
		}
		return $strOpt;
	} else {
		return $option;
	}
}
 
 /*
 * Function makeOptionAkun
 * Fungsi untuk membentuk array untuk acuan drop down field akun
 * I : Nama DB, Kondisi, isEmpty
 * O : array result
 * U : $option = makeOption(str,str,str,str);
 */
 function makeOptionAkun($dbname,$where=null,$empty=false) {
       # Init Options
       if($empty==true) {
	      $option = array(''=>'');
       } else {
	      $option = array();
       }
       
       # Get Kelompok
       $optKel = makeOption($dbname,'keu_5akun','noakun,namaakun',"length(trim(noakun))=3");
       
       if(!is_null($where)) {
	      $where = "detail=1 and ".$where;
       } else {
	      $where = "detail=1";
       }
       $query = selectQuery($dbname,'keu_5akun','noakun,namaakun',$where,'noakun',true);
       $data = fetchData($query);
       foreach($data as $row) {
	      if(isset($optKel[substr($row['noakun'],0,3)])) {
		     $option[$row['noakun']] = $row['noakun']." - ".$row['namaakun'].
			    " (".$optKel[substr($row['noakun'],0,3)].")";
	      }
       }
       
       return $option;
 }
 
/*
 * Function optionMonth
 * Fungsi yang membentuk array month untuk acuan drop down field
 * I : kode bahasa, format (long/short)
 * O : array result
 * U : $month = optionMonth(str,str);
 */
 function optionMonth($langcode='E',$format='short') {
       # Init
       $month = array();
       
       # Iterate
       for($i=1;$i<=12;$i++) {
	      $month[$i] = numToMonth($i,$langcode,$format);
       }
       
       return $month;
 }

/*
 * Function addZero
 * Fungsi menambahkan '0' di depan angka sesuai kebutuhan
 * I : angka, jumlah maksimum
 * O : angka hasil (string)
 * U : $num = addZero(str,str);
 */
 function addZero($tmpNum,$maxNum) {
       $len = $maxNum;
       while(strlen($tmpNum)<$len) {
	      $tmpNum = '0'.$tmpNum;
       }
       
       return $tmpNum;
 }
 
/*
 * Function optionNum
 * Fungsi yang membentuk array nomor untuk acuan drop down field
 * I : jumlah element array
 * O : array result
 * U : $num = optionNum(int);
 */
 function optionNum($arrEl) {
       $len = strlen($arrEl);
       $strArr = $len;
       $num = array();
       for($i=0;$i<$arrEl;$i++){
	      $tmpI = strval($i);
	      $tmpNo = addZero($tmpI,$strArr);
	      $num[$i] = $tmpNo;
       }
       
       return $num;
 }
 
 /*
 * Function makeFieldset
 * Fungsi untuk membentuk fieldset
 * I : judul, id div,content, bold title (T/F)
 * O : fieldset
 * U : echo makeFieldset($title,$content,true);
 */
 function makeFieldset($title,$id,$cont=null,$bold=null) {
       $fs = "<fieldset>";
       $fs .= "<legend>";
       !is_null($bold) ? $fs .= "<b>" : null;
       $fs .= $title;
       !is_null($bold) ? $fs .= "</b>" : null;
       $fs .= "</legend>";
       $fs .= "<div id='".$id."'>";
       $fs .= $cont;
       $fs .= "</div></fieldset>";
       
       return $fs;
 }
 
 /*
 * Function getFirstKey
 * Fungsi mengambil key pertama dari array
 * I : array
 * O : key of array
 */
 function getFirstKey($arr) {
       return key($arr);
 }
 
 /*
 * Function getFirstContent
 * Fungsi mengambil content pertama dari array
 * I : array
 * O : content of array
 */
 function getFirstContent($arr) {
       return reset($arr);
 }
?>