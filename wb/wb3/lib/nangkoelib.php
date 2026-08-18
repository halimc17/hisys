<?php
if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    header("Content-Type: text/html; charset=ISO-8859-1");
}
$firstTipe = 'MAP001';
$firstPT = 'MAP009';
$textBlok = 'MAP032';
$kodeproduktbs ='400000003';
$kodeprodukcpo ='400000001';
$kodeprodukpk ='400000002';

if (isset($_SESSION['theme']))
   $theme=$_SESSION['theme'];
else
   $theme='skyblue';

#theme mathcer
if($theme=='skyblue' || $theme==''){
  $men='menu.css';
  $gen='generic.css?ver=1.2';
  $bgColor='#E8F4F4';
  $logo='OWL_OV.png';
  $menuJs='menuscript.js';
  $drwImgDef='tab3.png';
  $drwImgSec='tab1.png';
  $bgTabInner='#E0ECFF';
  $bgTabOuter='#1E5896';
}else if($theme=='red'){
  $men='menuRed.css';
  $gen='genericRed.css';
  $bgColor='#C1976C';
  $logo='logo.jpg';
  $menuJs='menuscriptRed.js';
  $drwImgDef='tab3Red.png';
  $drwImgSec='tab1Red.png'; 
  $bgTabInner='#FBC0B9';
  $bgTabOuter='#BA0221';   
}else{
  $men='menuGray.css';
  $gen='genericGray.css';
  $bgColor='#9EAEC7';
  $logo='logo.jpg';
  $menuJs='menuscriptGray.js';
  $drwImgDef='tab3Gray.png';
  $drwImgSec='tab1Gray.png'; 
  $bgTabInner='#9E9A9A'; 
  $bgTabOuter='#636161';  
}

function generatenotiket($jenis,$kodeproduk){
    global $dbname;

    $str2="select * from ".$dbname.".mssystem limit 1";
    $res2=fetchdata($str2);
    $inisial=$res2[0]['idwb'];

    // $str="select inisial from ".$dbname.".msproduk where kodeproduk='".$kodeproduk."'";
    // $res=fetchdata($str);
    // $inisial=$res[0]['inisial'];

    // if ($inisial=='') {
    	// exit('Warning, inisial produk masih kosong, mohon dilengkapi untuk generate notiket.');
    // }

    //switch ($jenis) {
    	//case 'penerimaan':
		    $str="select notransaksi from ".$dbname.".wb";
		    $res=fetchdata($str);
		    if(!$res)
		    {
		        $no_1=1;
		        $no=str_pad($no_1,6,"0",STR_PAD_LEFT);
		    }
		    else
		    {   
		        $str2="select RIGHT(notransaksi,6) as notiket from ".$dbname.".wb  order by notiket desc limit 1";
		        $res2=fetchdata($str2);
		        if ($res2){
		            $ticketno=$res2[0]['notiket'];
		            $no_1=intval($ticketno)+1;
		            $no=str_pad($no_1,6,"0",STR_PAD_LEFT);
		        }
		        else
		        {
		            $no3=1;
		            $no=str_pad($no3,6,"0",STR_PAD_LEFT);
		        }
		    }
		    return $inisial.$no;
    	//break;

		//case 'pengiriman':
		  //  $str="select notransaksi from ".$dbname.".wb where in_out='O'";
		    //$res=fetchdata($str);
		   // if(!$res)
		    //{
		      //  $no_1=1;
		       // $no=str_pad($no_1,6,"0",STR_PAD_LEFT);
		   // }
		    //else
		    //{   
		      //  $str2="select RIGHT(notransaksi,6) as notiket from ".$dbname.".wb where in_out='O' order by notiket desc limit 1";
		        //$res2=fetchdata($str2);
		       // if ($res2){
		         //   $ticketno=$res2[0]['notiket'];
		           // $no_1=intval($ticketno)+1;
		            //$no=str_pad($no_1,6,"0",STR_PAD_LEFT);
		        //}
		       // else
		        //{
		         //   $no3=1;
		           // $no=str_pad($no3,6,"0",STR_PAD_LEFT);
		        //}
		    //}
		   // return $inisial.$no;
	//	break;
    	
    //	default:
    	//	exit('Warning harus pake param lah bro!');
    //	break;
   // }
}

function OPEN_BODY($title='Weighbridges')
{
global $men;
global $gen;
global $bgColor;
global $logo;
global $menuJs;
echo"<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">
<html>
	<head>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">
		<meta http-equiv='Cache-Control' CONTENT='no-cache'>
		<meta http-equiv='Pragma' CONTENT='no-cache'>	
		<title>".$title."</title>";
echo"
	<script src=\"lib/alertify/js/jquery.min.js\"></script>
	<link href=\"lib/alertify/css/alertify.css\" rel=\"stylesheet\"/>
	<link href=\"lib/alertify/css/themes/default.css\" rel=\"stylesheet\"/>
	<script src=\"lib/alertify/js/alertify.min.js\"></script>
	  
    <script language=JavaScript1.2 src=js/".$menuJs."></script>
	 <script language=JavaScript1.2 src=js/calendar.js></script>
    <script language=JavaScript1.2 src=js/drag.js></script>
	<!-- generic.js Ver.1.2 : Penambahan read file CSV  -->
    <script language=JavaScript1.2 src=js/generic.js?ver=2.1></script>
	<script language=JavaScript1.2 src=js/headerfixed.min.js?ver=0.2></script>
    <script language=JavaScript1.2 src=js/nangkoelsort.js></script>
	<link rel=stylesheet type=text/css href=style/".$men.">
	<link rel=stylesheet type=text/css href=style/".$gen.">	
	<link rel=stylesheet type=text/css href=style/calendarblue.css>
	<link rel=stylesheet type=text/css href=style/zTable.css>
	<link rel=\"stylesheet\" href=\"font-awesome/css/font-awesome.css\">
    </head>
<body  style='margin:30px;margin-top:0px;background-color:".$bgColor.";' onload=verify()>
<img id='smallOwl' src='images/".$logo."' width='300px'
	style='position:absolute;top:20%;left:35%;z-index:-998'>
<noscript>
	<span style='font-size:13px;font-family:arial;'>
		<span style='color:#dd3300'>Warning!</span>
			&nbsp&nbsp; QuickMenu may have been blocked by IE-SP2's active 
			content option. This browser feature blocks JavaScript from running 
			locally on your computer.<br>
			<br>This warning will not display once the menu is on-line.  
			To enable the menu locally, click the yellow bar above, and select 
			<span style='color:#0033dd;'>'Allow Blocked Content'
		</span>.
	<br><br>To permanently enable active content locally...
		<div style=padding:0px 0px 30px 10px;color:#0033dd;'>
			<br>1: Select 'Tools' --> 'Internet Options' from the IE menu.
			<br>2: Click the 'Advanced' tab.
			<br>3: Check the 2nd option under 'Security' in the tree 
			(Allow active content to run in files on my computer.)
		</div>
	</span>
</noscript>";
}
function OPEN_BODY_BI($title='OWL-Plantation System')
{
global $men;
global $gen;
global $bgColor;
global $menuJs;	
echo"<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">
<html>
	<head>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">
		<meta http-equiv='Cache-Control' CONTENT='no-cache'>
		<meta http-equiv='Pragma' CONTENT='no-cache'>
		<title>".$title."</title>";
echo" 
    <script language=JavaScript1.2 src=js/".$menuJs."></script>
	 <script language=JavaScript1.2 src=js/calendar.js></script>
    <script language=JavaScript1.2 src=js/drag.js></script>
    <script language=JavaScript1.2 src=js/generic.js?ver=1.1></script>
    <script language=JavaScript1.2 src=js/nangkoelsort.js></script>
	<link rel=stylesheet type=text/css href=style/".$men.">
	<link rel=stylesheet type=text/css href=style/".$gen.">	
	<link rel=stylesheet type=text/css href=style/calendarblue.css>
    </head>
<body  style='margin-top:10px;margin-left:2px;margin-right:2px;background-color:#E8F4F4;' onload=verify()>
<div id='progress' style='display:none;border:orange solid 1px;width:150px;position:fixed;right:20px;top:65px;color:#ff0000;font-family:Tahoma;font-size:13px;font-weight:bolder;text-align:center;background-color:#FFFFFF;z-index:10000;'>
Please wait.....! <br>
<img src='images/progress.gif'>
</div>
<img id='smallOwl' src='images/OWL_OV.png' width='300px' style='position:absolute;top:20%;left:35%;z-index:-998'>
<noscript>
	<span style='font-size:13px;font-family:arial;'>
		<span style='color:#dd3300'>Warning!</span>
			&nbsp&nbsp; QuickMenu may have been blocked by IE-SP2's active 
			content option. This browser feature blocks JavaScript from running 
			locally on your computer.<br>
			<br>This warning will not display once the menu is on-line.  
			To enable the menu locally, click the yellow bar above, and select 
			<span style='color:#0033dd;'>'Allow Blocked Content'
		</span>.
	<br><br>To permanently enable active content locally...
		<div style=padding:0px 0px 30px 10px;color:#0033dd;'>
			<br>1: Select 'Tools' --> 'Internet Options' from the IE menu.
			<br>2: Click the 'Advanced' tab.
			<br>3: Check the 2nd option under 'Security' in the tree 
			(Allow active content to run in files on my computer.)
		</div>
	</span>
</noscript>
<img src='images/owl2.png'  style='height:50px;position:absolute;right:0px;top:0px;;z-index:-998'>
";
}

function CLOSE_BODY()
{
	require_once('master_footer.php');
	@include('lib/lang.php');
	echo "</body></html>";
}


function OPEN_BODY_NEWBI($title='OWL-Plantation System')
{
global $men;
global $gen;
global $bgColor;
global $menuJs;
require_once('master_validation.php');
echo"<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">
<html>
	<head>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">
		<meta http-equiv='Cache-Control' CONTENT='no-cache'>
		<meta http-equiv='Pragma' CONTENT='no-cache'>
		<title>".$title."</title>";
echo" 
    <script language=JavaScript1.2 src=js/".$menuJs."></script>
	<script language=JavaScript1.2 src=../js/calendar.js></script>
	<script language=JavaScript1.2 src=../js/generic.js?ver=1.2></script>
    <script language=JavaScript1.2 src=../js/nangkoelsort.js></script>
	<script language=JavaScript1.2 src=../js/drag.js></script>
    <script language=JavaScript1.2 src=js/menu.js></script>
	<script type='text/javascript' src='js/drag-resize.js'></script>
	<link rel=stylesheet type=text/css href=../style/".$men.">
	<link rel=stylesheet type=text/css href=../style/".$gen.">	
	<link rel=stylesheet type=text/css href=../style/calendarblue.css>
	<link rel=stylesheet type=text/css href=style/styles.css>
    </head>
<body style='margin-right:2px;background-color:#E8F4F4;'>
<div id='progress' style='display:none;border:orange solid 1px;width:150px;position:fixed;right:20px;top:65px;color:#ff0000;font-family:Tahoma;font-size:13px;font-weight:bolder;text-align:center;background-color:#FFFFFF;z-index:10000;'>
Please wait.....! <br>
<img src='../images/progress.gif'>
</div>";

echo "<div id='rmbox' class='hide'>
    <ul id='rmtab'>
        <li>
            <img id='arrow' onclick=\"toggle('rmbox')\" src='images/arrows_left.png' style='width:20px;height:20px;'>
        </li>
    </ul>";
	
	require_once('master_mainMenu.php');
echo "</div>";

echo "<img id='smallOwl' src='../images/OWL_OV.png' width='300px' style='position:absolute;top:20%;left:35%;z-index:-998'>";
}

function CLOSE_BODY_NEWBI()
{
	echo "</body></html>";
}

function OPEN_BOX($style='',$title='',$id='',$contentId='')
{
	// if($_SESSION['theme']=='' || $_SESSION['theme']=='skyblue'){
		echo"<div  id='".$id."' class=\"x-box-blue\" style='".$style."'><div class=\"x-box-tl\"><div class=\"x-box-tr\">
		<div class=\"x-box-tc\"></div></div></div><div class=\"x-box-ml\"><div class=\"x-box-mr\">
		<div class=\"x-box-mc\" id='contentBox".$contentId."' style='overflow:auto;'>
		".$title;
	// }else if($_SESSION['theme']=='red'){
	// 	echo"<fieldset  id='".$id."'  style='".$style."'><legend>".$title."</legend>
	// 	<div  id='contentBox".$contentId."' style='overflow:auto;'>";
	// }else{
	// 	echo"<fieldset  id='".$id."'  style='".$style."'><legend>".$title."</legend>
	// 	<div  id='contentBox".$contentId."' style='overflow:auto;'>";		

	// }
}
function OPEN_BOXX($style='',$title='',$id='',$contentId='')
{
	// if($_SESSION['theme']=='' || $_SESSION['theme']=='skyblue'){
		echo"<div  id='".$id."' class=\"x-box-blue\" style='".$style.";position:sticky;top:35px;z-index:99'><div class=\"x-box-tl\"><div class=\"x-box-tr\">
		<div class=\"x-box-tc\"></div></div></div><div class=\"x-box-ml\"><div class=\"x-box-mr\">
		<div class=\"x-box-mc\" id='contentBox".$contentId."' style='overflow:auto;'>
		".$title;
	// }else if($_SESSION['theme']=='red'){
	// 	echo"<fieldset  id='".$id."'  style='".$style."'><legend>".$title."</legend>
	// 	<div  id='contentBox".$contentId."' style='overflow:auto;'>";
	// }else{
	// 	exit('Error');
	// 	echo"<fieldset  id='".$id."'  style='".$style."'><legend>".$title."</legend>
	// 	<div  id='contentBox".$contentId."' style='overflow:auto;'>";		

	// }
}
function OPEN_BOX2($style='',$title='',$id='',$contentId='')
{
	if($_SESSION['theme']=='' || $_SESSION['theme']=='skyblue'){
		return "<div  id='".$id."' class=\"x-box-blue\" style='".$style."'><div class=\"x-box-tl\"><div class=\"x-box-tr\">
		<div class=\"x-box-tc\"></div></div></div><div class=\"x-box-ml\"><div class=\"x-box-mr\">
		<div class=\"x-box-mc\" id='contentBox".$contentId."' style='overflow:auto;'>
		".$title;
	}else if($_SESSION['theme']=='red'){
		return "<fieldset  id='".$id."'  style='".$style."'><legend>".$title."</legend>
		<div  id='contentBox".$contentId."' style='overflow:auto;'>";
	}else{
		return "<fieldset  id='".$id."'  style='".$style."'><legend>".$title."</legend>
		<div  id='contentBox".$contentId."' style='overflow:auto;'>";		
	}		
}
function CLOSE_BOX()
{
	// if($_SESSION['theme']=='' || $_SESSION['theme']=='skyblue'){
	echo"</div></div></div>
        <div class=\"x-box-bl\"><div class=\"x-box-br\"><div class=\"x-box-bc\"></div></div></div>
        </div>";
 //    }else if($_SESSION['theme']=='red'){
	// 	echo"</div></fieldset><br>";
	// }else{
	// 	echo"</div></fieldset><br>";
	// }
}
function CLOSE_BOX2()
{
	if($_SESSION['theme']=='' || $_SESSION['theme']=='skyblue'){
	return "</div></div></div>
        <div class=\"x-box-bl\"><div class=\"x-box-br\"><div class=\"x-box-bc\"></div></div></div>
        </div>";
    }else if($_SESSION['theme']=='red'){
		return "</div></fieldset><br>";
	}else{
		return "</div></fieldset><br>";
	}
}
function drawTab($tabId='T',$arrHeader,$arrContent,$tabLength,$contentLength='300')
{
//if you use more than one tab group on one page you must throw/fill the $tabID var	
//array header must the same size as array content of the tab
global $drwImgDef;
global $drwImgSec;  
global $bgTabInner;
global $bgTabOuter;
$tabLength=str_replace("px","",$tabLength);
$tabLength=str_replace(";","",$tabLength);
$contentLength=str_replace("px","",$contentLength);
$contentLength=str_replace(";","",$contentLength);
$stream="
<table border=0 cellspacing=0 cellpadding='3'>
<tr class=tab>";
 for($x=0;$x<count($arrHeader);$x++)
 {
	if($x==0)
	  $stream.="<td id=tab".$tabId.$x." onclick=tabAction(this,".$x.",'".$tabId."',".(count($arrHeader)-1).",'skyblue'); style=\"background-color:#4D87B0;color:#FFFFFF;\">".$arrHeader[$x]."</td>";
	else
      $stream.="<td id=tab".$tabId.$x." onclick=tabAction(this,".$x.",'".$tabId."',".(count($arrHeader)-1).",'skyblue');>".$arrHeader[$x]."</td>";		
 }
$stream.="</tr></table>";
 for($x=0;$x<count($arrContent);$x++)
 {
	if($x==0)
       $stream.="<fieldset style='display:\"\";border-color:#1E5896; border-style:solid;border-width:2px; background-color:".$bgTabInner.";margin-left:0px;width:".$contentLength."px;border-radius: 0px 10px 10px 10px;' id=content".$tabId.$x.">".$arrContent[$x]."</fieldset>";
	else
	   $stream.="<fieldset style='display:none;border-color:#1E5896; border-style:solid;border-width:2px; background-color:".$bgTabInner.";margin-left:0px;width:".$contentLength."px;border-radius: 0px 10px 10px 10px;' id=content".$tabId.$x.">".$arrContent[$x]."</fieldset>";	
 }
 echo $stream;
}

