<?php
//Example : sdm_slave_programtraining.php
//author : Atwal
class Paging{
	// Fungsi Posisi limit untuk setelah halaman berubah
	function cariPosisi($batas,$halaman){
		$hal = $halaman;
		if(empty($hal)){
			$posisi=0;
			$hal=1;
		}else{
			$posisi = ($hal-1) * $batas;
		}
		return $posisi;
	}
	
	// Fungsi untuk menghitung total halaman
	function jumlahHalaman($jmldata, $batas){
		$jmlhalaman = ceil($jmldata/$batas);
		return $jmlhalaman;
	}
	// Fungsi memasukkan Attribute
	function addAttribute($button,$attr,$disabled){
		$button = strtolower($button);
		$result ="";
		$style="";
		if($disabled == false){
			$attr = '';
			$style='opacity:0.6;filter: gray;-webkit-filter: grayscale(1);filter: grayscale(1);';
		}
		switch ($button){
			case 'first':
				$result	= '<a '.$attr.' style="float:left;margin-right:5px;height:19px;'.$style.'"><img src="images/skyblue/first.png" size></a>';
			break;
			case 'prev':
				$result	= '<a '.$attr.' style="float:left;margin-right:5px;height:19px;'.$style.'"><img src="images/skyblue/prev.png"></a>&nbsp;';
			break;
			case 'next':
				$result	= '<a '.$attr.' style="float:left;margin-right:5px;height:19px;'.$style.'"><img src="images/skyblue/next.png" ></a>&nbsp;';
			break;
			case 'last':
				$result	= '<a '.$attr.' style="float:left;margin-right:5px;height:19px;'.$style.'"><img src="images/skyblue/last.png"></a>';
			break;
			case 'pages':
				$result	= '<select id="pages" name="pages" style="float:left;margin-right:5px;height:19px;min-width:50px;style="float:left;'.$style.'" '.$attr.'>';
			break;
		}
		return $result;
	}
	// Fungsi untuk link halaman 1,2,3 
	function navHalaman($halaman_aktif,$jmlhalaman,$arrButton){
		$link_halaman = "<div style='display: inline-block;padding:5px;'>";
		if(empty($arrButton['first'])){
			$arrButton['first'] = "";
		}
		if(empty($arrButton['prev'])){
			$arrButton['prev'] = "";
		}
		if(empty($arrButton['pages'])){
			$arrButton['pages'] = "";
		}
		if(empty($arrButton['next'])){
			$arrButton['next'] = "";
		}
		if(empty($arrButton['last'])){
			$arrButton['last'] = "";
		}
		if($halaman_aktif != 1){
			$link_halaman .= $this->addAttribute('first',$arrButton['first'],true);
		}else{
			$link_halaman .= $this->addAttribute('first',$arrButton['first'],false);
		}
		if($halaman_aktif > 1){
			$link_halaman .= $this->addAttribute('prev',$arrButton['prev'],true);
		}else{
			$link_halaman .= $this->addAttribute('prev',$arrButton['prev'],false);
		}
		$link_halaman .= $this->addAttribute('pages',$arrButton['pages'],true);
		// Link halaman 1,2,3, ...
		for ($i=1; $i<=$jmlhalaman; $i++){
			if ($i == $halaman_aktif){
				$link_halaman .= '<option value="'.$i.'" selected>'.$i.'</option>';
			}else{	
			  $link_halaman .= '<option value="'.$i.'">'.$i.'</option>';
			}
		}
		$link_halaman .= "</select>&nbsp;";
		if($halaman_aktif < $jmlhalaman){
			$link_halaman .= $this->addAttribute('next',$arrButton['next'],true);
		}else{
			$link_halaman .= $this->addAttribute('next',$arrButton['next'],false);
		}
		if($halaman_aktif != $jmlhalaman){
			$link_halaman .= $this->addAttribute('last',$arrButton['last'],true);
		}else{
			$link_halaman .= $this->addAttribute('last',$arrButton['last'],false);
		}
		$link_halaman .= "<div style='clear:both;'></div>";
		$link_halaman .= "</div>";
	return $link_halaman;
	}					
}
?>