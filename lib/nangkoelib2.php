<?
$firstTipe = 'MAP001';
$firstPT = 'MAP009';
$textBlok = 'MAP024';

if (isset($_SESSION['theme']))
   $theme=$_SESSION['theme'];
else
   $theme='skyblue';

#theme mathcer
if($theme=='skyblue' || $theme==''){
  $men='menu.css';
  $gen='generic.css';
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

// Ini Common Variable
$i=$no=0;
$where=$whr=$tab=$stream="";

function OPEN_BODY($title='OWL-Plantation System')
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
	<meta name='description' content='Sistem Perkebunan Kelapa Sawit, Palm Oil Plantation System, Administrasi Perkebunan, Program Sawit, Lightweight over the network, Easy to use, owl-plantation system, owl-plantation' />
	<meta name='keywords' content='Aplikasi Perkebunan Sawit, Palm Plantation System, Palm Oil System, Administrasi perkebunan,Sistem Perkebunan Kelapa Sawit, Palm Oil, Owl, Owl-Plantation, Owl Plantation' />		
		<title>".$title."</title>";
echo" 
    <script language=JavaScript1.2 src=js/".$menuJs."></script>
	 <script language=JavaScript1.2 src=js/calendar.js></script>
    <script language=JavaScript1.2 src=js/drag.js></script>
    <script language=JavaScript1.2 src=js/generic.js></script>
    <script language=JavaScript1.2 src=js/nangkoelsort.js></script>
	<link rel=stylesheet type=text/css href=style/".$men.">
	<link rel=stylesheet type=text/css href=style/".$gen.">	
	<link rel=stylesheet type=text/css href=style/calendarblue.css>
	<link rel=stylesheet type=text/css href=style/zTable.css>
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
</noscript>
<div style='height:30px'><a href='http://owl-plantation.com' target='new'><img src='images/owl2.png' style='height:30px;cursor:pointer;'></a>
</div>
";
}