function drawTabBI($tabId='T',$arrHeader,$arrContent,$tabLength,$contentLength='300'){
	global $drwImgDef;
	global $drwImgSec;  
	global $bgTabInner;
	global $bgTabOuter;
	$tabLength=str_replace("px","",$tabLength);
	$tabLength=str_replace(";","",$tabLength);
	$contentLength=str_replace("px","",$contentLength);
	$contentLength=str_replace(";","",$contentLength);
	
	$stream="<table border=0 cellspacing=0>
		<tr class=tab>";
		for($x=0;$x<count($arrHeader);$x++){
			if($x==0)
				$stream.="<td id=tab".$tabId.$x." onclick=tabActionBI(this,".$x.",'".$tabId."',".(count($arrHeader)-1).",'".$_SESSION['theme']."'); onmouseover=chgBackgroundImgBI(this,'./images/".$drwImgSec."','#CC3366');  onmouseout=chgBackgroundImgBI(this,'./images/".$drwImgDef."','#FFFFFF');  style=\"background-image:url('./images/".$drwImgSec."');color:#CC3366;font-weight:bold;border-right:#dedede solid 1px;width:".$tabLength."px;height:20px\">".$arrHeader[$x]."</td>";
			else
				$stream.="<td id=tab".$tabId.$x." style='border-right:#FFFFFF solid 1px; height:20px; width:".$tabLength."px; background-image:url(\"./images/".$drwImgDef."\");color:#FFFFFF' onclick=tabActionBI(this,".$x.",'".$tabId."',".(count($arrHeader)-1).",'".$_SESSION['theme']."'); onmouseover=chgBackgroundImgBI(this,'./images/".$drwImgSec."','#CC3366'); onmouseout=chgBackgroundImgBI(this,'./images/".$drwImgDef."','#FFFFFF'); >".$arrHeader[$x]."</td>";		
		}
		$stream.="</tr></table>";
		
		for($x=0;$x<count($arrContent);$x++){
			if($x==0)
				$stream.="<div style='display:\"\";border-color:".$bgTabOuter."; border-style:solid;border-width:2px;background-color:".$bgTabInner.";margin-left:0px;padding:5px;width:".$contentLength."px;' id=content".$tabId.$x.">".$arrContent[$x]."</div>";
			else
				$stream.="<div style='display:none;border-color:".$bgTabOuter."; border-style:solid;border-width:2px;background-color:".$bgTabInner.";margin-left:0px;padding:5px;width:".$contentLength."px;' id=content".$tabId.$x.">".$arrContent[$x]."</div>";
		}
	return $stream;
}

function drawaccordion($arrHeader,$arrContent){
	$stream = "";
	for($x=0;$x<count($arrHeader);$x++){
		if($x == 0){
			$stream .= "<div id=head".$x." style='background-color:#79b8d2;color: #fff;text-shadow: 1px 1px 3px #265c72;border-top-color: #fff;border-bottom-color: #649cb4;font-weight: 300;font-size: 1.25em;padding-top: .35em;padding-bottom: .175em;padding-left: 1em;border-left: 1px solid transparent;border-bottom: 1px solid #b5c3c9;cursor: pointer;' onclick=\"accordionClick('".$x."')\">".$arrHeader[$x]."
				<span id=span".$x." style='float:right;padding-right:5px;'><img src='images/arrow_top1.png'></span>
			</div>";
			$stream .= "<div id=content".$x." style='background-color:#ebf8f9;border-top-color: #fff;border-bottom-color: #649cb4;border-left: 1px solid transparent;border-bottom: 1px solid #b5c3c9;padding-top:5px; padding-right:5px;padding-bottom:10px;padding-left: 1em;'>
				".$arrContent[$x]."
			</div>";
		}else{
			$stream .= "<div id=head".$x." style='background-color:#79b8d2;color: #fff;text-shadow: 1px 1px 3px #265c72;border-top-color: #fff;border-bottom-color: #649cb4;font-weight: 300;font-size: 1.25em;padding-top: .35em;padding-bottom: .175em;padding-left: 1em;border-left: 1px solid transparent;border-bottom: 1px solid #b5c3c9;cursor: pointer;' onclick=\"accordionClick('".$x."')\">".$arrHeader[$x]."
				<span id=span".$x." style='float:right;padding-right:5px;'><img src='images/arrow_down1.png'></span>
			</div>";
			$stream .= "<div id=content".$x." style='display:none;background-color:#ebf8f9;border-top-color: #fff;border-bottom-color: #649cb4;padding-top: .35em;padding-bottom: 10px;padding-left: 1em;border-left: 1px solid transparent;border-bottom: 1px solid #b5c3c9; padding-right:5px;'>
				".$arrContent[$x]."
			</div>";
		}
	}
	
	echo $stream;
}

function showpopup($title,$content,$parent,$id,$tipe=null){
	$stream = "<div class='pane' id='".$id."'>
		<div id='title' class='disable-selection'>".$title."
			<span style='float:right;padding-right:5px;'>";
				if($tipe == 1){
					$stream .= "<img src=../images/closebig.gif align=right onclick=closeDialogPopUpSvg('".$parent."') title='Tutup' class=closebtn onmouseover=\"this.src='../images/closebigon.gif';\" onmouseout=\"this.src='../images/closebig.gif';\">";
				}
			$stream .= "</span>
		</div>
		<div class='disable-selection' style='padding:5px;position:relative;'>
			".$content."
		</div>
	</div>";
	
	echo $stream;
}


function OPEN_THEME($caption='',$width='',$text_align='left')
{
if (isset($_SESSION['theme']))
   $theme=$_SESSION['theme'];
else
   $theme='skyblue';  
   
   if($theme=='black')
      $capcolor='#FFFFFF';   
   else
      $capcolor='#333333';   
   
   if(isset($width))
      $lebar=" width=".$width."px";
   else
      $lebar='';	  	  
	  
	$text="<table class='boundary' ".$lebar." cellspacing='0' border='0' padding='0' style='font-family:Tahoma;font-size:11px;'>
	<tr class='trheader' style='height:22px;'>
	
	<td class='headleft' style='width:7px;height:22px;background: url(images/".$theme."/a1.gif);background-repeat:no-repeat;'></td>
	<td class='headtop' align='".$text_align."' style='color:".$capcolor.";height:22px;background: url(images/".$theme."/a2.gif);'><b>".$caption."</b></td>
	<td class='headright' style='width:13px;height:22px;background: url(images/".$theme."/a3.gif);background-repeat:no-repeat;'></td>
	</tr>
	
	<tr>
	<td class='bodyleft' style='background: url(images/".$theme."/a4.gif);'></td>
	<td class='content' style='padding:0px 0px 0px 0px;background-color:#FFFFFF;'>";
	return $text;
}

function CLOSE_THEME()
{
if (isset($_SESSION['theme']))
   $theme=$_SESSION['theme'];
else
   $theme='skyblue';  
	$text="</td>
	<td class='bodyright' style='background: url(images/".$theme."/a5.gif);background-repeat:repeat-y;'></td>
	</tr>
	
	<tr class='trbottom' style='height:7px;'>
	<td class='bottomleft' style='background: url(images/".$theme."/a6.gif);background-repeat:no-repeat;'></td>
	<td class='bottom' style='background: url(images/".$theme."/a7.gif);background-repeat:repeat-x;'></td>
	<td class='bottomright' style='background: url(images/".$theme."/a8.gif);background-repeat:no-repeat;'></td></tr>
	</table>";
	return $text;
}

function tanggalnormal($_q)
{
 $_q=str_replace("-","",$_q);
 $_retval=substr($_q,6,2)."-".substr($_q,4,2)."-".substr($_q,0,4);
 return($_retval);
}
function tglstrip($_q)
{
 $_q=str_replace("-","",$_q);
 $_retval=substr($_q,6,2)."/".substr($_q,4,2)."/".substr($_q,0,4);
 return($_retval);
}
function tglnormal($x){
	$x=str_replace("-","",$x);
	$_retval=substr($x,6,2).".".substr($x,4,2).".".substr($x,0,4);
	return($_retval);
}
function tanggalnormald($_q)
{
//20090804 08:00:00
 $_q=str_replace("-","",$_q);
 $_retval=substr($_q,6,2)."-".substr($_q,4,2)."-".substr($_q,0,4)." ".substr($_q,9,9);
 return($_retval);
}
function tanggalsystem($_q)//from format dd-mm-YYYY
{
 $_retval=substr($_q,6,4).substr($_q,3,2).substr($_q,0,2);
 return($_retval);//return 8 chardate format eg.20090804
}

function tanggalsystemn($_q)//from format dd-mm-YYYY
{//0408
 $_retval=substr($_q,6,4)."-".substr($_q,3,2)."-".substr($_q,0,2).substr($_q,10,9);
 return($_retval);//return 8 chardate format eg.20090804
}


    

function tanggalsystemd($_q)//from format dd-mm-YYYY
{//0408
 $_retval=substr($_q,6,4)."-".substr($_q,3,2)."-".substr($_q,0,2).substr($_q,10,7).":00";
 return($_retval);//return 8 chardate format eg.20090804
}

function tanggalsystemx($_q)//from format YYYYmmddhhMMss
{
	$_retval=substr($_q,0,4)."-".substr($_q,4,2)."-".substr($_q,6,2)." ".substr($_q,8,2).":".substr($_q,10,2).":00";
	
	return($_retval);
}

function tanggaldb($_q)//from format dd-mm-YYYY
{//0408
 $_retval=substr($_q,6,4)."-".substr($_q,3,2)."-".substr($_q,0,2);
 return($_retval);//return 8 chardate format eg.20090804
}

