<?
require_once('../config/connection.php');
require_once('../lib/nangkoelib.php');
require_once('lib/zLib.php');

if($_SESSION['language']=='ID'){
	$cpt="caption";
}else if($_SESSION['language']=='EN'){
	$cpt="caption2";
}else if($_SESSION['language']=='MY'){
	$cpt="caption3";
}

// echo"<pre>";
// print_r();
// echo"</pre>";

$tab = "<div id='links'>
	<div id='deco'>";
		
		## GET ALL MENU
		$str="select * from ".$dbname.".menubi where type='master' and hide='0' order by urut asc";
		$res=fetchdata($str);
		foreach($res as $val){
			if($val['caption']=='MAP'){
				$imgicon="images/mapicon.png";
			}
			if($val['caption']=='GRAPH'){
				$imgicon="images/graphicon.png";
			}
			
			if(in_array($val['id'],$_SESSION['priv'])){
				$showstyle="";
			}else{
				$showstyle="style=display:none";
			}
			
			## GET AUTH USER
			$strx="select * from ".$dbname.".authbi where namauser='".$_SESSION['standard']['username']."'";
			$resx=fetchdata($strx);
			
			$tab.="<div class='bt' ".$showstyle."><a onclick='".$val['action']."()'>&nbsp;";
			$tab.="<img class=iconmenukiri style='vertical-align:middle' src='".$imgicon."' onclick='".$val['action']."()'>&nbsp;".$val[$cpt]."</a></div>";
		}
		
		## BUTTON LOGOUG
		$tab.="<div class='bt'><a onclick='logout()'>&nbsp;
			<img class=iconmenukiri style='vertical-align:middle' src='images\logouticon.png' onclick='map()'>&nbsp;LOGOUT</a></div>";
$tab.="</div>
</div>";
echo $tab;

?>