function CLOSE_BODY()
{
	require_once('master_footer.php');
	echo "</body></html>";
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
	if($_SESSION['theme']=='' || $_SESSION['theme']=='skyblue'){
	echo"</div></div></div>
        <div class=\"x-box-bl\"><div class=\"x-box-br\"><div class=\"x-box-bc\"></div></div></div>
        </div>";
    }else if($_SESSION['theme']=='red'){
		echo"</div></fieldset><br>";
	}else{
		echo"</div></fieldset><br>";
	}
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
<table border=0 cellspacing=0>
<tr class=tab>";
 for($x=0;$x<count($arrHeader);$x++)
 {
	if($x==0)
	  $stream.="<td id=tab".$tabId.$x." onclick=tabAction(this,".$x.",'".$tabId."',".(count($arrHeader)-1).",'".$_SESSION['theme']."'); onmouseover=chgBackgroundImg(this,'./images/".$drwImgSec."','#d0d0d0');  onmouseout=chgBackgroundImg(this,'./images/".$drwImgDef."','#333333');  style=\"background-image:url('./images/".$drwImgSec."');color:#FFFFFF;font-weight:bolder;border-right:#dedede solid 1px;width:".$tabLength."px;height:20px\">".$arrHeader[$x]."</td>";
	else
      $stream.="<td id=tab".$tabId.$x." style='border-right:#dedede solid 1px; height:20px; width:".$tabLength."px; background-image:url(\"./images/".$drwImgDef."\")' onclick=tabAction(this,".$x.",'".$tabId."',".(count($arrHeader)-1).",'".$_SESSION['theme']."'); onmouseover=chgBackgroundImg(this,'./images/".$drwImgSec."','#d0d0d0');   onmouseout=chgBackgroundImg(this,'./images/".$drwImgDef."','#333333'); >".$arrHeader[$x]."</td>";		
 }
$stream.="</tr></table>";
 for($x=0;$x<count($arrContent);$x++)
 {
	if($x==0)
       $stream.="<fieldset style='display:\"\";border-color:".$bgTabOuter."; border-style:solid;border-width:2px; border-top:".$bgTabOuter." solid 8px; background-color:".$bgTabInner.";margin-left:0px;width:".$contentLength."px;' id=content".$tabId.$x.">".$arrContent[$x]."</fieldset>";
	else
	   $stream.="<fieldset style='display:none;border-color:".$bgTabOuter."; border-style:solid;border-width:2px; border-top:".$bgTabOuter." solid 8px; background-color:".$bgTabInner.";margin-left:0px;width:".$contentLength."px;' id=content".$tabId.$x.">".$arrContent[$x]."</fieldset>";	
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
function tanggalnormald($_q)
{
//20090804 08:00:00
 $_q=str_replace("-","",$_q);
 $_retval=substr($_q,6,2)."-".substr($_q,4,2)."-".substr($_q,0,4)." ".substr($_q,9,5);
 return($_retval);
}
function tanggalsystem($_q)//from format dd-mm-YYYY
{
 $_retval=substr($_q,6,4).substr($_q,3,2).substr($_q,0,2);
 return($_retval);//return 8 chardate format eg.20090804
}

function tanggalsystemn($_q)//from format dd-mm-YYYY
{//0408
 $_retval=substr($_q,6,4)."-".substr($_q,3,2)."-".substr($_q,0,2).substr($_q,10,7);
 return($_retval);//return 8 chardate format eg.20090804
}


    

function tanggalsystemd($_q)//from format dd-mm-YYYY
{//0408
 $_retval=substr($_q,6,4)."-".substr($_q,3,2)."-".substr($_q,0,2).substr($_q,10,7).":00";
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
        $strAv=$owlPDO->query("select email from ".$dbname.".datakaryawan
	        where  karyawanid in(".$karyawanid.")");		
        $strAv->setFetchMode(PDO::FETCH_OBJ);
        $retMail='';
        $no=0;
        while($barAv=$strAv->fetch())
	{
            $email=$barAv->email;
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

function getNamaKaryawan($karyawanid)
{
        global $dbname;
        global $owlPDO;
        $strAv=$owlPDO->query("select namakaryawan from ".$dbname.".datakaryawan
	        where  karyawanid in(".$karyawanid.")");		
        $strAv->setFetchMode(PDO::FETCH_OBJ);
        $retnama='';
        $no=0;
        while($barAv=$strAv->fetch())
        {
                if($no==0)
                       $retnama=$barAv->namakaryawan;
                   else
                       $retnama.=",".$barAv->namakaryawan;#comma separated
         $no+=1;   
        }

        return $retnama;    
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
echo"<br>".$_SESSION['lang']['find']." <input type=text class=myinputtext id=txtcari onkeypress=\"return tanpa_kutip(event);\" size=15 maxlength=20 onblur=checkThis(this) value=All> ".$_SESSION['lang']['oncolumn'].":<select id=field>".$field."</select>
    ".$_SESSION['lang']['order']." <select id=order1>".$field."</select>,<select id=order2>".$field."</select>
	 ";
echo"<button class=mybutton onclick=\"browseTable('".$TABLENAME."');\">".$_SESSION['lang']['find']."</button>";
	
}

function printSearchOnTable($TABLENAME,$fieldname,$texttofind,$order='',$curpage=0,$MAX_ROW=1000)
{
 
 	//$MAX_ROW plese change this if required
	global $dbname;
	global $owlPDO;
	$offset   =$curpage*$MAX_ROW;//
	$disp_page=$curpage+1;
	$field=getFieldName($TABLENAME,'array'); 
	if($texttofind!='')
	{
		$where=" where ".$fieldname." like '%".$texttofind."%'"; 
	}	
	else
	{
		$where='';
	}
	$strXu=$owlPDO->query("select * from ".$dbname.".".$TABLENAME." ".$where."  order by ".$order." limit ".$offset.",".$MAX_ROW);
	$strXu->setFetchMode(PDO::FETCH_NUM);
	//get num rows of the query
	//and create page navigator
	$strXur=$owlPDO->query("select * from ".$dbname.".".$TABLENAME." ".$where);
	$strXur->setFetchMode(PDO::FETCH_NUM);
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
    if($ssl=='YES' or $ssl=='TRUE')
    {
        $host="ssl://".$host;
    }
    #mailType posible value 'text/html' or 'text/text'
    
     require_once "Mail.php";   
     $from = "Owl-Plantation<noreply@owl-plantation.com>";
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
     
     if($mailType=='text/html')
     {
         $body.="<br><br>
                 ";
                 // remove baris Surya/Dipa 20220103
                 // <i style='font-size:10pt'>Follow <a href='http://192.168.0.200/'>this link</a> to connect to OWL plantation system from office network.<br>
                 // Or <a href='http://'>this link</a> from public network.
                 // </i>";
     }    
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
    return $arr[$int][$lang][$format];
}

//fungsi untuk memeriksa apakah periode transaksi normal

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

    if($month1>$month2)
    {
        exit("Error:Month 1 > Month 2");
    }

    $thn1=substr($month1,0,4);
    $bln1=substr($month1,5,2);
    
    $thn2=substr($month2,0,4);
    $bln2=substr($month2,5,2);

     $month_array = array();
     
     if($thn1==$thn2)
     {
        if($bln2>=$bln1)
        {
            for($i=$bln1;$i<=$bln2;$i++)
            {
                if(strlen($i)<2)
                {
                    $i='0'.$i;
                }
                $month_array[$thn1.'-'.$i]=$thn1.'-'.$i;

            }
        }
     }
     else if ($thn2>$thn1)//thn ke 2 > thn ke 1
     {
        
        if($bln2>=$bln1 || $bln2<=$bln1) //bln 2 > bln 1
        {
            for($i=$bln1;$i<=12;$i++)//for untuk thn 1
            {
                if(strlen($i)<2)
                {
                    $i='0'.$i;
                }
                $month_array[$thn1.'-'.$i]=$thn1.'-'.$i;
            }
            
            for($i=1;$i<=$bln2;$i++)//for untuk thn 2
            {
                if(strlen($i)<2)
                {
                    $i='0'.$i;
                }
                $month_array[$thn2.'-'.$i]=$thn2.'-'.$i;
            }
        }  
     }
    return $month_array;
    
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