function hari($tgl,$lang='ID')//$tgl==2009-04-13
{
//return name of days in Indonesia	
	$bln=substr($tgl,5,2);
	$thn=substr($tgl,0,4);
	$tgl=substr($tgl,8,2);
	$ha=date("w", mktime(0, 0, 0, $bln,$tgl,$thn));
	$x=array ("Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu");
	$y=array ("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
	if($lang=='ID')
	   return($x[$ha]);
	else
	   return($y[$ha]);   
}

function getUserEmail($karyawanid)
{
	//find user email address on datakaryawan table
        global $dbname;
        global $owlPDO;
        $strAv=$owlPDO->query("select emailkantor from ".$dbname.".datakaryawan
	        where  karyawanid in(".$karyawanid.")");		
        $strAv->setFetchMode(PDO::FETCH_OBJ);
        $retMail='';
        $no=0;
        while($barAv=$strAv->fetch())
	{
            $email=$barAv->emailkantor;
            if(strpos($email,'@')>1)
            {
                if($no==0)
                       $retMail=$email;
                   else
                       $retMail.=",".$email;#comma separated
                       
            }	
         $no+=1;   
        }
		
        return $retMail;
}

function getNamaOrg($kodeorg){
	global $dbname;
	global $owlPDO;
	
	$hasil="";
	$str="select namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kodeorg."'";
	$res=fetchdata($str);
	$hasil=$res[0]['namaorganisasi'];
	
	return $hasil;    
}

function getNamaAruskas($kodeorg){
	global $dbname;
	global $owlPDO;
	
	$hasil="";
	$str="select nama_aruskas from ".$dbname.".keu_5aruskas where noaruskas='".$kodeorg."'";
	$res=fetchdata($str);
	$hasil=$res[0]['nama_aruskas'];
	
	return $hasil;    
}

function getNamaAkun($noakun){
	global $dbname;
	global $owlPDO;
	
	$hasil="";
	$str="select namaakun from ".$dbname.".keu_5akun where noakun='".$noakun."'";
	$res=fetchdata($str);
	$hasil=$res[0]['namaakun'];
	
	return $hasil;    
}


function getNamaBrg($kodebarang){
	global $dbname;
	global $owlPDO;
	
	$hasil="";
	$str="select namaproduk from ".$dbname.".msproduk where kodeproduk='".$kodebarang."'";
	$res=fetchdata($str);
	$hasil=$res[0]['namaproduk'];
	
	return $hasil;    
}

function getNamaKaryawan($karyawanid){
	global $dbname;
	global $owlPDO;
	$retnama='';
	if($karyawanid!=''){		
		$strAv=$owlPDO->query("select namakaryawan from ".$dbname.".datakaryawan where  karyawanid in(".$karyawanid.")");
		$strAv->setFetchMode(PDO::FETCH_OBJ);
		$no=0;
		while($barAv=$strAv->fetch()){
			if($no==0){
			   $retnama=$barAv->namakaryawan;
			}else{				   
				$retnama.=",".$barAv->namakaryawan;#comma separated
			}
			$no+=1;   
		}
		return $retnama;    
	}else{
		return $retnama;   
	}
}

function getJabatanKaryawan($karyawanid)
{
        global $dbname;
        global $owlPDO;
        $strAv=$owlPDO->query("select b.namajabatan from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5jabatan b on a.kodejabatan=b.kodejabatan where  karyawanid in(".$karyawanid.")");		
        $strAv->setFetchMode(PDO::FETCH_OBJ);
        $retnama='';
        $no=0;
        while($barAv=$strAv->fetch()){
                if($no==0)
                       $retnama=$barAv->namajabatan;
                   else
                       $retnama.=",".$barAv->namajabatan;#comma separated
         $no+=1;   
        }

        return $retnama;    
}

function getDepartemen($karyawanid){
	global $dbname;
    global $owlPDO;
    
	$departemen='';
    $str="select b.nama as departemen from ".$dbname.".datakaryawan a left join ".$dbname.".sdm_5departemen b on a.bagian=b.kode where a.karyawanid='".$karyawanid."'";
	$res=fetchdata($str);
	$departemen=$res[0]['departemen'];
	
	return $departemen;    
}

function getNamaSupplier($supplierid){
	global $dbname;
    global $owlPDO;
    
	$suppliername='';
    $str="select vendorname from ".$dbname.".msvendor where vendorcode='".$supplierid."'";
	$res=fetchdata($str);
	if ($res) {
		$suppliername=$res[0]['vendorname'];
	}
	
	return $suppliername;    
}

function getNamaCustomer($id){
	global $dbname;
    global $owlPDO;
    
	$hasil='';
    $str="select custname from ".$dbname.".mscustomer where custcode='".$id."'";
	$res=fetchdata($str);
	if ($res) {
		$hasil=$res[0]['custname'];
	}
	
	return $hasil;    
}

function getNamaProduk($id){
	global $dbname;
    global $owlPDO;
    
	$hasil='';
    $str="select namaproduk from ".$dbname.".msproduk where kodeproduk='".$id."'";
	$res=fetchdata($str);
	$hasil=$res[0]['namaproduk'];
	
	return $hasil;    
}

function getPlant($id){
	global $dbname;
    global $owlPDO;
    
	$hasil='';
    $str="select descode1 from ".$dbname.".msunit where unitcode='".$id."'";
	$res=fetchdata($str);
	$hasil=$res[0]['descode1'];
	
	return $hasil;    
}
function getNik($karyawanid){
	global $dbname;
    global $owlPDO;
    
	$nik='';
    $str="select nik from ".$dbname.".datakaryawan where karyawanid='".$karyawanid."'";
	$res=fetchdata($str);
	$nik=$res[0]['nik'];
	
	return $nik;    
}
function getNopol($kodevhc,$detail=''){
	global $dbname;
    global $owlPDO;
    
	$nopol='';
    $str="select nopol,detailvhc from ".$dbname.".vhc_5master where kodevhc='".$kodevhc."'";
	$res=fetchdata($str);
	if($detail=='d' or $detail!=''){
		$nopol=$res[0]['detailvhc'];
	}else{		
		$nopol=$res[0]['nopol'];
	}
	
	return $nopol;    
}

function getUnit($id){
	global $dbname;
    global $owlPDO;
    
	$hasil='';
    $str="select unitname from ".$dbname.".msunit where unitcode='".$id."'";
	$res=fetchdata($str);
	$hasil=$res[0]['unitname'];
	
	return $hasil;    
}

function getDivisi($id,$id2){
	global $dbname;
    global $owlPDO;
    
	$hasil='';
    $str="select divname from ".$dbname.".msdivisi where unitcode='".$id."' and divcode='".$id2."'";
	$res=fetchdata($str);
	if ($res) {
		$hasil=$res[0]['divname'];
	}
	
	return $hasil;    
}

function getFieldName($TABLENAME,$output)
{
//get Fieldname on the table mentioned
//this is general purposed
//return value available on array or string like <option value...>
	global $dbname;
	global $owlPDO;
	$option='';
	$arrReturn=Array();
        	try { 
	$strUx=$owlPDO->query("select * from ".$dbname.".".$TABLENAME." limit 1");
                    $raw_column_data = $strUx->fetchAll();
                    $jlh_Kolom=$strUx->columnCount();
                            for($x=0;$x<$jlh_Kolom;$x++){
                                    $test=$strUx->getColumnMeta($x);
                                    $column_names[] = $test['name'];
                                    array_push($arrReturn, $test['name']);
                                    $option.="<option value='".$test['name']."'>".$test['name']."</option>"; 
                            } 
	} catch (PDOException $e){
                        echo " Gagal: ".$e->getMessage(); //return exception
                        return false;
	}         
        
	if($output=='array')
	  return $arrReturn;
	else
	  return $option; 
}

function printTableController($TABLENAME)
{
//seacrh controller for table query
//javascript function stated in tablebrowser.js	
$field=getFieldName($TABLENAME,'option');
echo"<br>".$_SESSION['lang']['find']." <input type=text class=myinputtext id=txtcari onkeypress=\"return tanpa_kutip(event);\" size=15 maxlength=20  placeholder='Kata Pencarian'> ".$_SESSION['lang']['oncolumn'].":<select id=field>".$field."</select>
    ".$_SESSION['lang']['order']." <select id=order1>".$field."</select>,<select id=order2>".$field."</select>
	 ";
echo"<button class=mybutton onclick=\"browseTable('".$TABLENAME."');\">".$_SESSION['lang']['find']."</button>";
	
}

function printSearchOnTable($TABLENAME,$fieldname,$texttofind,$order='',$curpage=0,$MAX_ROW=300)
{
 
 	//$MAX_ROW plese change this if required
	global $dbname;
	global $owlPDO;
	$offset   =$curpage*$MAX_ROW;//
	$disp_page=$curpage+1;
	$field=getFieldName($TABLENAME,'array'); 
	
	if($texttofind==''){
		exit("Warning:Kolom cari tidak boleh kosong");
	}
	
	if($texttofind!='')
	{
		$where=" where ".$fieldname." like '%".$texttofind."%' and username not like 'tim.owl%' "; 
	}	
	
	$strXu=$owlPDO->query("select * from ".$dbname.".".$TABLENAME." ".$where."  order by ".$order." limit ".$offset.",".$MAX_ROW);
	// $a="select * from ".$dbname.".".$TABLENAME." ".$where."  order by ".$order." limit ".$offset.",".$MAX_ROW;
	// echo $a;
	$strXu->setFetchMode(PDO::FETCH_NUM);
	//get num rows of the query
	//and create page navigator
	// $strXur=$owlPDO->query("select * from ".$dbname.".".$TABLENAME." ".$where);
	// $strXur->setFetchMode(PDO::FETCH_NUM);
	$numrows=owlBaris($strXur);
	if($numrows>$MAX_ROW)
	{
		if(($numrows%$MAX_ROW)!=0)
		    $page=(floor($numrows/$MAX_ROW))+1;
		else
		    $page=$numrows/$MAX_ROW;	
	}	
	else
	{
		$page=1;
	}
	echo $_SESSION['lang']['page']." ".$disp_page." ".$_SESSION['lang']['of']." ".$page." (Max.".$MAX_ROW."/".$_SESSION['lang']['page'].")";
	echo" [ ".$_SESSION['lang']['gotopage'].":<select id=page>";
	for($y=0;$y<$page;$y++)
	{
		echo"<option value=".$y.">".($y+1)."</option>";
	}
	echo "</select> <button onclick=\"navigatepage('".$TABLENAME."');\" class=mybutton>Go</button> ]";
		
	echo"<table class=sortable cellspacing=1 border=0>
	     <thead><tr class=rowheader>";
	for($x=0;$x<count($field);$x++)
	{
		echo"<td>".$field[$x]."</td>";
	}	 
	echo"</tr></thead><tbody>";
	$num=0;
	while($barXu=$strXu->fetch())
	{
		echo"<tr class=rowcontent>";
		for($x=0;$x<count($field);$x++)
		{
			echo"<td>".$barXu[$x]."</td>";
		}	
		echo"</tr>";		
	}
	echo"</tbody><tfoot></tfoot></table>";
	
	//echo "This function no longer available";
}
#send mail from win 32
function sendMail($subject,$content,$from='',$to,$cc='',$bcc='',$replyTo='')//for win
{
	//FOR WINDOW SERVER ONLY
	//$subject,$content and $to is obligatory
	$headers  ='MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    if($from!='')
    $headers .= "From: ".$from. "\r\n";
    if($cc!='')
    $headers .= "Cc: ".$cc. "\r\n";
    if($bcc!='')
	$headers .= 'Bcc: '.$bcc. "\r\n";
	if($replyTo!='')
	$headers .= 'Reply-To: '.$replyTo. "\r\n";
    if(mail($to,$subject,$content,$headers))
	    return true;
	else
	  {
	    return false;
	  }
}
//send mail on ubuntu linux
function  kirimEmail($to,$cc = "",$subject,$body,$mailType='text/html')//multiple recipient separated by comma
{
    global $owlPDO;
    global $dbname;
	
	// $to="simamora.hendry@gmail.com";
	
    #default
    $port=25;
    $ssl='YES';
    $str=$owlPDO->query("select * from ".$dbname.".setup_remotetimbangan where lokasi='MAILSYS'");
    $str->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$str->fetch()){
        $host=trim($bar->ip);
        $username=trim($bar->username);
        $password=trim($bar->password);
        $port=trim($bar->port);
        $ssl=strtoupper(trim($bar->dbname));
    }

    if($ssl=='YES' or $ssl=='TRUE' or strtoupper($ssl)=='SSL')
    {
        $host="ssl://".$host;
    }
    #mailType posible value 'text/html' or 'text/text'
    
     require_once "Mail.php";   
     $from = "OWL-Plantation<owl@ksp-agro.com>";
     $headers = array ('From' => $from,
       'To' => $to,
       'Cc' => $cc,  
       'Subject' => $subject,
       'Content-Type'=> $mailType);
     $mail = Mail::factory('smtp',
       array ('host' => $host,
         'auth' => true,
         'port' => $port,
         'username' => $username,
         'password' => $password));     
/*
     echo "<pre>";
     print_r($headers);
     echo "<br>";
     print_r($mail);
     echo "<br>";
     echo "</pre>";
*/
     // if($mailType=='text/html')
     // {
         // $body.="<br><br>
                 // <i style='font-size:10pt'>Follow <a href='http://192.168.2.9/owl'>this link</a> to connect to OWL plantation system from office network.<br>
                 // Or <a href='http://202.152.52.21/owl'>this link</a> from public network.
                 // </i>";
     // }    
	 $toto=explode(",",$to);
	 foreach($toto as $key =>$val){
           $kirim = $mail->send($val, $headers, $body);
       }
     if (PEAR::isError($kirim)) {
       return $kirim->getMessage();
     	//return true;
      } else {
       return true;
      }
     return true;
} 

## SEND EMAIL WITH ATTACHMENT AND MULTIPLE RECEIPT SEPARATED BY COMMA
function kirimEmailatt($to,$cc="",$subject,$body,$mailType='text/html',$attachment=""){
	require_once "Mail.php";
	require_once "Mail/mime.php";
    global $owlPDO;
    global $dbname;
    
	$port=25;
    $ssl='YES';
    $str=$owlPDO->query("select * from ".$dbname.".setup_remotetimbangan where lokasi='MAILSYS'");
    $str->setFetchMode(PDO::FETCH_OBJ);
    while($bar=$str->fetch()){
        $host=trim($bar->ip);
        $username=trim($bar->username);
        $password=trim($bar->password);
        $port=trim($bar->port);
        $ssl=strtoupper(trim($bar->dbname));
    }

    if($ssl=='YES' or $ssl=='TRUE' or strtoupper($ssl)=='SSL'){
        $host="ssl://".$host;
    }
    
	$from = "OWL-Plantation<owl@ksp-agro.com>";
	$headers = array ('From' => $from,
		'To' => $to,
		'Cc' => $cc,  
		'Subject' => $subject,
		'Content-Type'=> $mailType);
	$mail = Mail::factory('smtp',
		array ('host' => $host,
			'auth' => true,
			'port' => $port,
			'username' => $username,
			'password' => $password
		)
	);
	
	$text="";
	
	if($mailType=='text/html'){
		$body.="<br><br>
                 <i style='font-size:10pt'>Follow <a href='http://owl.ksp-agro.com'>this link</a> to connect to OWL plantation system.
                 </i>";
	}
	
	$mime = new Mail_mime(array('eol' => $crlf));

	$mime->setTXTBody($text);
	$mime->setHTMLBody($body);
	if($attachment!=''){
		$mime->addAttachment($attachment, 'text/plain');		
	}

	$bodys = $mime->get();
	$hdrs = $mime->headers($headers);

	$toto=explode(",",$to);
	foreach($toto as $key =>$val){
		$kirim = $mail->send($val, $hdrs, $bodys);
	}
	
	if(PEAR::isError($kirim)){
		return $kirim->getMessage();
	}else{
		return true;
	}
	return true;
}



function jumlahbulandepan($tgl,$jumlahbulan){
	#membuat tanggal kemarin dari parameter kiriman
	#$tgl format : 2015-12-25;
	if(strlen($tgl)<8){
		$tgl=$tgl.'01';
	}
	$tgl=str_replace('-','',$tgl);
	$newdate = strtotime("+".$jumlahbulan." month",strtotime($tgl));
	$newdate = date('Y-m', $newdate);
	return $newdate;
}


function jumlahtglbulandepan($tgl,$jumlahbulan){
	#membuat tanggal kemarin dari parameter kiriman
	#$tgl format : 2015-12-25;
	$tgl=str_replace('-','',$tgl);
	$newdate = strtotime("+".$jumlahbulan." month",strtotime($tgl));
	$newdate = date('Y-m-d', $newdate);
	return $newdate;
}



function readCountry($file)
{
$comment = "#";

$fp = fopen($file, "r");
$lin=-1;
while (!feof($fp)) {
$line = fgets($fp, 4096); // Read a line.
    if(!preg_match("/^#/",$line) AND $line!='')
	{
    $lin+=1;
	$pieces = explode("=", $line);
    $name = trim($pieces[0]);
    //$code = trim($pieces[1]);
    $code = isset($pieces[1])? trim($pieces[1]): '';
	$curr = isset($pieces[2])? trim($pieces[2]): '';
	$cont = isset($pieces[3])? trim($pieces[3]): '';
    $country[$lin][0] = $name;
	$country[$lin][1] = $code;
	$country[$lin][2] = $curr;
	$country[$lin][3] = $cont;
	}
  }

fclose($fp);
return $country;
}

function readTextFile($file)
{
$handle = fopen($file, "r");
$contents = fread($handle, filesize($file));
fclose($handle);
return $contents;
}

function readOrgType($file)
{
$comment = "#";

$fp = fopen($file, "r");
$lin=0;
while (!feof($fp)) {
$line = fgets($fp, 4096); // Read a line.
    if(!preg_match("/^#/",$line) AND $line!='')
	{
    $lin+=1;
	$pieces = explode("=", $line);
    $code = trim($pieces[0]);
    $name = trim($pieces[1]);
    $orgtype[$lin][0] = $code;
	$orgtype[$lin][1] = $name;
	}
  }

fclose($fp);
return $country;
}

function numToMonth($int,$lang='E',$format='short')
{
	$arr=Array();
	$arr[1]['E']['short']='Jan';
	$arr[1]['I']['short']='Jan';
	$arr[1]['E']['long']='January';
	$arr[1]['I']['long']='Januari';
		$arr[2]['E']['short']='Feb';
		$arr[2]['I']['short']='Peb';
		$arr[2]['E']['long']='February';
		$arr[2]['I']['long']='Februari';	
	$arr[3]['E']['short']='Mar';
	$arr[3]['I']['short']='Mar';
	$arr[3]['E']['long']='Maret';
	$arr[3]['I']['long']='Maret';	
		$arr[4]['E']['short']='Apr';
		$arr[4]['I']['short']='Apr';
		$arr[4]['E']['long']='April';
		$arr[4]['I']['long']='April';			
	$arr[5]['E']['short']='May';
	$arr[5]['I']['short']='Mei';
	$arr[5]['E']['long']='May';
	$arr[5]['I']['long']='Mei';	
		$arr[6]['E']['short']='Jun';
		$arr[6]['I']['short']='Jun';
		$arr[6]['E']['long']='Juni';
		$arr[6]['I']['long']='Juni';
	$arr[7]['E']['short']='Jul';
	$arr[7]['I']['short']='Jul';
	$arr[7]['E']['long']='July';
	$arr[7]['I']['long']='Juli';	
		$arr[8]['E']['short']='Aug';
		$arr[8]['I']['short']='Agu';
		$arr[8]['E']['long']='August';
		$arr[8]['I']['long']='Agustus';
	$arr[9]['E']['short']='Sep';
	$arr[9]['I']['short']='Sep';
	$arr[9]['E']['long']='September';
	$arr[9]['I']['long']='September';	
		$arr[10]['E']['short']='Oct';
		$arr[10]['I']['short']='Okt';
		$arr[10]['E']['long']='October';
		$arr[10]['I']['long']='Oktober';
	$arr[11]['E']['short']='Nov';
	$arr[11]['I']['short']='Nop';
	$arr[11]['E']['long']='November';
	$arr[11]['I']['long']='Nopember';	
		$arr[12]['E']['short']='Dec';
		$arr[12]['I']['short']='Des';
		$arr[12]['E']['long']='December';
		$arr[12]['I']['long']='Desember';
		
//find and return		
	$int=intval($int);
	$result = "";
	if(isset($arr[$int][$lang][$format])){
		$result = $arr[$int][$lang][$format];
	}else{
		$result = "undefined";
	}
    return $result;
}

//fungsi untuk memeriksa apakah periode transaksi normal

function dateFilemanager($date)
{
	$val = '';
	$expDate = explode(' ',$date);
	$date = $expDate[0];
	$time = $expDate[1];
	
	$_q=str_replace("-","",$date);
	$expTime = explode(':',$time);
	
	$_retval=substr($_q,6,2)."-".numToMonth(substr($_q,4,2),'E','short')."-".substr($_q,0,4)." ".$expTime[0].":".$expTime[1];
	
	return $_retval;
}

function isTransactionPeriod()
{
	$stat=true;
	if($_SESSION['org']['period']['start']=='')
	  $stat=false;
	if($_SESSION['org']['period']['end']=='')
	  $stat=false;
	if($_SESSION['org']['period']['bulan']=='')
	  $stat=false;
	if($_SESSION['org']['period']['tahun']=='')
	 $stat=false; 
return $stat;
}

function readCSV($file,$separator=',',$comment='#')
{
#read CSV file with optional separator and commented line
#return an array
$fp = fopen($file, "r");
while (!feof($fp)) {
$line = fgets($fp, 4096); // Read a line.
    if(!preg_match("/^".$comment."/",$line) AND $line!='')
	{
	 $baris[] = explode($separator, $line);
	}
  }
return $baris;
} 
function hitungHrMinggu($bln1,$tgl1,$thn1,$date2,$hrLbr){   
    #format $date2=dd-mm-YYYY
    global $dbname;
    global $owlPDO;
    $i=0;
    $sum=0;
    if($hrLbr==''){
        $hrLbr=0;
    }
    do{
      //
       // mengenerate tanggal berikutnyahttp://blog.rosihanari.net/menghitung-jumlah-hari-minggu-antara-dua-tanggal/
       $tanggal = date("d-m-Y", mktime(0, 0, 0, $bln1, $tgl1+$i, $thn1));
       // cek jika harinya minggu, maka counter $sum bertambah satu, lalu tampilkan tanggalnya
       if (date("w", mktime(0, 0, 0, $bln1, $tgl1+$i, $thn1)) == 0){
          $sum++;
       }
       if($hrLbr==1){
           $sLbr=$owlPDO->query("select distinct * from ".$dbname.".sdm_5harilibur where 
                  tanggal='".tanggalsystem($tanggal)."' and regional='".$_SESSION['empl']['regional']."'");
           $sLbr->setFetchMode(PDO::FETCH_OBJ);
           $count=owlBaris($sLbr);
           if($count==1){
               $sum+=1;
           }
       
       }
       // increment untuk counter looping
       $i++;
    }
    while ($tanggal != $date2);  
    return $sum; 
}

function rangeTanggal($date1, $date2){

    $day = 60*60*24;

    $date1 = strtotime($date1);
    $date2 = strtotime($date2);

    $days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between

    $dates_array = array();
    $dates_array[] = date('Y-m-d',$date1);

    for($x = 1; $x < $days_diff; $x++){
        $dates_array[] = date('Y-m-d',($date1+($day*$x)));
    }

    $dates_array[] = date('Y-m-d',$date2);
    if($date1==$date2){
        $dates_array = array();
        $dates_array[] = date('Y-m-d',$date1);        
    }
    return $dates_array;
}



function rangeTanggalarr($date1, $date2){

    $day = 60*60*24;

    $date1 = strtotime($date1);
    $date2 = strtotime($date2);

    $days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between

    $dates_array = array();
    $dates_array[date('Y-m-d',$date1)] = date('Y-m-d',$date1);

    for($x = 1; $x < $days_diff; $x++){
        $dates_array[date('Y-m-d',($date1+($day*$x)))] = date('Y-m-d',($date1+($day*$x)));
    }

    $dates_array[date('Y-m-d',$date2)] = date('Y-m-d',$date2);
    if($date1==$date2){
        $dates_array = array();
        $dates_array[date('Y-m-d',$date1)] = date('Y-m-d',$date1);        
    }
    return $dates_array;
}



/**
 * checkPostGet
 * Check if not isset $_POST then $_GET else blank / 0
 */
function checkPostGet($str, $blankVal) {
	if(isset($_POST[$str])) {
		return $_POST[$str];
	} elseif(isset($_GET[$str])) {
		return $_GET[$str];
	} else {
		return $blankVal;
	}
}

/**
 * setIt
 * Set $value if $var not set
 */
function setIt(&$var, $value) {
	if(!isset($var)) $var = $value;
	return $var;
}








/*range bulan function*/
 
function month_inbetween($month1, $month2){

    if($month1>$month2) {
        exit("Error:Month 1 > Month 2");
    }

	$day = 60*60*24;
	$date1=$month1.'-01';
	$date2=$month2.'-01';
    $date1 = strtotime($date1);
    $date2 = strtotime($date2);

    $days_diff = round(($date2 - $date1)/$day); // Unix time difference devided by 1 day to get total days in between

    $dates_array = array();
    $dates_array[substr(date('Y-m-d',$date1),0,7)] = substr(date('Y-m-d',$date1),0,7);

    for($x = 1; $x < $days_diff; $x++){
        $dates_array[substr(date('Y-m-d',($date1+($day*$x))),0,7)] = substr(date('Y-m-d',($date1+($day*$x))),0,7);
    }

    $dates_array[substr(date('Y-m-d',$date2),0,7)] = substr(date('Y-m-d',$date2),0,7);
    if($date1==$date2){
        $dates_array = array();
        $dates_array[substr(date('Y-m-d',$date1),0,7)] = substr(date('Y-m-d',$date1),0,7);        
    }
    return $dates_array;
	
}

/**
 * Clear Invalid non UTF-8 character
 */
function clearInvalidChar($strInput) {
    $regex = '/[^(\x20-\x7F)]*/';
    return preg_replace($regex, '', $strInput);
}


function periodelalu($per)
{
    #bentuk periode lalu
    $thnIni=substr($per,0,4);
    $blnIni=substr($per,5,2);
    if($blnIni=='01')
    {
        $blnLalu=12;
        $thnLalu=$thnIni-1;
    }
    else
    {  
      $blnLalu=$blnIni-1; 
      $thnLalu=$thnIni;
    }

    if(strlen($blnLalu)<2)
    {
        $blnLalu="0".$blnLalu;
    }
    $perLalu=$thnLalu."-".$blnLalu;
    return $perLalu;
}

/**
 * Fungsi untuk periode berikutnya
*/
function periodeberikut($per)
{    
   $thnIni=substr($per,0,4);
   $blnIni=substr($per,5,2);
   
   if($blnIni=='12')
   {
       
       $blnBerikut=1;
       $thnBerikut=$thnIni+1;
   }
   else
   {  
     $blnBerikut=$blnIni+1; 
     $thnBerikut=$thnIni;
   }

   if(strlen($blnBerikut)<2)
   {
       $blnBerikut="0".$blnBerikut;
   }
  
   $perBerikut=$thnBerikut."-".$blnBerikut;
   return $perBerikut;
}

/**
 * Print/Show special character UTF-8
 */
function printSpecialChar($text){
	$resulttext = iconv('UTF-8', 'windows-1252', $text);
	return $resulttext;
}

function saveKutip($text){
	$newText = str_replace('"','',$text);
	$newText = str_replace("'",'',$newText);
	return $newText;
}

function changeKutipChar($text){
	$resulttext = str_replace ("'", "&prime;", $text );
	$resulttext = str_replace ('"', "&quot;", $resulttext );
	return $resulttext;
}

function fixnan($val){
	if (is_nan($val)) {
		$val=0;
	}else if (is_infinite($val)){
		$val=0;
	}
	return $val;
}

//exp romawi(1);
function romawi($angka){
    $hsl = "";
    if($angka<1||$angka>3999){
        $hsl = "Batas Angka 1 s/d 3999";
    }else{
         while($angka>=1000){
             $hsl .= "M";
             $angka -= 1000;
         }
         if($angka>=500){
             if($angka>500){
                 if($angka>=900){
                     $hsl .= "CM";
                     $angka-=900;
                 }else{
                     $hsl .= "D";
                     $angka-=500;
                 }
             }
         }
         while($angka>=100){
             if($angka>=400){
                 $hsl .= "CD";
                 $angka-=400;
             }else{
                 $angka-=100;
             }
         }
         if($angka>=50){
             if($angka>=90){
                 $hsl .= "XC";
                  $angka-=90;
             }else{
                $hsl .= "L";
                $angka-=50;
             }
         }
         while($angka>=10){
             if($angka>=40){
                $hsl .= "XL";
                $angka-=40;
             }else{
                $hsl .= "X";
                $angka-=10;
             }
         }
         if($angka>=5){
             if($angka==9){
                 $hsl .= "IX";
                 $angka-=9;
             }else{
                $hsl .= "V"; 
                $angka-=5;
             }
         }
         while($angka>=1){
             if($angka==4){
                $hsl .= "IV"; 
                $angka-=4;
             }else{
                $hsl .= "I";
                $angka-=1;
             }
         }
    }
    return ($hsl);
}

function owlBaris($var){
	#applied only on pdo select query
	#not affectecd on insert,update,delete
	global $owlPDO;
	$query=$var->queryString;
	$pos = strpos(strtolower($query), 'limit');
	$posunion = strpos(strtolower($query), 'union');
	$posgroup = strpos(strtolower($query), 'group');	
	$posshw	=strpos(strtolower($query), 'show table');
	if($pos===false && $posunion===false && $posgroup===false && $posshw===false){
		$str="select count(*) as num ".strstr($query, 'from');
	    $res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_NUM);
		$baris=0;
	 	while($bar=$res->fetch()){
	 		$baris=$bar[0];
	 	}
 	}else{
	    $res=$owlPDO->query($var->queryString) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_NUM); 	
	 	$baris=0;
	 	while($bar=$res->fetch()){
	 		$baris++;
	 	}			
 	}

 return $baris;	
}

