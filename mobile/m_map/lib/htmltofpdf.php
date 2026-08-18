<?php
class PDF_HTML_Table extends FPDF
{
protected $B;
protected $I;
protected $U;
protected $HREF;

function __construct($orientation='P', $unit='mm', $format='A4')
{
    //Call parent constructor
    parent::__construct($orientation,$unit,$format);
    //Initialization
    $this->B=0;
    $this->I=0;
    $this->U=0;
    $this->HREF='';
}

function WriteHTML2($html)
{
    //HTML parser
    $html=str_replace("\n",' ',$html);
    $a=preg_split('/<(.*)>/U',$html,-1,PREG_SPLIT_DELIM_CAPTURE);
	//print_r($a);
	//exit();
    foreach($a as $i=>$e)
    {

		if($i%2==0)
		{
			//Text
			if($this->HREF)
				$this->PutLink($this->HREF,$e);
			else
				$this->Write(5,$e);
		}
		else
		{
			//Tag
			if($e[0]=='/')
				$this->CloseTag(strtoupper(substr($e,1)));
			else
			{
				//Extract attributes
				$a2=explode(' ',$e);
				$tag=strtoupper(array_shift($a2));
				$attr=array();
				foreach($a2 as $v)
				{
					if(preg_match('/([^=]*)=["\']?([^"\']*)/',$v,$a3))
						$attr[strtoupper($a3[1])]=$a3[2];
				}
				$this->OpenTag($tag,$attr);
			}
		}
		
    }
}

function OpenTag($tag, $attr)
{
	$imageTypeArray = array
		(
			0=>'UNKNOWN',
			1=>'GIF',
			2=>'JPEG',
			3=>'PNG',
			4=>'SWF',
			5=>'PSD',
			6=>'BMP',
			7=>'TIFF_II',
			8=>'TIFF_MM',
			9=>'JPC',
			10=>'JP2',
			11=>'JPX',
			12=>'JB2',
			13=>'SWC',
			14=>'IFF',
			15=>'WBMP',
			16=>'XBM',
			17=>'ICO',
			18=>'COUNT'  
		);

    //Opening tag
    if($tag=='B' || $tag=='I' || $tag=='U'){
        $this->SetStyle($tag,true);
	}
    if($tag=='A'){
        $this->HREF=$attr['HREF'];
	}
    if($tag=='BR'){
        $this->Ln(5);
	}
    if($tag=='P'){
        $this->Ln(10);
	}
	if($tag=='HR'){
		$this->Ln(5);
		$x=$this->GetX();
		$y=$this->GetY();
		if($this->CurOrientation=='L'){
			$this->Line(0,$y,300,$y);	
		}else{
			$this->Line(0,$y,210,$y);
		}
		$this->Ln(5);
	}
	if($tag=='IMG'){
		$x=$this->GetX();
		$y=$this->GetY();
		$width = 0;
		$height = 0;
		
		if(isset($attr['SRC']) and $attr['SRC'] != ""){
			list($wimg, $himg, $type, $attribute) = getimagesize($attr['SRC']);

			if(isset($attr['WIDTH'])){
				$width = $attr['WIDTH'];
			}
			if(isset($attr['HEIGHT'])){
				$height = $attr['HEIGHT'];
			}
			if($width > 0 and $height == 0){
				$height = ($width/$wimg)*$himg;
			}
			if($width > 0 and $height == 0){
				$width = ($height/$himg)*$wimg;
			}

			$this->Image($attr['SRC'],$x,$y,$width,$height,$imageTypeArray[$type]);
			$this->SetXY($x+$width,$y+$height); 
		}
        
	}
}

function CloseTag($tag)
{
    //Closing tag
    if($tag=='B' || $tag=='I' || $tag=='U')
        $this->SetStyle($tag,false);
    if($tag=='A')
        $this->HREF='';
    if($tag=='P')
        $this->Ln(10);
}

function SetStyle($tag, $enable)
{
    //Modify style and select corresponding font
    $this->$tag+=($enable ? 1 : -1);
    $style='';
    foreach(array('B','I','U') as $s)
        if($this->$s>0)
            $style.=$s;
    $this->SetFont('',$style);
}

function PutLink($URL, $txt)
{
    //Put a hyperlink
    $this->SetTextColor(0,0,255);
    $this->SetStyle('U',true);
    $this->Write(5,$txt,$URL);
    $this->SetStyle('U',false);
    $this->SetTextColor(0);
}
function checkcolspan($data){
	$cCheck = 0;
	for($i=0;$i<count($data);$i++){
		if(isset($data[$i]['attribute']['colspan'])){
			$cCheck += $data[$i]['attribute']['colspan'];
		}
	}
	return $cCheck;
}

function WriteTable($tableatt,$tableData,$w,$thtb,$a,$b,$c,$allAttribute)
{
    $this->SetLineWidth(.2);
    $this->SetFillColor(255,255,255);
    $this->SetTextColor(0);
    $this->SetFont('');
	$data = $tableData;
	//print_r($hi);
	//$tablecolumn = $tableData['column'];
	$estborder = 1;
	$rect  = "";
	//setup Table 
	
	if(count($tableatt)>0){
		foreach ($tableatt as $k=>$v){
			if(strtolower($k) == "border"){
				$estborder = $v;
			}
		}
	}
	$att = array();
	if(count($allAttribute)>0){
		foreach ($allAttribute as $k=>$v){
			if(strtolower($k) == "width"){
				$att = $v;
			}
		}
	}
	
	$gs = 0;
    for($n=0;$n<count($data);$n++)
    {
        $nb=0;
		$nb2=0;
		$dtRow = $data[$n];
        for($i=0;$i<count($dtRow);$i++){
			$nb=max($nb,$this->NbLines($w[$n][$i],trim($dtRow[$i]['text'])));
			$h[$n]=5*$nb;
			if(isset($allAttribute[$n][$i]['rowspan']) and $allAttribute[$n][$i]['rowspan'] > 1){ //jika ada rowspan
				for($ii=1; $ii<$allAttribute[$n][$i]['rowspan']; $ii++){ //loop jumlah  rowspan
					//untuk height rowspan cek data lain
					if(isset($data[$n+$ii])){
						for($iii=0;$iii<count($data[$n+$ii]);$iii++){
							$nb2=max($nb2,$this->NbLines($w[$n+$ii][$iii],trim($data[$n+$ii][$iii]['text'])));
							$h[$n+$ii]=5*$nb2;
						}
					}
				}
			}
			$this->CheckPageBreak(5);
		}
		
		//print_r($h);
        for($i=0;$i<count($dtRow);$i++)
        {

			$x=$this->GetX();
            $y=$this->GetY();
			$wcol = $w[$n][$i];
			$height = $h[$n];
			$jmlNextRowTemp 	  = 0;
			$jmlColpsanThisColumn = 0;
			$jmlStartTemp 		  = 0;
			$widthColspanTemp 	  = 0;
			
			if(isset($allAttribute[$n][$i]['rowspan']) and $allAttribute[$n][$i]['rowspan'] > 1){
				for($ii=1; $ii<$allAttribute[$n][$i]['rowspan']; $ii++){//mulai dari 1 (tidak 0) karena untuk row berikutnya
					//untuk height rowspan
					if(isset($h[$n+$ii])){
						$height += $h[$n+$ii];
					}
					if(isset($allAttribute[$n][$i+1]['rowspan']) and $allAttribute[$n][$i+1]['rowspan'] > 1){
						
					}else{
						$nh[$n+$ii][] = $x+$wcol;
					}
					
				}
				
			}
			if(isset($allAttribute[$n][$i]['colspan']) and $allAttribute[$n][$i]['colspan'] > 1){	
				for($ii=1; $ii<$allAttribute[$n][$i]['colspan']; $ii++){
					//looping sesuai jumlah colspan, untuk menentukan kolom berikutnya tidak ikut dibawah kolom berikutnya
					$nh[$n+1][] = "colspan";
				}
				$jmlNextRowTemp 	  = count($nh[$n+1]);
				$jmlColpsanThisColumn = $allAttribute[$n][$i]['colspan'];
				$jmlStartTemp 		  = $jmlNextRowTemp-$jmlColpsanThisColumn;
				$widthColspanTemp 	  =  $wcol / $jmlColpsanThisColumn;
				for($ii=$jmlColpsanThisColumn; $ii<$jmlNextRowTemp; $ii++){
					$w[$n+1][$ii] = $widthColspanTemp; //replace width row berikutnya ( penyesuaian widthnya )
				}
			}
			if(empty($nh[$n][$i]) or $nh[$n][$i] == "colspan"){
				$nh[$n][$i] = $x;
			}else{
				$this->SetX($nh[$n][$i]);
			}
			$hr[$n][$i] = $height;
			
			$maxh = $this->NbLines($wcol,trim($dtRow[$i]['text']))*5;
			if($maxh == $hr[$n][$i]){
				$hr[$n][$i] = 5;
			}
			
			//print_r($maxh );
            
			//MultiCell(float w, float h, string txt [, mixed border [, string align [, boolean fill]]])
			
			if($thtb == "thead"){
				//$this->SetFont('arial','B', 10);
				//colorFill
				if($b[$n][$i]){
					list($br,$bg,$bb) = explode(",",$b[$n][$i]);
				}else{
					$br = "255";
					$bg = "255";
					$bb = "255";
				}
				$this->SetFillColor($br, $bg, $bb);
				//colorFont
				if($c[$n][$i]){
					list($cr,$cg,$cb) = explode(",",$c[$n][$i]);
				}else{
					$cr = "0";
					$cg = "0";
					$cb = "0";
				}
				$this->SetTextColor($cr, $cg, $cb);
				
				if(isset($a[$n][$i]) or $a[$n][$i] != ""){
					$align = $a[$n][$i];
				}else{
					$align = "J";
				}
				
				$this->MultiCell($wcol,$hr[$n][$i],trim($dtRow[$i]['text']),$border=1, $align,true);
			}elseif($thtb == "tbody"){
				//colorFill
				if($b[$n][$i]){
					list($br,$bg,$bb) = explode(",",$b[$n][$i]);
				}else{
					$br = "255";
					$bg = "255";
					$bb = "255";
				}
				$this->SetFillColor($br, $bg, $bb);
				//colorFont
				if($c[$n][$i]){
					list($cr,$cg,$cb) = explode(",",$c[$n][$i]);
				}else{
					$cr = "0";
					$cg = "0";
					$cb = "0";
				}
				if(isset($a[$n][$i]) or $a[$n][$i] != ""){
					$align = $a[$n][$i];
				}else{
					$align = "J";
				}
				$this->SetTextColor($cr, $cg, $cb);
				$this->MultiCell($wcol,5,trim($dtRow[$i]['text']),$border=0,$align ,true);
				if($estborder == '1'){
					$this->Rect($nh[$n][$i],$y,$wcol,$h[$n]);
				}else{
					if(@$allAttribute[$n][$i]['border-top'] == "1"){
						$this->Line($nh[$n][$i],$y,$nh[$n][$i]+$wcol,$y);
					}
					if(@$allAttribute[$n][$i]['border-bottom'] == "1"){
						$this->Line($nh[$n][$i],$y+$hr[$n][$i],$nh[$n][$i]+$wcol,$y+$hr[$n][$i]);
					}
					if(@$allAttribute[$n][$i]['border-left'] == "1"){
						$this->Line($nh[$n][$i],$y,$nh[$n][$i],$y+$hr[$n][$i]);
					}
					if(@$allAttribute[$n][$i]['border-right'] == "1"){
						$this->Line($nh[$n][$i]+$wcol,$y,$nh[$n][$i]+$wcol,$y+$hr[$n][$i]);
					}
				}
			}
			
            //Put the position to the right of the cell
            $this->SetXY($nh[$n][$i]+$w[$n][$i],$y);   
        }
        $this->Ln($h[$n]); 
    }
	//print_r($h);
	//exit("ERROR:".$rect);
}

function NbLines($w, $txt)
{
    //Computes the number of lines a MultiCell of width w will take
    $cw=&$this->CurrentFont['cw'];
    if($w==0)
        $w=$this->w-$this->rMargin-$this->x;
    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    $s=str_replace("\r",'',$txt);
    $nb=strlen($s);
    if($nb>0 && $s[$nb-1]=="\n")
        $nb--;
    $sep=-1;
    $i=0;
    $j=0;
    $l=0;
    $nl=1;
    while($i<$nb)
    {
        $c=$s[$i];
        if($c=="\n")
        {
            $i++;
            $sep=-1;
            $j=$i;
            $l=0;
            $nl++;
            continue;
        }
        if($c==' ')
            $sep=$i;
        $l+=$cw[$c];
        if($l>$wmax)
        {
            if($sep==-1)
            {
                if($i==$j)
                    $i++;
            }
            else
                $i=$sep+1;
            $sep=-1;
            $j=$i;
            $l=0;
            $nl++;
        }
        else
            $i++;
    }
    return $nl;
}

function CheckPageBreak($h)
{
    //If the height h would cause an overflow, add a new page immediately
    if($this->GetY()+$h>$this->PageBreakTrigger)
        $this->AddPage($this->CurOrientation);
}

function ReplaceHTML($html)
{
    $html = str_replace( '<li>', "\n<br> - " , $html );
    $html = str_replace( '<LI>', "\n - " , $html );
    $html = str_replace( '</ul>', "\n\n" , $html );
    $html = str_replace( '<strong>', "<b>" , $html );
    $html = str_replace( '</strong>', "</b>" , $html );
    $html = str_replace( '&#160;', "\n" , $html );
    $html = str_replace( '&nbsp;', " " , $html );
    $html = str_replace( '&quot;', "\"" , $html ); 
    $html = str_replace( '&#39;', "'" , $html );
    return $html;
}

function ParseTable($Table)
{
    $_var='';
    $htmlText = $Table;
    $parser = new HtmlParser ($htmlText);
	$attdt = array();
	$alldata = array();
	$itable = array();
	$colom = 0;
	$colstart=0;
    while ($parser->parse())
    {
        if(strtolower($parser->iNodeName)=='table')
        {
            if($parser->iNodeType == NODE_TYPE_ENDELEMENT){
               // $_var .='/::';
            }else{
               // $_var .='::';
			   $colstart = 1;
			}
			$att = $parser->iNodeAttributes;
			$d	= array();
            foreach ($att as $k=>$v)
			{
				$d[$k] = $v;
			}
			$idata["table"]['attribute'] = $d;
			
        }

		if(strtolower($parser->iNodeName)=='thead')
        {
			
            if($parser->iNodeType == NODE_TYPE_ENDELEMENT){
				if($colstart ==1 && $colom > 0){
					$colstart = 0;
				}

				$idata["table"]['thead'] = $itable;
				$itable = array();
				
            }
        }
		if(strtolower($parser->iNodeName)=='tbody')
        {
            if($parser->iNodeType == NODE_TYPE_ENDELEMENT){
				$idata["table"]['tbody'] = $itable;
				$itable = array();
            }
        }
		
        if(strtolower($parser->iNodeName)=='tr')
        {
            if($parser->iNodeType == NODE_TYPE_ENDELEMENT){
                //$_var .='!-:'; //opening row
				$itable[] = $alldata;
            }else{
               // $_var .=':-!'; //closing row
				$alldata = array();
				if($colstart == 1 ){
					$colom = 0;
				}
			}
        }
		if(strtolower($parser->iNodeName)=='th' && $parser->iNodeType == NODE_TYPE_ELEMENT)
        {
			$att = $parser->iNodeAttributes;
			$d	= array();
			
            foreach ($att as $k=>$v)
			{
				$d[$k] = $v;
			}
			$data['attribute'] = $d;
        }
		if(strtolower($parser->iNodeName)=='td' && $parser->iNodeType == NODE_TYPE_ELEMENT)
        {
			$att = $parser->iNodeAttributes;
			$d	= array();
			
            foreach ($att as $k=>$v)
			{
				$d[$k] = $v;
			}
			$data['attribute'] = $d;
        }
        if(strtolower($parser->iNodeName)=='th' && $parser->iNodeType == NODE_TYPE_ENDELEMENT)
        {
            //$_var .='#,#';
			$alldata[] = $data;
			if($colstart == 1 ){
				$colom += 1;
			}
        }
		if(strtolower($parser->iNodeName)=='td' && $parser->iNodeType == NODE_TYPE_ENDELEMENT)
        {
            //$_var .='#,#';
			$alldata[] = $data;
			
        }
        if ($parser->iNodeName=='Text' && isset($parser->iNodeValue))
        {
            $_var .= $parser->iNodeValue;
			$value = $parser->iNodeValue;
			$data['text'] = $value;
            
        }
		 if ($parser->iNodeName=='img')
        {
			$data['img'] = $parser->iNodeAttributes;
        }
    }
	/**
    $elems = explode(':-!',str_replace('/','',str_replace('::','',str_replace('!-:','',$_var)))); //opening row
    foreach($elems as $key=>$value)
    {
        if(trim($value)!='')
        {
            $elems2 = explode('#,#',$value);
            array_pop($elems2);
            $dt['text'][] = $elems2;
        }
    }
	**/

	$idata["column"] 	= $colom;
	
    return $idata;
}

function WriteHTML($html)
{
    $html = $this->ReplaceHTML($html);
	$hset = 130;
	$hnormal = 5;
	$tableHead	= "";
	$tableBody	="";
	$allAttribute = array();
    //Search for a table
    $start = strpos(strtolower($html),'<table');
    $end = strpos(strtolower($html),'</table');
    if($start!==false && $end!==false)
    {
        $this->WriteHTML2(substr($html,0,$start).'<BR>');

        $tableVar = substr($html,$start,$end-$start);
        $arrtableData = $this->ParseTable($tableVar);
		$tableatt = array();
		$tableData = array();
		if(isset($arrtableData['table']['attribute'])){
			$tableatt = $arrtableData['table']['attribute'];
		}
		if(isset($arrtableData['table']['thead'])){
			$tableData[] = $arrtableData['table']['thead'];
			$tagDev[] = "thead";
		}
		if(isset($arrtableData['table']['tbody'])){
			$tableData[] = $arrtableData['table']['tbody'];
			$tagDev[] = "tbody";
		}
        $tablecolumn = $arrtableData['column'];

	if(count($tableData)>0){	
		for($nt=0;$nt<count($tableData);$nt++){
			if(count($tableData[$nt])>0){
				$jmlThead = count($tableData[$nt]);
				for($n=0;$n<$jmlThead;$n++){
					$w = array();
					$h = array();
					for($i=0;$i<count($tableData[$nt][$n]);$i++)
					{
						$bil = 1;
						if(isset($tableData[$nt][$n][$i]['attribute']['width'])){
							$w[] = $tableData[$nt][$n][$i]['attribute']['width'];
						}else{
							if($this->CurOrientation=='L'){
								if((count($tableData[$nt][$n])-1)>0){
									$bil = (count($tableData[$nt][$n])-1);
								}
								$w[] = abs($bil)+24;
							}else{
								if((count($tableData[$nt][$n])-1)>0){
									$bil = (count($tableData[$nt][$n])-1);
								}
								$w[] = abs($hset/$bil)+5;
							}
						}
						/*
						if(isset($tableData[$nt][$n][$i]['attribute']['rowspan']) and $tableData[$nt][$n][$i]['attribute']['rowspan'] > 1){
							$h[] = $tableData[$nt][$n][$i]['attribute']['rowspan'];
						}else{
							$h[] = 1;
						}*/
						if(isset($tableData[$nt][$n][$i]['attribute']['background']) and $tableData[$nt][$n][$i]['attribute']['background'] != ""){
							$b[$n][$i] = $tableData[$nt][$n][$i]['attribute']['background'];
						}else{
							$b[$n][$i] = "255,255,255";
							if($tagDev[$nt] == "thead"){
								$b[$n][$i] = "114, 186, 67";
							}
						}
						if(isset($tableData[$nt][$n][$i]['attribute']['color']) and $tableData[$nt][$n][$i]['attribute']['color'] != ""){
							$c[$n][$i] = $tableData[$nt][$n][$i]['attribute']['color'];
						}else{
							$c[$n][$i] = "0,0,0";
							if($tagDev[$nt] == "thead"){
								$c[$n][$i] = "255,255,255";
							}
						}
						if(isset($tableData[$nt][$n][$i]['attribute']['align'])){
							$swc = strtoupper($tableData[$nt][$n][$i]['attribute']['align']);
							switch ($swc){
								case 'CENTER' :
									$a[$n][$i] = "C";
								break;	
								case 'JUSTIFY' :
									$a[$n][$i] = "J";
								break;	
								case 'RIGHT' :
									$a[$n][$i] = "R";
								break;	
								case 'LEFT' :
									$a[$n][$i] = "L";
								break;	
							}
						}else{
							$a[$n][$i] = "J";
						}
						if(isset($tableData[$nt][$n][$i]['attribute'])){
							$allAttribute[$n][$i] = $tableData[$nt][$n][$i]['attribute'];
						}
					}
					$wi[$n] = $w;
					//$hi[$n] = $h;
				}
				$this->WriteTable($tableatt,$tableData[$nt],$wi,$tagDev[$nt],$a,$b,$c,$allAttribute);
			}
		}
		

	}

        $this->WriteHTML2(substr($html,$end+8,strlen($html)-1).'<BR>');
    }
    else
    {
        $this->WriteHTML2($html);
    }
}

}
?>