function showLegend($idlaporan,$tipe=null){
	global $owlPDO;
	
	$result = "<table cellpading=1 cellspacing=1 border=0 class=sortable width=100%>
		<thead>
		<tr align=center>
			<td>Legend</td>
			<td>Keterangan</td>
		</tr>
		</thead>
		<tbody>";
	
	if($tipe==1){
		$str = "select * from ".$dbname.".bi_5siklusdt where idsiklus = '".$idlaporan."'";
	}else{
		$str = "select * from ".$dbname.".bi_5warnalaporan where idlap = '".$idlaporan."'";
	}
	$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
	$res->setFetchMode(PDO::FETCH_ASSOC);
	while($bar = $res->fetch()){
		$result .= "<tr class=rowcontent>
			<td bgcolor='".$bar['warna']."' style='width:10px;'>&nbsp;</td>
			<td style='text-align:justify'>".$bar['keterangan']."</td>
		</tr>";
	}	
	
	$result .= "</tbody>
	</table>
	<div style='width:100%;text-align:right;color:#3399FF;padding-top:5px;'><span style='cursor:pointer;' onclick=\"detaillaporangraph('".$idlaporan."','".$tipe."',event)\">Detail Report</span></div>";
	
	return $result;
}


function tglbulanlalu($tgl){
	#membuat tanggal kemarin dari parameter kiriman
	#$tgl format : 2015-12-25;
	$tgl=str_replace('-','',$tgl);
	$newdate = strtotime('-1 month',strtotime($tgl));
	$newdate = date('Y-m-d', $newdate);
	return $newdate;
}


function tglbulandepan($tgl){
	#membuat tanggal kemarin dari parameter kiriman
	#$tgl format : 2015-12-25;
	$tgl=str_replace('-','',$tgl);
	$newdate = strtotime('+1 month',strtotime($tgl));
	$newdate = date('Y-m-d', $newdate);
	return $newdate;
}

function tglakhir($tgl){
	#menbuat tanggal terakhir di periode parameter kiriman
	#$tgl format : 2015-12-25;
	$tglakhir = date('Y-m-t', strtotime($tgl));
	return $tglakhir;
}


function tglkemarin($tgl){
	#membuat tanggal kemarin dari parameter kiriman
	#$tgl format : 2015-12-25;
	$tgl=str_replace('-','',$tgl);
	$newdate = strtotime('-1 day',strtotime($tgl));
	$newdate = date('Y-m-d', $newdate);
	return $newdate;
}

function tglbesok($tgl){
	#membuat tanggal kemarin dari parameter kiriman
	#$tgl format : 2015-12-25;
	$tgl=str_replace('-','',$tgl);
	$newdate = strtotime('+1 day',strtotime($tgl));
	$newdate = date('Y-m-d', $newdate);
	return $newdate;
}

// menambahkan menit
function tambahmenit($jlhmenit){
	$date = date_create(date('d-m-Y H:i:s'));
	date_add($date, date_interval_create_from_date_string($jlhmenit.' minutes'));
	return date_format($date, 'Y-m-d H:i:s');
}

// mengurangi menit
function kurangmenit($jlhmenit){
	$date = date_create(date('d-m-Y H:i:s'));
	date_add($date, date_interval_create_from_date_string('-'.$jlhmenit.' minutes'));
	return date_format($date, 'Y-m-d H:i:s');
}

//selisih tanggal (hari)
function selisihari($tgl1,$tgl2){
	//format tangal Y-m-d // 2015-12-31
	$selisih = ((abs(strtotime ($tgl2) - strtotime ($tgl1)))/(60*60*24));
	return $selisih;
}

//selisih tanggal (hari)
function selisitgl($tgl1,$tgl2){
	//format tangal Y-m-d // 2015-12-31
	$selisih = (((strtotime ($tgl1) - strtotime ($tgl2)))/(60*60*24));
	return $selisih;
}

function getCountRows($dbname,$tableName,$where=null) {
	global $owlPDO;
	
	$strXur=$owlPDO->query("select * from ".$dbname.".".$tableName." where ".$where."");
	$strXur->setFetchMode(PDO::FETCH_NUM);
	$numrows=owlBaris($strXur);
	
	return $numrows;
}

function setheadreport($kdorg,$kdpt='')
{
	global $dbname;
	global $owlPDO;
	
	$arrHead = array();
	if($kdpt=='')
	{
		$optPt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$kdorg."'");
		$str=$owlPDO->query("select kodeorganisasi,alamat,telepon,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$optPt[$kdorg]."'");		
		$str->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$str->fetch();
		
		$kdpt=$optPt[$kdorg];
	}
	else
	{
		$str=$owlPDO->query("select kodeorganisasi,alamat,telepon,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$kdpt."'");		
		$str->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$str->fetch();
		
	}
	
	$arrHead['alamat'] = $bar['alamat'];
	$arrHead['telepon'] = $bar['telepon'];
	$arrHead['nama'] = $bar['namaorganisasi'];
	if($bar['kodeorganisasi']=='TML')
	{
		$arrHead['logo'] = "images/ksp.jpg"; 
	}
	else if($bar['kodeorganisasi']=='BAS')
	{
		$arrHead['logo'] = "images/ksp.jpg"; 
	}
	else
	{

		$str=$owlPDO->query("select kodeorganisasi,alamat,telepon,namaorganisasi,kodepos from ".$dbname.".organisasi where kodeorganisasi='".$kdpt."'");		
		$str->setFetchMode(PDO::FETCH_ASSOC);
		$bar=$str->fetch();
		$arrHead['alamat'] = $bar['alamat'];
		$arrHead['telepon'] = $bar['telepon'];
		$arrHead['nama'] = $bar['namaorganisasi'];
		$arrHead['kodepos'] = $bar['kodepos'];
		$arrHead['logo'] = "images/ksp.jpg";
	
	
	}
	
	return $arrHead;
}

function getMenu($name,$tipe='') {
		include('zLib.php');
        global $dbname;
		global $owlPDO;
		

		$s="select id, caption from ".$dbname.".menu where action='".$name."'";
		$r=fetchdata($s);
		
		$postMenu=strtoupper($r[0]['caption']);
		
		#<span class='lblnotification'><label class='badge1' data-badge=*></label></span>
		
		$str="SELECT f.*
		FROM (
			SELECT @id AS _id, (SELECT @id := parent FROM ".$dbname.".menu WHERE id = _id)
			FROM (SELECT @id := ".$r[0]['id']." ) tmp1
			JOIN ".$dbname.".menu ON @id <> 0
			) tmp2
		JOIN ".$dbname.".menu f ON tmp2._id = f.Id
		order by action,parent";
		$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
		$res->setFetchMode(PDO::FETCH_ASSOC);
		while($bar=$res->fetch()){
			$menu[$bar['caption']]=strtoupper(strtolower($bar['caption']));
		}
		
		
		if ($tipe == '') {
        	return $postMenu."<span style='font-size:13px;font-weight:normal;float:right'>".implode(" > ",$menu)."</span>";
		} else {
        	return "<span id=pathmenu style='font-size:13px;font-weight:normal;'>".implode(" - ",$menu)."</span>";
		}
}


function weeks($month, $year){
    $firstday = date("w", mktime(0, 0, 0, $month, 1, $year)); 
    $lastday = date("t", mktime(0, 0, 0, $month, 1, $year)); 
    $count_weeks = 1 + ceil(($lastday-7+$firstday)/7);
    return $count_weeks;
}

function getOrgDetail($tipe){
	global $dbname;
	
	$optOrg[$_SESSION['empl']['lokasitugas']] = $_SESSION['empl']['lokasitugas'];
	if(isset($_SESSION['orgdet'])){
		foreach($_SESSION['orgdet'] as $key=>$val){
			if($val!=''){				
				$optOrg[$_SESSION['orgdet'][$key]] = $_SESSION['orgdet'][$key];
			}
		}
	}

	if($tipe==1){
		##LIST UNIT
		foreach($optOrg as $key){
			$optMyOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$key."'",'',true);
			$optNewOrg[$key] = $optMyOrg[$key];
		}
	}else if($tipe==2){
		##LIST UNIT
		$nourut = 0;
		$optNewOrg = "";
		foreach($optOrg as $key){
			if($nourut==0){
				$optNewOrg .= "'".$key."'";
			}else{
				$optNewOrg .= ",'".$key."'";
			}
			$nourut++;
		}
	}else if($tipe==3){
		##LIST PT
		$optNewOrg[$_SESSION['empl']['kodeorganisasi']] = $_SESSION['empl']['kodeorganisasi'];
		foreach($optOrg as $key){
			$optMyPt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$key."'",'',true);
			$optMyOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$optMyPt[$key]."'",'',true);
			$optNewOrg[$optMyPt[$key]] = $optMyOrg[$optMyPt[$key]];
		}
	}else if($tipe==4){
		##LIST PT
		$nourut = 0;
		$optNewOrg = "";
		foreach($optOrg as $key){
			$optMyPt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$key."'",'',true);
			if($nourut==0){
				$optNewOrg .= "'".$optMyPt[$key]."'";
			}else{
				$optNewOrg .= ",'".$optMyPt[$key]."'";
			}
			$nourut++;
		}
	}else if($tipe==9){
		##LIST UNIT
		foreach($optOrg as $key){
			$optMyOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$key."'",'',true);
			$optNewOrg[$key] = $key." - ".$optMyOrg[$key];
		}
	}else if($tipe==10){
		$optNewOrg[$_SESSION['empl']['lokasitugas']] = $_SESSION['empl']['lokasitugas'];
		if(isset($_SESSION['orgdet'])){
			foreach($_SESSION['orgdet'] as $key=>$val){
				$optNewOrg[$_SESSION['orgdet'][$key]] = $_SESSION['orgdet'][$key];
			}
		}
	}else if($tipe==11){
		##LIST UNIT
		foreach($optOrg as $key){
			$optMyOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$key."'",'',true);
			$optNewOrg[$key] = $optMyOrg[$key];
		}
	}else if($tipe==12){
		##LIST GUDANG CENTRAL
		foreach($optOrg as $key){
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$key."' and tipe='GUDANG'";
			$res=fetchdata($str);
			foreach($res as $val){
				$optNewOrg[$val['kodeorganisasi']] = $val['namaorganisasi'];				
			}
		}
	}else if($tipe==13){
		##LIST PABRIK
		foreach($optOrg as $key){
			$optMyPt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$key."'",'',true);
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$key."' and tipe in ('PABRIK','BULKING')";
			$res=fetchdata($str);
			foreach($res as $val){
				$optNewOrg[$val['kodeorganisasi']] = $val['namaorganisasi'];				
			}
		}
	}else if($tipe==14){
		##LIST GUDANG CENTRAL
		$nourut = 0;
		$optNewOrg = "";
		foreach($optOrg as $key){
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$key."' and tipe='GUDANG'";
			$res=fetchdata($str);
			foreach($res as $val){
				if($nourut==0){
					$optNewOrg .= "'".$val['kodeorganisasi']."'";
				}else{
					$optNewOrg .= ",'".$val['kodeorganisasi']."'";
				}
				$nourut++;
			}
		}
	}else if($tipe==15){
		##LIST tipeorganisasi
		foreach($optOrg as $key){
			$optMyOrg = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$key."'",'',true);
			$optNewOrg[$optMyOrg[$key]] = $optMyOrg[$key];
		}
	}else if($tipe==16){
		##LIST LIKE GUDANG
		foreach($optOrg as $key){
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$key."' and substr(tipe,1,6) = 'GUDANG'";
			$res=fetchdata($str);
			foreach($res as $val){
				$optNewOrg[$val['kodeorganisasi']] = $val['namaorganisasi'];				
			}
		}
	}else if($tipe==17){
		##LIST WORKSHOP
		foreach($optOrg as $key){
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$key."' and tipe='WORKSHOP'";
			$res=fetchdata($str);
			foreach($res as $val){
				$optNewOrg[$val['kodeorganisasi']] = $val['namaorganisasi'];				
			}
		}
	}else if($tipe==18){
		##LIST TRAKSI
		foreach($optOrg as $key){
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$key."' and tipe='TRAKSI'";
			$res=fetchdata($str);
			foreach($res as $val){
				$optNewOrg[$val['kodeorganisasi']] = $val['namaorganisasi'];				
			}
		}
	}else if($tipe==19){
		##LIST DIVISI
		foreach($optOrg as $key){
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$key."' and tipe in ('AFDELING','BIBITAN')";
			$res=fetchdata($str);
			foreach($res as $val){
				$optNewOrg[$val['kodeorganisasi']] = $val['namaorganisasi'];				
			}
		}
	}else if($tipe==20){
		##LIST DIVISI
		$nourut = 0;
		$optNewOrg = "";
		foreach($optOrg as $key){
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$key."' and tipe in ('AFDELING','BIBITAN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($nourut==0){
					$optNewOrg .= "'".$val['kodeorganisasi']."'";
				}else{
					$optNewOrg .= ",'".$val['kodeorganisasi']."'";
				}
				$nourut++;
			}
		}
	}else if($tipe==21){
		##LIST STATION
		foreach($optOrg as $key){
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$key."' and tipe in ('STATION')";
			$res=fetchdata($str);
			foreach($res as $val){
				$optNewOrg[$val['kodeorganisasi']] = $val['namaorganisasi'];				
			}
		}
	}else if($tipe==22){
		##LIST STATION
		$nourut = 0;
		$optNewOrg = "";
		foreach($optOrg as $key){
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where induk='".$key."' and tipe in ('STATION')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($nourut==0){
					$optNewOrg .= "'".$val['kodeorganisasi']."'";
				}else{
					$optNewOrg .= ",'".$val['kodeorganisasi']."'";
				}
				$nourut++;
			}
		}
	}else if($tipe==23){
		##LIST KEBUN
		foreach($optOrg as $key){
			$optMyPt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$key."'",'',true);
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$key."' and tipe='KEBUN'";
			$res=fetchdata($str);
			foreach($res as $val){
				$optNewOrg[$val['kodeorganisasi']] = $val['namaorganisasi'];				
			}
		}
	}else if($tipe==24){
		##LIST KEBUN
		$nourut = 0;
		$optNewOrg = "";
		foreach($optOrg as $key){
			$optMyPt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$key."'",'',true);
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$key."' and tipe in ('KEBUN')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($nourut==0){
					$optNewOrg .= "'".$val['kodeorganisasi']."'";
				}else{
					$optNewOrg .= ",'".$val['kodeorganisasi']."'";
				}
				$nourut++;
			}
		}
	}else if($tipe==25){
		##LIST PABRIK
		$nourut = 0;
		$optNewOrg = "";
		foreach($optOrg as $key){
			$optMyPt = makeOption($dbname,'organisasi','kodeorganisasi,induk',"kodeorganisasi='".$key."'",'',true);
			$str="select kodeorganisasi,namaorganisasi from ".$dbname.".organisasi where kodeorganisasi='".$key."' and tipe in ('PABRIK','BULKING')";
			$res=fetchdata($str);
			foreach($res as $val){
				if($nourut==0){
					$optNewOrg .= "'".$val['kodeorganisasi']."'";
				}else{
					$optNewOrg .= ",'".$val['kodeorganisasi']."'";
				}
				$nourut++;
			}
		}
	}else{
		##LIST UNIT
		foreach($optOrg as $key){
			$optMyOrg = makeOption($dbname,'organisasi','kodeorganisasi,namaorganisasi',"kodeorganisasi='".$key."'");
			$optNewOrg[$key] = $optMyOrg[$key];
		}
	}
	
	return $optNewOrg;
}

function getCountApproval($tipe,$kdunit='',$departemen='',$kodegol='',$karyawaniduser=''){
	global $dbname;
	global $owlPDO;
	
	$where = "";
	
	if($departemen!=''){
		##CEK PER DEPARTEMEN
		$str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$kdunit."' and jenispersetujuan='".$tipe."' and departemen='".$departemen."'";
		$res=fetchdata($str);
		$perdepartemen=$res[0]['kodeunit'];
		$where="";
		if($perdepartemen>0){
			$where.=" and departemen='".$departemen."'";
		}else{
			$where.=" and departemen=''";
		}
	}

	if($kodegol!=''){
		##CEK PER GOlLONGAN
		$str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$kdunit."' and jenispersetujuan='".$tipe."' and golongan='".$kodegol."'";
		$res=fetchdata($str);
		$pergolongan=$res[0]['kodeunit'];
		$where="";
		if($pergolongan>0){
			$where.=" and golongan='".$kodegol."'";
		}else{
			$where.=" and golongan=''";
		}
	}

	if($karyawaniduser!=''){
		##CEK PER Karyawan
		$str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$kdunit."' and jenispersetujuan='".$tipe."' and karyawaniduser='".$karyawaniduser."'";
		$res=fetchdata($str);
		$perkaryawaniduser=$res[0]['kodeunit'];
		$where="";
		if($perkaryawaniduser>0){
			$where.=" and karyawaniduser='".$karyawaniduser."'";
		}else{
			$where.=" and karyawaniduser=''";
		}
	}
	
	if($kdunit!=''){
		$where.=" and kodeunit='".$kdunit."'";
	}
	
	$hasil = 0;
	$str=$owlPDO->query("select distinct(level) as level from ".$dbname.".setup_approval where jenispersetujuan='".$tipe."' ".$where." order by level desc limit 1");	
	$str->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$str->fetch();
	
	if(isset($bar['level']))
		$hasil = $bar['level'];
	
	return $hasil;
}

function detailApprove($level,$notransaksi,$tipe,$karyawanpenyetuju='')
{
	global $dbname;
	global $owlPDO;
	
	$arrDetail = array();

	$where="";
	if($karyawanpenyetuju!='')
	{
		$where = " and a.karyawanid='".$karyawanpenyetuju."'";
	}
	
	$str=$owlPDO->query("select a.karyawanid,a.status,a.komentar,a.keterangan,a.tanggal,a.level,b.namakaryawan, c.namaorganisasi, c.kodeorganisasi, d.namajabatan from ".$dbname.".approval a 
		left join ".$dbname.".datakaryawan b on a.karyawanid=b.karyawanid 
		left join ".$dbname.".organisasi c on b.lokasitugas=c.kodeorganisasi 
		left join ".$dbname.".sdm_5jabatan d on b.kodejabatan=d.kodejabatan
		where notransaksi='".$notransaksi."' and jenispersetujuan='".$tipe."' and level='".$level."' ".$where);
	$str->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$str->fetch();
	
	$optNmKar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['karyawanid']."'");
	$arrStatus = array('0'=>'Waiting','1'=>'Disetujui','2'=>'Dikoreksi','3'=>'Ditolak');
	
	$arrDetail['nama'] = $bar['namakaryawan'];
	$arrDetail['karyawanid'] = $bar['karyawanid'];
	$arrDetail['status'] = $bar['status'];
	$arrDetail['namastatus'] = $arrStatus[$bar['status']];
	$arrDetail['komentar'] = $bar['komentar'];
	$arrDetail['keterangan'] = $bar['keterangan'];
	$arrDetail['tanggal'] = $bar['tanggal'];
	$arrDetail['level'] = $bar['level'];
	$arrDetail['namajabatan'] = $bar['namajabatan'];
	$arrDetail['namalokasitugas'] = $bar['namaorganisasi'];
	$arrDetail['idlokasitugas'] = $bar['kodeorganisasi'];
	
	return $arrDetail;
}


function listApprove($level,$tipe,$kdunit='',$departemen=''){
	global $dbname;
	global $owlPDO;
	
	$arrList = array();
	
	$where="";
	if($departemen!=''){
		##CEK PER DEPARTEMEN
		$str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval where kodeunit='".$kdunit."' and jenispersetujuan='".$tipe."' and departemen='".$departemen."'";
		$res=fetchdata($str);
		$perdepartemen=$res[0]['kodeunit'];
		if($perdepartemen>0){
			$where.=" and departemen='".$departemen."'";
		}else{
			$where.=" and departemen=''";
		}
	}else{
		$where.=" and departemen=''";		
	}
		
	
	if($kdunit!=''){
		$where.=" and kodeunit='".$kdunit."'";
	}
	
	// $str=$owlPDO->query("select * from ".$dbname.".setup_approval where jenispersetujuan='".$tipe."' and level='".$level."' ".$where." and karyawanid!='".$_SESSION['standard']['userid']."'");
	// untuk mengantisipasi orang yang sama menjadi pjs atas posisi tertentu sehingga muncul di 2 level
	$str=$owlPDO->query("select * from ".$dbname.".setup_approval where jenispersetujuan='".$tipe."' and level='".$level."' ".$where." ");
	$str->setFetchMode(PDO::FETCH_ASSOC);
	$no=0;
	while($bar=$str->fetch()){
		if($bar['tipe']=='1'){
			if($bar['departemen']!=''){
				$opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$bar['departemen']."'");
				$arrList[$no]['nama'] = $opttipe[$bar['departemen']];
				$arrList[$no]['karyawanid'] = $opttipe[$bar['departemen']];
			}
			
			if($bar['tipekaryawan']!=''){
				$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$bar['tipekaryawan']."'");
				$arrList[$no]['nama'] = $opttipe[$bar['tipekaryawan']];
				$arrList[$no]['karyawanid'] = $opttipe[$bar['tipekaryawan']];
			}
			if($bar['jabatan']!='0'){
				$opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$bar['jabatan']."'");
				$arrList[$no]['nama'] = $opttipe[$bar['jabatan']];
				$arrList[$no]['karyawanid'] = $opttipe[$bar['jabatan']];
			}
			$arrList[$no]['lokasitugas'] = $kdunit;
		}else{
			$optNmKar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['karyawanid']."'");
			$optLokTugas = makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$bar['karyawanid']."'");
			$arrList[$no]['nama'] = $optNmKar[$bar['karyawanid']];
			$arrList[$no]['karyawanid'] = $bar['karyawanid'];
			$arrList[$no]['lokasitugas'] = $optLokTugas[$bar['karyawanid']];
		}
		$no++;
	}
	
	return $arrList;
}
function listNotif($level,$tipe,$kdunit='',$departemen=''){
	global $dbname;
	global $owlPDO;
	
	$arrList = array();
	
	$where="";
	if($departemen!=''){
		##CEK PER DEPARTEMEN
		$str="select count(kodeunit) as kodeunit from ".$dbname.".setup_approval_notif where kodeunit='".$kdunit."' and jenispersetujuan='".$tipe."' and departemen='".$departemen."'";
		$res=fetchdata($str);
		$perdepartemen=$res[0]['kodeunit'];
		if($perdepartemen>0){
			$where.=" and departemen='".$departemen."'";
		}else{
			$where.=" and departemen=''";
		}
	}else{
		$where.=" and departemen=''";		
	}
	
	if($kdunit!=''){
		$where.=" and kodeunit='".$kdunit."'";
	}
	
	// untuk mengantisipasi orang yang sama menjadi pjs atas posisi tertentu sehingga muncul di 2 level
	$str = "select * from ".$dbname.".setup_approval_notif where jenispersetujuan='".$tipe."' and level='".$level."' ".$where." and notifonly='1'";
	$res = fetchdata($str);
	$no=0;
	foreach($res as $bar){
		if($bar['tipe']=='1'){
			if($bar['departemen']!=''){
				$opttipe = makeOption($dbname,'sdm_5departemen','kode,nama',"kode='".$bar['departemen']."'");
				$arrList[$no]['nama'] = $opttipe[$bar['departemen']];
				$arrList[$no]['karyawanid'] = $opttipe[$bar['departemen']];
			}
			
			if($bar['tipekaryawan']!=''){
				$opttipe = makeOption($dbname,'sdm_5tipekaryawan','id,tipe',"id='".$bar['tipekaryawan']."'");
				$arrList[$no]['nama'] = $opttipe[$bar['tipekaryawan']];
				$arrList[$no]['karyawanid'] = $opttipe[$bar['tipekaryawan']];
			}
			if($bar['jabatan']!='0'){
				$opttipe = makeOption($dbname,'sdm_5jabatan','kodejabatan,namajabatan',"kodejabatan='".$bar['jabatan']."'");
				$arrList[$no]['nama'] = $opttipe[$bar['jabatan']];
				$arrList[$no]['karyawanid'] = $opttipe[$bar['jabatan']];
			}
			$arrList[$no]['lokasitugas'] = $kdunit;
		}else{
			$optNmKar = makeOption($dbname,'datakaryawan','karyawanid,namakaryawan',"karyawanid='".$bar['karyawanid']."'");
			$optLokTugas = makeOption($dbname,'datakaryawan','karyawanid,lokasitugas',"karyawanid='".$bar['karyawanid']."'");
			$arrList[$no]['nama'] = $optNmKar[$bar['karyawanid']];
			$arrList[$no]['karyawanid'] = $bar['karyawanid'];
			$arrList[$no]['lokasitugas'] = $optLokTugas[$bar['karyawanid']];
		}
		$no++;
	}
	
	return $arrList;
}

function generateCustCode($nama,$countno=0)
{
	$custKode = "";
	$nama = strtoupper($nama);
	
	$tempname = preg_split("/[\s,_-]+/", $nama);
	
	$tempname=str_replace("PT","",$tempname);
	$tempname=str_replace("PT ","",$nama);
	$tempname=str_replace("PT.","",$tempname);
	$tempname=str_replace("PT. ","",$tempname);
	$tempname=str_replace("CV","",$tempname);
	$tempname=str_replace("CV ","",$tempname);
	$tempname=str_replace("CV.","",$tempname);
	$tempname=str_replace("CV. ","",$tempname);
	
	$words=explode(" ",$tempname);
	
	if(count($words) == 1)
	{
		$tempName = $words[0];
		$custKode = substr($words[0],0,1)."".substr($tempName,($countno+1),2);
		$custKode = checkCodeCust($nama,$custKode,$countno);
	}
	elseif(count($words) == 2)
	{
		$tempName = $words[1];
		$custKode = substr($words[0],0,1)."".substr($words[1],0,1)."".substr($tempName,($countno+1),1);
		$custKode = checkCodeCust($nama,$custKode,$countno);
	}
	else
	{
		$custKode = substr($words[0],0,1)."".substr($words[1],0,1)."".substr($words[2],$countno,1);
		$custKode = checkCodeCust($nama,$custKode,$countno);
	}
	
	if(strlen($custKode) < 3)
	{
		$my_rand_strng = substr(str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZ"), -3);
		$custKode = $custKode."".$my_rand_strng;
		$custKode = substr($custKode,0,3);
		$custKode = checkCodeCust($nama,$custKode,$countno);
	}
	
	return $custKode;
}

function checkCodeCust($nama,$code,$countno)
{
	global $dbname;
	global $owlPDO;
	
	$hasil = 0;
	$str=$owlPDO->query("select kodecustomer from ".$dbname.".pmn_4customer where kodecustomer='".$code."' limit 1");
	$str->setFetchMode(PDO::FETCH_ASSOC);
	$bar=$str->fetch();
	if($bar['kodecustomer']=='')
	{
		$hasil = $code;
	}
	else
	{
		$hasil = generateCustCode($nama,($countno+1));
	}
	return $hasil;
}

function hidezerodecimal($val,$no=0){
	if($no==0){
		$hasil = @number_format(@$val);
	}else{
		if($val==''){
			$hasil=rtrim(rtrim(@number_format(0, $no), '0'), '.');
		}else{
			$hasil = rtrim(rtrim(@number_format(@$val, $no), '0'), '.');			
		}
	}
	
	return $hasil;
}


#= merubah data '2017-08-11' menjadi 11 Agustus 2017 

function tglnmblnhr ($tgl,$la,$for){
	if($for==''){
		$for='short';
	}else{
		$for='long';
	}
	
	if($la==''){
		$la='E';
	}else{
		$la='I';
	}
	
	$expltgl=explode('-',$tgl);
	$nmbln=numToMonth($expltgl[1],$lang=$la,$format=$for);
	
	
		$bln=substr($tgl,5,2);
		$thn=substr($tgl,0,4);
		$tgl=substr($tgl,8,2);
		$ha=date("w", mktime(0, 0, 0, $bln,$tgl,$thn));
		$x=array ("Minggu","Senin","Selasa","Rabu","Kamis","Jumat","Sabtu");
		$y=array ("Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday");
		if($la=='I'){
		   $hari=$x[$ha];
		} else {
		   $hari=$y[$ha];   
		}
	
	if($nmbln != 'undefined'){
		$tglbaru=$hari.', '.$expltgl[2].' '.$nmbln.' '.$expltgl[0];
	}else{
		$tglbaru="";
	}
	
	
	return $tglbaru;
	
}


function  tglnmbln ($tgl,$la,$for){
	if($for==''){
		$for='short';
	}else{
		$for='long';
	}
	
	if($la==''){
		$la='E';
	}else{
		$la='I';
	}
	
	$expltgl=explode('-',$tgl);
	$nmbln=numToMonth($expltgl[1],$lang=$la,$format=$for);
	if($nmbln != 'undefined'){
		$tglbaru=$expltgl[2].' '.$nmbln.' '.$expltgl[0];
	}else{
		$tglbaru="";
	}
	return $tglbaru;
}




#= merubah data '2017-08-11' menjadi 11 Agustus 2017 

function tglnmblnsec($tgl,$la,$for){
	if($for==''){
		$for='short';
	}else{
		$for='long';
	}
	
	if($la==''){
		$la='E';
	}else{
		$la='I';
	}
	
	$exptgl = explode(' ',$tgl);
	$expltgl=explode('-',$exptgl[0]);
	$nmbln=numToMonth($expltgl[1],$lang=$la,$format=$for);
	if($nmbln != 'undefined'){
		if($exptgl[1]==''){
			$tglbaru=$expltgl[2].'-'.$nmbln.'-'.$expltgl[0];
		}else{
			$tglbaru=$expltgl[2].'-'.$nmbln.'-'.$expltgl[0].' '.$exptgl[1];
		}
	}else{
		$tglbaru="";
	}
	return $tglbaru;
}


function datediff($tgl1, $tgl2){
	$tgl1 = strtotime($tgl1);
	$tgl2 = strtotime($tgl2);
	$diff_secs = abs($tgl1 - $tgl2);
	$base_year = min(date("Y", $tgl1), date("Y", $tgl2));
	$diff = mktime(0, 0, $diff_secs, 1, 1, $base_year);
	return array( 	"years" => date("Y", $diff) - $base_year, 
					"months_total" => (date("Y", $diff) - $base_year) * 12 + date("n", $diff) - 1, 
					"months" => date("n", $diff) - 1, 
					"days_total" => floor($diff_secs / (3600 * 24)), 
					"days" => date("j", $diff) - 1, 
					"hours_total" => floor($diff_secs / 3600), 
					"hours" => date("G", $diff), 
					"minutes_total" => floor($diff_secs / 60), 
					"minutes" => (int) date("i", $diff), 
					"seconds_total" => $diff_secs, 
					"seconds" => (int) date("s", $diff) 
				);
}

## SET ICON FILE ##
function seticonfile($tipe)
{
	if($tipe=='.pdf'){$images = 'images/uploader/pdf.png';}
	else if($tipe=='.jpeg'||$tipe=='.jpg'){$images = 'images/uploader/jpg.png';}
	else if($tipe=='.gif'){$images = 'images/uploader/gif.png';}
	else if($tipe=='.png'){$images = 'images/uploader/png.png';}
	else if($tipe=='.xls'||$tipe=='.xlsx'){$images = 'images/uploader/excel.png';}
	else if($tipe=='.doc'||$tipe=='.docx'){$images = 'images/uploader/word.png';}
	else{$images = 'images/uploader/onebit_37.png';}
	
	return $images;
}

function potongtext($text,$num_char,$spasi=0){
	$char = $text{$num_char - 1};
	if($spasi!=0){
		while($char != ' ') {
			$char = $text{--$num_char};
		}		
	}
	return substr($text, 0, $num_char);
}

function tanggalbulan($tanggal){
    //get tgl persetujuan
    $tgl=explode('-', $tanggal);
    $tahun=$tgl[0];
    $bulan=$tgl[1];
    $tglex=$tgl[2];

    switch ($bulan) {
        case '01' :$bulan='Januari';break;
        case '02' :$bulan='Februari';break;
        case '03' :$bulan='Maret';break;
        case '04' :$bulan='April';break;
        case '05' :$bulan='Mei';break;
        case '06' :$bulan='Juni';break;
        case '07' :$bulan='Juli';break;
        case '08' :$bulan='Agustus';break;
        case '09' :$bulan='September';break;
        case '10' :$bulan='Oktober';break;
        case '11' :$bulan='November';break;
        case '12' :$bulan='Desember';break;
        default:
        break;
    }
    return $tgl=$tglex." ".$bulan." ".$tahun;
}

# Fungsi ambil aruskas, keterangan
function getArusKasket($noakun,$bulan,$tahun){
	global $dbname;
	global $owlPDO;

	$str1="select noaruskas from ".$dbname.".keu_5aruskas_detail where noakun='".$noakun."'";
    $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
    $res1->setFetchMode(PDO::FETCH_ASSOC);
    $bar1=$res1->fetch();
    $noaruskasdt=$bar1['noaruskas'];

    $str1="select keterangan,id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$noaruskasdt."'";
    $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
    $res1->setFetchMode(PDO::FETCH_ASSOC);
    $bar1=$res1->fetch();
    $keterangan2temp=$bar1['id_ket'];
    $keterangan2=trim($bar1['keterangan'].' '.numToMonth($bulan,'I','long').' '.$tahun);

	return $noaruskasdt."##".$keterangan2temp."##".$keterangan2;
}

# Fungsi ambil keterangan
function getSetupKeterangan($idArus,$bulan,$tahun){
    global $dbname;
    global $conn;
    global $owlPDO;

    $str1="select keterangan,id_ket from ".$dbname.".keu_5keterangan where noaruskas='".$idArus."'";
    $res1=$owlPDO->query($str1)or die(print " Gagal: ".PDOException::getMessage());
    $res1->setFetchMode(PDO::FETCH_ASSOC);
    $bar1=$res1->fetch();
    $keterangan2=trim($bar1['keterangan'].' '.numToMonth($bulan,'I','long').' '.$tahun);
    $keterangan2temp=$bar1['id_ket'];

    return $keterangan2."##".$keterangan2temp;
}

#= fungsi selisih bulan
function selisihbulan($per1,$per2){
	global $dbname;
	global $owlPDO;
	$per1=$per1.'-01';
	$per2=$per2.'-01';
	$jumlah=datediff($per1,$per2);
	return $jumlah['months_total'];
}

function replaceEnter($a, $x="####"){
}

#== Get Id Supplier berdasarkan kodetimbangan
function getSupIdWb($id){
	$hasil = "";
	$str="select a.supplierid from ".$dbname.".log_5suptimbangan a left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid where a.kodetimbangan='".$id."'";
	$res=fetchdata($str);
	$hasil = $res[0]['supplierid'];
	
	return $hasil;
}

#== Get Nama Supplier berdasarkan kodetimbangan
function getSupNameWb($id){
	global $dbname;
	global $owlPDO;
	
	$hasil = array();
	$str="select b.namasupplier from ".$dbname.".log_5suptimbangan a left join ".$dbname.".log_5supplier b on a.supplierid=b.supplierid where a.kodetimbangan='".$id."'";
	$res=fetchdata($str);
	$hasil = $res[0]['namasupplier'];
	
	return $hasil;
}

## Get Tipe ID Jenis E-Fill From Kas Bank
function gettipeefill($notransaksi){
	global $dbname;
	global $owlPDO;
	
	$arrkelompok = array();
	
	$expnotran = explode('/',$notransaksi);
	$temptipekb = $expnotran[2];
	$tipekb = substr($temptipekb,0,1);
	
	## Get Header Kas Bank
	$str="select * from ".$dbname.".keu_kasbankht where notransaksi='".$notransaksi."'";
	$res=fetchdata($str);
	$arrkelompok['periode'] = substr($res[0]['tanggalinput'],0,7);
	$arrkelompok['tipetransaksi'] = $res[0]['tipetransaksi'];
	$arrkelompok['tipekb'] = ($tipekb=='K' ? 'Kas Besar' : 'Bank');
	
	## Get Detail Kas Bank
	$str="select * from ".$dbname.".keu_kasbankdt where notransaksi='".$notransaksi."'";
	$res=fetchdata($str);
	$keterangan = $res[0]['keterangan1'];
	$noakun = $res[0]['noakun'];
	$arrkelompok['keterangan'] = $keterangan;
	$arrkelompok['unit'] = $res[0]['kodeorg'];
	$arrkelompok['noakun'] = $noakun;
	$arrkelompok['tahun'] = $res[0]['tahun'];
	$arrkelompok['bulan'] = $res[0]['bulan'];
	
	$fdname = '';
	## Get Tipe E-Fill Penagihan
	$str="select * from ".$dbname.".keu_penagihanht where noinvoice='".$keterangan."' and noinvoice!=''";
	$res=fetchdata($str);
	$jenis = $res[0]['jenis'];
	if($jenis=='UM'){
		$tipe='others';
		$fdname = 'Penjualan Dengan Downpayment';
	}else if($jenis=='PM' || $jenis=='PK'){
		$tipe='others';
		$fdname = 'Penagihan';
	}else if($jenis==''){
		## Get Tipe E-Fill Tagihan
		$str="select * from ".$dbname.".keu_tagihanht where noinvoice='".$keterangan."' and noinvoice!=''";
		$res=fetchdata($str);
		$tipe = $res[0]['tipeinvoice'];
		if($tipe==''){
			## BANSOS
			$str="select * from ".$dbname.".lgl_bansos where notransaksi='".$keterangan."' and notransaksi!=''";
			$res=fetchdata($str);
			if(count($res) > 0){
				$tipe='others';
				if($res[0]['jenis']=='BANSOS'){
					$fdname = 'BANSOS';				
				}
				if($res[0]['jenis']=='PP'){
					$fdname = 'Pengajuan Pembayaran';				
				}
			}
			
			## GRL
			$str="select * from ".$dbname.".lgl_pembebasanlahan where nosppt='".$keterangan."' and nosppt!=''";
			$res=fetchdata($str);
			if(count($res) > 0){
				$tipe='others';
				$fdname = 'GRL';
			}
			
			## Payroll Payment
			if($noakun=='2160101'){
				$tipe='others';
				$fdname = 'Payroll Payment';
			}
		}
	}
		
	if($fdname!=''){
		$wherefdname = " and foldername='".$fdname."'";
	}
	
	## Get Tipe ID Jenis E-Fill
	$str="select * from ".$dbname.".fil_5mapht where tipe='".$tipe."' ".$wherefdname."";
	$res=fetchdata($str);
	$idtipe = $res[0]['id'];
	$arrkelompok['foldername'] = $res[0]['foldername'];
	$arrkelompok['idtipe'] = $idtipe;
	$arrkelompok['tipe'] = $tipe;
	
	## Get No PO
	$arrpo = array();
	$str="select nopo from ".$dbname.".keu_tagihanht where noinvoice='".$keterangan."'";
	$res=fetchdata($str);
	foreach($res as $key=>$val){
		$arrpo[$val['nopo']] = $val['nopo'];
	}
	$arrjoinpo = join("','",$arrpo);
	$arrkelompok['arrjoinpo'] = $arrjoinpo;
	
	$str="select * from ".$dbname.".fil_5mapdt where idht='".$idtipe."'";
	$res=fetchdata($str);
	foreach($res as $key=>$val){
		if($val['kriteriadet'] != ''){
			$arrkelompok[$val['kriteriadet']] = $val['kriteriadet'];
		}else{			
			if(!empty($arrpo)){				
				$strx="select * from ".$dbname.".log_podt where nopo in ('".$arrjoinpo."')";
				$resx=fetchdata($strx);
				foreach($resx as $keyx=>$valx){
					$kelompokbarang = substr($valx['kodebarang'],0,3);
					
					if($kelompokbarang == $val['kelompok']){
						$stry="select * from ".$dbname.".fil_5mapdt2 where idht='".$val['id']."' and status='1'";
						$resy=fetchdata($stry);
						foreach($resy as $keyy=>$valy){
							$arrkelompok[$valy['kriteriadet']] = $valy['kriteriadet'];
						}
					}
				}
			}
			
		}
	}
	
	return $arrkelompok;
}

## Get Tipe ID Jenis E-Fill from AP
function gettghefill($notransaksi){
	global $dbname;
	global $owlPDO;
	
	$arrkelompok = array();
	
	## Get Tipe E-Fill
	$str="select * from ".$dbname.".keu_tagihanht where noinvoice='".$notransaksi."'";
	$res=fetchdata($str);
	$tipe = $res[0]['tipeinvoice'];
	$nopo = $res[0]['nopo'];
	
	## Get Tipe ID Jenis E-Fill
	$str="select * from ".$dbname.".fil_5mapht where tipe='".$tipe."'";
	$res=fetchdata($str);
	$idtipe = $res[0]['id'];
	
	$str="select * from ".$dbname.".fil_5mapdt where idht='".$idtipe."' and status='1' and modul!='KB'";
	$res=fetchdata($str);
	foreach($res as $key=>$val){
		if($val['kriteriadet'] != ''){
			$arrkelompok[$val['kriteriadet']] = $val['kriteriadet'];
		}else{			
			if($nopo != ""){				
				$strx="select * from ".$dbname.".log_podt where nopo='".$nopo."'";
				$resx=fetchdata($strx);
				foreach($resx as $keyx=>$valx){
					$kelompokbarang = substr($valx['kodebarang'],0,3);
					
					if($kelompokbarang == $val['kelompok']){
						$stry="select * from ".$dbname.".fil_5mapdt2 where idht='".$val['id']."' and status='1'  and modul!='KB'";
						$resy=fetchdata($stry);
						foreach($resy as $keyy=>$valy){
							$arrkelompok[$valy['kriteriadet']] = $valy['kriteriadet'];
						}
					}
				}
			}
			
		}
	}
	
	return $arrkelompok;
}

function getpghefill($notransaksi){
	global $dbname;
	global $owlPDO;
	
	$arrkelompok = array();
	
	## Get Tipe E-Fill
	$str="select * from ".$dbname.".keu_penagihanht where noinvoice='".$notransaksi."'";
	$res=fetchdata($str);
	$jenis = $res[0]['jenis'];
	if($jenis=='UM'){
		$idtipe='17';
	}else{
		$idtipe='16';
	}
	$arrkelompok['nokontrak']=$res[0]['nokontrak'];
	
	$str="select * from ".$dbname.".fil_5mapdt where idht='".$idtipe."' and status='1' and modul!='KB'";
	$res=fetchdata($str);
	foreach($res as $key=>$val){
		if($val['kriteriadet'] != ''){
			$arrkelompok[$val['kriteriadet']] = $val['kriteriadet'];
		}
	}
	
	return $arrkelompok;
}

## Get Location File E-fill
function setlocationfile($id){
	global $dbname;
	global $owlPDO;
	
	$path = "fileupload/filingsystem/";
	$val = "";
	$tempval = "";
	$curid = $id;
	
	$level = makeOption($dbname,'filemanager','id,level',"id='".$id."'");
	for($i=0;$i<=$level[$id];$i++)
	{
		$str="select * from ".$dbname.".filemanager where id='".$curid."' and status='1'";
		$res=fetchData($str);
		if($i==0)
		{
			$val=str_replace('/','',$res[0]['namafile']);
		}
		else
		{
			$val=str_replace('/','',$res[0]['namafile'])."/".$tempval;
		}
		$tempval=$val;
		$curid = $res[0]['induk'];
	}
	
	return $path."".$val;
}

## Get Kriteria from modul E-Fill
function getmodulefil($emodul){
	global $dbname;
	global $owlPDO;
	
	$optemodul = array();
	
	$str="select * from ".$dbname.".fil_5mapcriteria where modul='".$emodul."' and status='1'";
	$res=fetchdata($str);
	foreach($res as $key=>$val){
		$optemodul[$val['id']]['id'] = $val['id'];
		$optemodul[$val['id']]['kriteria'] = $val['kriteria'];
	}
	$optemodul['others']['id'] = 'others';
	$optemodul['others']['kriteria'] = 'Others';
	
	return $optemodul;
}

## Get Kriteria Name E-Fill
function getcriterianame($kriteria){
	global $dbname;
	global $owlPDO;
	
	$value="";
	if($kriteria=='others'){
		$value="Others";
	}else{
		$str="select * from ".$dbname.".fil_5mapcriteria where id='".$kriteria."'";
		$res=fetchdata($str);
		$value=$res[0]['kriteria'];
	}
	
	return $value;
}

## GET FOLDER BANK E-Fil
function getbankefil($notransaksi){
	global $dbname;
	global $owlPDO;
	
	$accbank="";
	$str="select a.rekening, c.namabank from ".$dbname.".keu_kasbankht a 
		left join ".$dbname.".keu_5akunbank b on a.rekening=b.noakun 
		left join ".$dbname.".keu_5daftarbank c on b.namabank=c.kodebank 
		where a.notransaksi='".$notransaksi."'";
	$res=fetchdata($str);
	$accbank=$res[0]['namabank']." ".$res[0]['rekening'];
	
	return $accbank;
}

function selisihjam($jammulai,$menitmulai,$jamselesai,$menitselesai){
	/*
		$jam_start = "08";
		$menit_start = "05";
		$jam_end = "16";
		$menit_end = "00";

		$hasil = (intVal($jam_end) - intVal($jam_start)) * 60 + (intVal($menit_end) - intVal($menit_start));
		$hasil = $hasil / 60;
		$hasil = number_format($hasil,2);
	*/
	
	$jammulai=$jammulai;
		$menitmulai=$menitmulai;
	$jamselesai=$jamselesai;
		$menitselesai=$menitselesai;
	
	$hasil = (intVal($jamselesai) - intVal($jammulai)) * 60 + (intVal($menitselesai) - intVal($menitmulai));
	$hasil = $hasil / 60;
	$hasil = number_format($hasil,2);
	
	return $hasil;
}

function waktunormal($waktu){//from format YYYYmmddhhMMss
	$_retval=substr($waktu,8,2)."-".substr($waktu,5,2)."-".substr($waktu,0,4)." ".substr($waktu,11,2).":".substr($waktu,14,2).":".substr($waktu,17,2);
	
	return($_retval);
}

## GET VALIDASI ANGGARAN
function validasibudget($modul,$unit){
	global $dbname;
	global $owlPDO;
	
	$arr = array();
	$str="select * from ".$dbname.".setup_validasianggaran where modul='".$modul."' and unit='".$unit."' and status='1'";
	$res=fetchdata($str);
	if(count($res) > 0){
		$arr['status'] = '1';
		$arr['toleransi'] = ($res[0]['toleransi']=='0'?'1':$res[0]['toleransi']);
	}else{
		$arr['status'] = '0';
		$arr['toleransi'] = '1';
	}
	
	return $arr;
}

## CREATE PAGING
function createpaging($jlhbrs,$limit,$page,$colspan,$loaddata,$getpage){
	global $dbname;
	global $owlPDO;
	
	$tab="";
	$totrows=ceil($jlhbrs/$limit);
	if($totrows==0){
		$totrows=1;
	}
	
	$isiRow='';
	for($er=1;$er<=$totrows;$er++){
		$sel = ($page==$er-1)? 'selected': '';
		$isiRow.="<option value='".$er."' ".$sel.">".$er."</option>";
	}
	
	$frompage = @(($page*$limit)+1);
	if(@(($page+1)*$limit) > $jlhbrs){
		$topage = $jlhbrs;
	}else{
		$topage = @(($page+1)*$limit);
	}
	$tab.="<tr>
		<td colspan=".$colspan." align=center>
			<div>".$frompage." to ".$topage." Of ".  $jlhbrs."</div><div>";
			$tab.="<div style='display:inline-block;vertical-align:middle;'>";
			if($page=='0'){
				$tab.="";
			}else{
				$tab.="<button class=mybutton onclick=$loaddata(".@($page-1).");>".$_SESSION['lang']['pref']."</button>";
			}
			$tab.="</div>";
			
			$tab.="<div style='display:inline-block;vertical-align:middle;'>";
			$tab.="<table style='min-width:10px'><tr><td><select id=\"pages\" name=\"pages\" style=\"min-width:20px\" onchange=\"$getpage()\">".$isiRow."</select></td></tr></table>";
			$tab.="</div>";
			
			$tab.="<div style='display:inline-block;vertical-align:middle;'>";
			if(@($page+1) == $totrows){
				$tab.="";
			}else{
				$tab.="<button class=mybutton onclick=$loaddata(".@($page+1).");>".$_SESSION['lang']['lanjut']."</button>";
			}
			$tab.="</div>";
		$tab.="</div></td>
	</tr>";
	
	return $tab;
}

function headerreport($kdorg=''){
	global $dbname;
	global $owlPDO;
	
	$tab="";
	
	if($kdorg!=''){
		$str="select tipe,induk from ".$dbname.".organisasi where kodeorganisasi='".$kdorg."'";
		$res=fetchdata($str);
		$tipe=$res[0]['tipe'];
		$induk=$res[0]['induk'];
		
		if($tipe=='PT'){
			$kdpt=$kdorg;
		}else{
			$kdpt=$induk;
		}
		
		$str="select kodeorganisasi,alamat,telepon,namaorganisasi,wilayahkota,tipe from ".$dbname.".organisasi where kodeorganisasi='".$kdpt."'";
		$res=fetchdata($str);
		$namaorganisasi = $res[0]['namaorganisasi'];
		$alamat = $res[0]['alamat'];
		$telepon = $res[0]['telepon'];
		$wilayahkota = ($res[0]['wilayahkota']==''?'':', '.$res[0]['wilayahkota']);
		$tipe = $res[0]['tipe'];
		$imgsrc= "images/ksp.jpg";
		
		if($tipe=='PT'){
			$tab="<table cellspacing=0,5 cellpading=0 width=100% style=\"font-family:'Arial Narrow',Arial,sans-serif;\">
				<tr>
					<td style='width:100px;'>
						<img src='".$imgsrc."' style='width:100px'>
					</td>
					<td>
						<b><font size=3>".$namaorganisasi."</font></b><br>
						<font size=2>".$alamat."".$wilayahkota."</font><br>
						<font size=2>Tel: ".$telepon."</font>
					</td>
				</tr>
			</table>
			<hr>";
		}
	}
	
	$tab.="<table width=100%>
		<tr>
			<td style='text-align:right;font-size:7'>PRINT TIME : ".date('d-m-Y H:i:s')."</td>
		</tr>
	</table>";
	
	return $tab;
}
function nan2zero($e,$i=0){
	if(is_nan($e)){
		$e=0;
	}else{
		$e=$e;
	}
	return number_format($e,$i);
}
function createnotif($notrk,$tipe,$msgdt,$createby,$tanggal){
	global $dbname;
	global $owlPDO;
	
	$stry="insert into ".$dbname.".list_notification (kodetransaksi,kodenotification,detail,karyawanid,readnotif,shownotif,tanggal) values ('".$notrk."','".$tipe."','".$msgdt."','".$createby."','0','0','".$tanggal."')";
	$owlPDO->exec($stry);
}

function picnotification($kodejenis){
	global $dbname;
	global $owlPDO;
	
	$arr=array();
	
	$strf="select * from ".$dbname.".setup_notification_dt where kodejenis='".$kodejenis."'";
	$resf=fetchdata($strf);
	foreach($resf as $valf){
		if($valf['tipe']=='1'){
			if($valf['kodedepartement']!=''){
				$strfx="select karyawanid from ".$dbname.".datakaryawan where bagian='".$valf['kodedepartement']."'";
				$resfx=fetchdata($strfx);
				foreach($resfx as $valfx){
					$arr[$valfx['karyawanid']]=$valfx['karyawanid'];
				}
			}
			if($valf['kodetipekaryawan']!=''){
				$strfx="select karyawanid from ".$dbname.".datakaryawan where tipekaryawan='".$valf['kodetipekaryawan']."'";
				$resfx=fetchdata($strfx);
				foreach($resfx as $valfx){
					$arr[$valfx['karyawanid']]=$valfx['karyawanid'];
				}
			}
			if($valf['kodejabatan']!=''){
				$strfx="select karyawanid from ".$dbname.".datakaryawan where kodejabatan='".$valf['kodejabatan']."'";
				$resfx=fetchdata($strfx);
				foreach($resfx as $valfx){
					$arr[$valfx['karyawanid']]=$valfx['karyawanid'];
				}
			}
		}else{
			$arr[$valf['karyawanid']]=$valf['karyawanid'];
		}
	}
	
	return $arr;
}

function tipepurchase($val){
	$hasil="";
	$arrtipepurchase=array('PR'=>'Purchase Request (INV)','SR'=>'Service Request (JASA)','CP'=>'Capex Request (ASET)','NR'=>'Non-Inventory Request (NON-INV)');
	$hasil=$arrtipepurchase[$val];
	return $hasil;
}

## BEGIN BI ##
function strToArray($data,$sparator){
	$res = explode($sparator,$data);
	if(count($res) > 1){
		$result = $res; // result data array
	}else{
		$result = $data; // result data str
	}
	return $result;
}

function forKebunAll($kbnarr,$colomn,$type){
	$where_unit = "";
	$kebunall = $kbnarr;
	if(count($kebunall)>1){
		if($type == 'in'){
			$kebun = "'".$kebunall[0]."'";//unit
			for($i=1; $i<count($kebunall); $i++){
				$kebun .= ",'".$kebunall[$i]."'";//unit
			}
			$where_unit = "And ".$colomn." in (".$kebun.")";//unit
		}elseif($type == 'like'){
			$kebun = "".$colomn." like '".$kebunall[0]."%'";//kodeorg
			for($i=1; $i<count($kebunall); $i++){
				$kebun .= " or ".$colomn." like '".$kebunall[$i]."%'";//kodeorg
			}
			$where_unit= "And (".$kebun.")";//kodeorg
		}
	}else{
		if($type == 'in'){
			$where_unit = "And ".$colomn." = '".$kebunall."'";//unit
		}elseif($type == 'like'){
			$where_unit  = "And ".$colomn." like '".$kebunall."%'";//kodeorg
		}
	}
	 $result = $where_unit;
	return  $result;
}


function nantodouble($value){
	$val = 0;
	if(is_nan($value)=='1'){
		$val = 0;
	}else{
		$val = $value;
	}
	
	return $val;
}
    


## END BI ##


function movetoappreturn($notransaksi){
	global $dbname;
	global $owlPDO;
	
	$str="select max(nourut) as nourut from ".$dbname.".approval_return where notransaksi='".$notransaksi."'";
	$res=fetchdata($str);
	$nourut=($res[0]['nourut']==''?0:$res[0]['nourut']);
	$nourut++;
	
	$str="select * from ".$dbname.".approval where notransaksi='".$notransaksi."'";
	$res=fetchdata($str);
	foreach($res as $val){
		$strx="insert into ".$dbname.".approval_return (notransaksi,jenispersetujuan,level,karyawanid,status,komentar,keterangan,tanggal,nourut) values ('".$val['notransaksi']."','".$val['jenispersetujuan']."','".$val['level']."','".$val['karyawanid']."','".$val['status']."','".$val['komentar']."','".$val['keterangan']."','".$val['tanggal']."','".$nourut."')";
		$owlPDO->exec($strx);
	}
	$str="delete from ".$dbname.".approval where notransaksi='".$notransaksi."'";
	$owlPDO->exec($str);
}

function gethistoryapp($notransaksi){
	global $dbname;
	global $owlPDO;
	
	$tabhist="";
	
	$arrsttapp=array("0"=>"Proses Persetujuan","1"=>"Disetujui","2"=>"Ditolak","3"=>"Reconfirm");
	
	##APPROVAL IN PROGRESS
	$str="select * from ".$dbname.".approval where notransaksi='".$notransaksi."' order by level asc";
	$res=fetchdata($str);
	$jlhdataapp=count($res);
	if($jlhdataapp>0){
		$tabhist.="<div style='width:auto;overflow:auto;'>
		<table border=0 cellspacing=1 cellpadding=3 class=sortable style=width:100%>";
		$no=0;
		$tempnourut=0;
		foreach($res as $val){
			$no++;
			if($no==1){
				if($no!=1){
					$tabhist.="<tbody>
						<tr class=rowcontent style='background-color:#42f5c2;'>
							<td colspan='5' style='text-align:center'>&nbsp;</td>
						</tr>
					</tbody>";
				}
				$tabhist.="<thead>
					<tr style='font-weight:bold'>
						<td colspan='5'>Approval In Progress</td>
					</tr>
				</thead>";
				
				$tabhist.="<thead>
				<tr style='font-weight:bold'>
					<td style='text-align:center'>".$_SESSION['lang']['level']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['status']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['pic']."</td>
					<td style='text-align:center;min-width:50px'>".$_SESSION['lang']['waktu']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['keterangan']."</td>
				</tr>
				</thead>";
			}
			
			$tabhist.="<tbody>
				<tr class=rowcontent>
					<td style='text-align:center'>".$val['level']."</td>
					<td style='text-align:center'>".$arrsttapp[$val['status']]."</td>
					<td style='text-align:left'>".getNamaKaryawan($val['karyawanid'])."</td>
					<td style='text-align:center'>".($val['tanggal']=='0000-00-00 00:00:00'?'':waktunormal($val['tanggal']))."</td>
					<td style='text-align:left'>".$val['komentar']."</td>
				</tr>
			</tbody>";
			
			$tempnourut=$val['nourut'];
		}
		$tabhist.="<tbody>
			<tr class=rowcontent style='background-color:#42f5c2;'>
				<td colspan='5' style='text-align:center'>&nbsp;</td>
			</tr>
		</tbody>";
		$tabhist.="</table>
		</div>";
	}
	
	##APPROVAL HISTORY
	$str="select * from ".$dbname.".approval_return where notransaksi='".$notransaksi."' order by nourut asc, level asc";
	$res=fetchdata($str);
	$jlhdata=count($res);
	if($jlhdata>0){
		$tabhist.="<div style='width:auto;overflow:auto;'>
		<table border=0 cellspacing=1 cellpadding=3 class=sortable style=width:100%>";
		$no=0;
		$tempnourut=0;
		foreach($res as $val){
			if($tempnourut!=$val['nourut']){
				$no++;
				if($no!=1){
					$tabhist.="<tbody>
						<tr class=rowcontent style='background-color:#42f5c2;'>
							<td colspan='5' style='text-align:center'>&nbsp;</td>
						</tr>
					</tbody>";
				}
				$tabhist.="<thead>
					<tr style='font-weight:bold'>
						<td colspan='5'>Return - ".$no."</td>
					</tr>
				</thead>";
				
				$tabhist.="<thead>
				<tr style='font-weight:bold'>
					<td style='text-align:center'>".$_SESSION['lang']['level']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['status']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['pic']."</td>
					<td style='text-align:center;min-width:50px'>".$_SESSION['lang']['waktu']."</td>
					<td style='text-align:center'>".$_SESSION['lang']['keterangan']."</td>
				</tr>
				</thead>";
			}
			
			$tabhist.="<tbody>
				<tr class=rowcontent>
					<td style='text-align:center'>".$val['level']."</td>
					<td style='text-align:center'>".$arrsttapp[$val['status']]."</td>
					<td style='text-align:left'>".getNamaKaryawan($val['karyawanid'])."</td>
					<td style='text-align:center'>".waktunormal($val['tanggal'])."</td>
					<td style='text-align:left'>".$val['komentar']."</td>
				</tr>
			</tbody>";
			
			$tempnourut=$val['nourut'];
		}
		$tabhist.="</table>
		</div>";
	}else{
		if($jlhdataapp <= 0){
			$tabhist.="<table border=0 cellspacing=1 cellpadding=3 class=sortable>
				<tbody>
				<tr class=rowcontent>
					<td style='min-width:200px;text-align:center;'><label style='color:red;'>".$_SESSION['lang']['datanotfound']."</label></td>
				</tr>
				</tbody>
			</table>";
		}
	}
	
	return $tabhist;
}

function statusapproval($id){
	$hasil="";
	if($id=='0'){
		$hasil = "Belum diajukan";
	}else if($id=='9'){
		$hasil = "Proses Persetujuan";
	}else if($id=='2'){
		$hasil = "Ditolak";
	}else if($id=='3'){
		$hasil = "Reconfirm";
	}else{
		$hasil = "Disetujui";
	}
	
	return $hasil;
}

function tipektrkjasa($tipe=''){
	global $dbname;
	global $owlPDO;
	
	if($tipe==''){
		$hasil['jasa']="Jasa";
		$hasil['material']="Material";
	}else{
		if($tipe=='jasa'){
			$hasil="Jasa";
		}else{
			$hasil="Material";
		}
	}
	
	return $hasil;
}

function exiterroradmin($str=''){
	global $dbname;

	$query="select * from ".$dbname.".admin_list where username='".$_SESSION['standard']['username']."'";
	$res=fetchdata($query);
	if ($res) {
		$hasil = exit('Error '.$str);
	}else{
		$hasil = "";
	}

	echo $hasil;
}

function validasiInput($kodeorg,$divisi='',$jenistransaksi,$tgltrans,$exit='1'){
	#exit
	# 0 = throw new PDOException("exit");
	# 1 = exit('Error');
	
	// echo $kodeorg."<br>";
	// echo $divisi."<br>";
	// echo $jenistransaksi."<br>";
	// echo $tgltrans."<br>";
	// exit("error");
	
	global $dbname;
	global $owlPDO;
	
	$error="";
	
	/* switch(strtolower($jenistransaksi)){
		case'bkm':
			$str = "select tanggal from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where b.kodeorg='".$kodeorg."' and a.kodeorg like '".$divisi."%' and b.tipetransaksi!='PNN' order by tanggal desc limit 1";
			$res = fetchdata($str);
			$lastinput = $res[0]['tanggal'];
		break;
		case'pnn':
			$str = "select tanggal from ".$dbname.".kebun_prestasi a left join ".$dbname.".kebun_aktifitas b on a.notransaksi=b.notransaksi where b.kodeorg='".$kodeorg."' and a.kodeorg like '".$divisi."%' and b.tipetransaksi='PNN' order by tanggal desc limit 1";
			$res = fetchdata($str);
			$lastinput = $res[0]['tanggal'];
		break;
		case'rpnn':
			$str = "select tanggal from ".$dbname.".kebun_rekappnn where divisi like '".$kodeorg."%' and divisi like '".$divisi."%' order by tanggal desc limit 1";
			$res = fetchdata($str);
			$lastinput = $res[0]['tanggal'];
		break;
		case'log':
			$str = "select tanggal from ".$dbname.".log_transaksi_vw where kodegudang like '".$kodeorg."%' order by tanggal desc limit 1";
			$res = fetchdata($str);
			$lastinput = $res[0]['tanggal'];
		break;
		case'kb':
			$str = "select tanggal from ".$dbname.".keu_kasbankht where kodeorg = '".$kodeorg."' order by tanggal desc limit 1";
			$res = fetchdata($str);
			$lastinput = $res[0]['tanggal'];
		break;
	} */
	
	$tglhi=date("Y-m-d");
	$wh="";
	if($divisi!=''){
		$wh=" and divisi='".$divisi."'";
	}
	
	$str  = "select * from " . $dbname . ".setup_validasiinput_ht where kodeorg='".$kodeorg."' and jenistransaksi='".strtolower($jenistransaksi)."' and  berlaku <='".$tgltrans."' ".$wh." order by berlaku desc limit 1";
	$res  = fetchdata($str);
	if(count($res)==0){
		$str = "select * from " . $dbname . ".setup_validasiinput_ht where kodeorg='".$kodeorg."' and jenistransaksi='".strtolower($jenistransaksi)."' and  berlaku <='".$tgltrans."' order by berlaku desc limit 1";
		$res = fetchdata($str);
	}
	
	$aktif= $res[0]['status'];
	$ada  = count($res);
	
	if($ada>0 and $aktif=='1'){
		# Cek parameter dt.
		$str = "select * from " . $dbname . ".setup_validasiinput_dt where kodeorg='".$kodeorg."' and jenistransaksi='".strtolower($jenistransaksi)."' and berlakudari<='".$tgltrans."' and berlakusampai>='".$tgltrans."' and status='1'";
		$res = fetchdata($str);
		$div = $res[0]['divisi'];
		if(count($res)>0){
			if($div!=''){
				$str = "select * from " . $dbname . ".setup_validasiinput_dt where kodeorg='".$kodeorg."' and jenistransaksi='".strtolower($jenistransaksi)."' and berlakudari<='".$tgltrans."' and berlakusampai>='".$tgltrans."' ".$wh."  and status='1'";
				$res = fetchdata($str);
			}else{
				$str = "select * from " . $dbname . ".setup_validasiinput_dt where kodeorg='".$kodeorg."' and jenistransaksi='".strtolower($jenistransaksi)."' and berlakudari<='".$tgltrans."' and berlakusampai>='".$tgltrans."' and status='1'";
				$res = fetchdata($str);
			}
		}else{
			#kalau tidak ada di dt maka ambil di ht sebagai default
			$str = "select * from " . $dbname . ".setup_validasiinput_ht where kodeorg='".$kodeorg."' and jenistransaksi='".strtolower($jenistransaksi)."' and  berlaku <='".$tgltrans."' and status='1' ".$wh." order by berlaku desc limit 1";
			$res = fetchdata($str);
			if(count($res)>0){
				$str = "select * from " . $dbname . ".setup_validasiinput_ht where kodeorg='".$kodeorg."' and jenistransaksi='".strtolower($jenistransaksi)."' and  berlaku <='".$tgltrans."' and status='1' ".$wh." order by berlaku desc limit 1";
				$res = fetchdata($str);
			}else{
				$str = "select * from " . $dbname . ".setup_validasiinput_ht where kodeorg='".$kodeorg."' and jenistransaksi='".strtolower($jenistransaksi)."' and  berlaku <='".$tgltrans."' and status='1' order by berlaku desc limit 1";
				$res = fetchdata($str);
			}
		}
		
		$where="";
		$tipe = makeOption($dbname,'organisasi','kodeorganisasi,tipe',"kodeorganisasi='".$kodeorg."'");
		if($tipe[$kodeorg]=='HOLDING'){
			$where=" and (kebun='GLOBAL' or kebun='HOLDING' or kebun='".$kodeorg."')";			
		}else{
			$where=" and (kebun='GLOBAL' or kebun='".$kodeorg."')";			
		}
		
		
		if(count($res)>0){
			foreach($res as $bar){
				$hmhb = 0;
				$h="Hari";$l="Termasuk Hari Libur";
				if($bar['harilibur']==0){
					$h="HKE"; $l="HKE";
					//$rangetgl = rangeTanggal($lastinput,$tgltrans);
					$rangetgl = rangeTanggal($tgltrans,$tglhi);
					foreach($rangetgl as $tgli){						
						$day = date('D', strtotime($tgli));
						$sql = "select * from ".$dbname.".sdm_5harilibur where tanggal='".$tgli."' ".$where.""; #exit("error".$sql);
						$req = fetchdata($sql);
						if(@$req[0]['keterangan']=='libur'){
							$hmhb+=1;
						} else if (($day=='Sun' and @$req[0]['keterangan']=='') or @$req[0]['keterangan']=='libur'){
							$hmhb+=1;
						}
					}
				}
				$h="H";
				if($divisi!='' and $bar['divisi']!=''){
					if($bar['divisi']==$divisi){
						//$hari = selisitgl($tgltrans,$lastinput);
						$hari = selisitgl($tglhi,$tgltrans);
						if(($hari-$hmhb)>$bar['jumlahhari']){
							$error="Transaksi yang anda input sudah melewati batas maksimal penginputan ".$h."+".$bar['jumlahhari']." (".$l."), silahkan hubungi Administrator untuk melanjutkan transaksi ini.\nTanggal Input : ".tanggalnormal($tglhi)."\nTanggal Transaksi : ".tanggalnormal($tgltrans)."";
						}
					}
				}else{
					//$hari = selisitgl($tgltrans,$lastinput);
					$hari = selisitgl($tglhi,$tgltrans);
					if(($hari-$hmhb)>$bar['jumlahhari']){
						$error="Transaksi yang anda input sudah melewati batas maksimal penginputan ".$h."+".$bar['jumlahhari']." (".$l."), silahkan hubungi Administrator untuk melanjutkan transaksi ini.\nTanggal Input : ".tanggalnormal($tglhi)."\nTanggal Transaksi : ".tanggalnormal($tgltrans)."";
					}
				}
			}
		}
		
		if($error!=''){		
			if($exit='1'){
				exit("Gagal : ".$error);
			}else{
				throw new PDOException($error);
			}
		}	
	}
}
?>