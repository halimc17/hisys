<?
$theme = 'skyblue';
$men='menu.css';
$gen='generic.css';
$bgColor='rgb(12, 49, 75);';
$logo='login-bacldrop.jpg';
$menuJs='menuscript.js';
$drwImgDef='tab3.png';
$drwImgSec='tab1.png';
$bgTabInner='#E0ECFF';
$bgTabOuter='#1E5896';
$version = "?ver=".VERSION;
$description = '';
$$keywords = array();
echo"<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">
<html>
	<head>
		<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">
		<meta name=\"viewport\" content=\"user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width\" />
		<meta http-equiv='Cache-Control' CONTENT='no-cache'>
		<meta http-equiv='Pragma' CONTENT='no-cache'>
		<meta name='description' content='".$description."' />
		<meta name='keywords' content='".implode(",",$$keywords)."' />		
		<link rel=\"shortcut icon\" href=\"".$this->base_template()."assets/images/favicon/favicon.ico".$version."\" type=\"image/x-icon\">
	<title>".APP_NAME."</title>";
echo" 
    <script language=JavaScript1.2 src=".$this->base_template()."assets/js/".$menuJs.$version."></script>
	<!-- generic.js Ver.1.1 : Penambahan read file CSV  -->
    <script language=JavaScript1.2 src=".$this->base_template()."assets/js/generic.js".$version."></script>
	<script language=JavaScript1.2 src=".$this->base_template()."assets/js/genericproject.js".$version."></script>
    <script language=JavaScript1.2 src=".$this->base_template()."assets/js/owlproject.js".$version."></script>
	 <script language=JavaScript1.2 src=".$this->base_template()."assets/js/datepicker.js".$version."></script>
	 <script language=JavaScript1.2 src=".$this->base_template()."assets/js/datepicker.setup.js".$version."></script>
	<link rel=stylesheet type=text/css href=".$this->base_template()."assets/css/".$men.$version.">
	<link rel=stylesheet type=text/css href=".$this->base_template()."assets/css/".$gen.$version.">	
	<link rel=stylesheet type=text/css href=".$this->base_template()."assets/css/datepicker.css".$version.">
	<link rel=stylesheet type=text/css href=".$this->base_template()."assets/css/panel.css".$version.">
	<link rel=stylesheet type=text/css href=".$this->base_template()."assets/css/notifikasi.css".$version.">
	<link rel=\"stylesheet\" href=\"".$this->base_template()."font-awesome/css/font-awesome.css\">
	<link href=\"".$this->base_url()."lib/alertify/css/alertify.css\" rel=\"stylesheet\"/>
	<link href=\"".$this->base_url()."lib/alertify/css/themes/default.css\" rel=\"stylesheet\"/>
	<script src=\"".$this->base_url()."lib/alertify/js/alertify.min.js?v=3\"></script>

	<link rel=\"stylesheet\" href=\"".$this->base_template()."assets/map/css/default.css".$version."\" type=\"text/css\"/>
	<script type=\"text/javascript\" src=\"".$this->base_template()."assets/map/js/default.js".$version."\"></script>
	<link rel=\"stylesheet\" href=\"".$this->base_template()."assets/richtexteditor/rte_theme_default.css\" />
	<script type=\"text/javascript\" src=\"".$this->base_template()."assets/richtexteditor/rte.js\"></script>
	<script type=\"text/javascript\" src=\"".$this->base_template()."assets/richtexteditor/plugins/all_plugins.js\"></script>
				
    </head>
<body style='background-image:url(".$this->base_template()."assets/images/".$logo.");background-size: cover;background-position-y: 30px;' onload=verify()>
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
// fungsi user setting masih di matikan usersetting()
$user_name = $_SESSION['standard']['username'];
$user_name = ucwords(str_replace("."," ",$user_name));
echo '<div id="wraperbody" class="body">   
	<div class="header" style="width:100%;">
		<div style="position:fixed;top: 0px;left:0px;z-index: 2;">
			<a id="logomenuutama" class="logo_menuutama" style="display:block;"><img src="'.$this->base_template().'assets/images/logo-white.png"></a>
		</div>
		<div class="user">	
			<label class="btn-menu-bar menu-user fa fa-user" for="toggle_menu_user" title="User"></label>
			<label class="btn-menu-bar menu-notif fa fa-bell" for="toggle_menu_notif" title="Notifikasi"></label >
			<!--<label class="btn-menu-bar menu-mail fa fa-envelope" for="toggle_menu_mail" title="Mail Box"></i></label >-->
			
			<div id="toggle_menu_user" class="list-menu-user noselect">
				<div class="title">Identitas User</div>
				<div class="description">';
					echo '<div class="row">';
					echo '<div class="col-4">Username</div><div class="col-8">: '.((ISSET($_SESSION['standard']['username']))?$_SESSION['standard']['username']:' - ').'</div>';
					echo '<div class="col-4">Name</div><div class="col-8">: '.((ISSET($_SESSION['empl']['name']))?$_SESSION['empl']['name']:' - ').'</div>';
					echo '</div></div>
				<div class="clearfix"></div>
				<hr/>
				<ul>
					<li class="fa fa-gear" title="Setting" onclick="javascript:usersetting();"><span>Setting</span></li>
					<li class="fa fa-sign-out" onclick=signout() title="Logout"><span>Logout</span></li>
					
				</ul>
			</div>
			
			<div id="toggle_menu_notif" class="list-menu-notif noselect">
				<div class="title">Notifikasi</div>
			</div>
			
			<div id="toggle_menu_mail" class="list-menu-mail noselect">
				<div class="title">Mail Box</div>
			</div>
			
			<!--<<label class="btn-menu-bar menu-mail fa fa-envelope list-menu-active" for="toggle_menu_mail" title="Mail Box"></label>-->
			<label class="menu-devider"></label >
			<label class="menu-home" onclick="javascript:getHrefUtama(\'modules/master_front\',\'master_front\');" title="Kembali ke Dashboard"><i class="fa fa-dashboard"></i></label >
			<label class="menu-home" onclick="javascript:getHrefUtama(\'modules/map\',\'map\');" title="Map"><i class="fa fa-map"></i></label>
			<input class="search-jump myinputtext" type="hidden" oninput=jump(this.value,event) list=jump  title="Shortcut to specific menu"></input>
			<label class="btn-menu-bar menu-user">'.$user_name.' <i class="fa fa-angle-left" aria-hidden="true"></i> '.@$_SESSION['empl']['namaorganisasi'].' <i class="fa fa-angle-right" aria-hidden="true"></i></label>
			
		</div>
		<div class="user_alert"> 
			<label class="menu-devider"></label >
			<label class="btn-menu-bar menu-user fa fa-isonline"></label>
		</div>
		
	  <datalist id=jump>';
		if(count($menu_jump['id'])>0){  
		   foreach($menu_jump['id'] as $key=>$val){
			  echo"<option id='".$val."' value='".$val."' action='".$menu_jump['action'][$key]."'>".$menu_jump['caption'][$key]."</option>";
			}
		  }
		echo"   
		  </datalist>
		  <select id=jumpList style='display:none;'>";
		  if(count($menu_jump['id'])>0)
		  {  
		   foreach($menu_jump['id'] as $key=>$val){
			  echo"<option id='".$val."' value='".$val."' action='".$menu_jump['action'][$key]."'>".$menu_jump['caption'][$key]."</option>";
			}
		  }
	echo '
	  </select>
	</div>';

echo "
<div id='menuwrapper' class='openmenu' onmousehover='openmenu();'>
<div id='menuutama'>";

function randomColor(){
    $result = array('rgb' => array(), 'hex' => '');
	$rgb = array('r', 'b', 'g');
    foreach($rgb as $col){
        $rand = mt_rand(0, 255);
        $result['rgb'][$col] = $rand;
         $dechex = dechex($rand);
        if(strlen($dechex) < 2){
            $dechex = '0' . $dechex;
        }
        $result['hex'] .= $dechex;
    }
    return $result;
}
echo "<ul class='homemenu'>";
$this->load->model('Menumap');
$list = $this->Menumap->loadMenu($_SESSION['standard']['username'],'0'); 
foreach($list as $bar_m1){
        $master_id=$bar_m1->id;
		$rgb = randomColor()['rgb'];
		if(!empty($bar_m1->icon_path) or $bar_m1->icon_path == ""){
			$iconImg = "assets/images/navigasi/folder-fully.png";
		}else{
			$iconImg = "assets/images/icon".$bar_m1->icon_path;
		}	
        echo"<li class='menubox arrow ' style='' ch=\"".$bar_m1[id]."\"><div><img class='menumastericon' src='".$this->base_template().$iconImg."'></div><a id='menu_".$bar_m1[id]."' parentid='".$bar_m1[induk]."' class=\"qmparent\">".$bar_m1[caption2]."</a></li>";
}
echo "</ul>";
echo "
<div id=\"deviderMenu\" class=\"deviderMenu\"></div>
</div>";
echo"
</div>";

?>
<div id='progress' class='progress' style='display:none;'>
<div class="progress-body">
Please wait.....! <br>
<img src="<? echo $this->base_template();?>assets/images/progress.gif?v=3">
</div>
</div>

<div id="bodymaster" class="dashboard backbody openmenu" self-path="<?php echo @$pathFirst['basename']; ?>"><div>
<? 
echo "</div><div style='clear:both;'>";
$backUrl = "";
if(!empty($this->get('q'))){
	$backUrl = $this->get('q');
}
?>
<script>
	var $ = null;
	sessionStorage.api_key = '<?php echo @$_SESSION['standard']['api_key']; ?>';
	sessionStorage.token = '<?php echo @$_SESSION['standard']['token']; ?>';
	var site_url_php = "<?php echo $this->site_url();?>";
	var base_url_php = "<?php echo $this->base_template();?>";
	var backUrl = '<?php echo $backUrl;?>';
	function getHrefUtama(Url,title){
		tujuan = site_url(Url);
			getPageOwlProject(tujuan,title,"click",[]);
	}
	window.addEventListener("load",function(){
		if(typeof options !== 'undefined'){
			if(typeof $ != 'undefined' && $ !== null){
				
				$.resetProject(options);
			}else{
				$ = new OwlProject(options);
			}
		}else{
			//Refresh
			if(typeof $ != 'undefined' && $ !== null){
				$.resetProject();
			}else{
				$ = new OwlProject();
			}
		}
		swiperColFrame();
		if(document.getElementById("toggle_menu_mail")){
			var eleMailBox = document.getElementById("toggle_menu_mail");
			//var notifMail = new createNotifMailPop(eleMailBox,{interval:30000});
		}
		var data = {};
		if((window.history.state == null || window.history.state.page == 'master_front' || window.history.state.page == 'map') && backUrl.trim() == "" ){
			data.page = 'map';
			tujuan = site_url('modules/'+data.page);
			if(window.history.state!== null){
				data.page = window.history.state.page;
				tujuan = window.history.state.href;
			}
			getPageOwlProject(tujuan,data.page,"click",[]);
		}else{
			let pageCi = window.history.state;
			if(window.history.state !== null){
				data.page = pageCi.page;
				// tujuan = window.history.state.href;
			}
			if(backUrl.trim() != ""){
				data.page = backUrl.trim();
			}
			ifEnterForJummping(data,'refresh');
		}
		//if(sessionStorage.menuFormat == "v2"){
			toggleFormatMenuUtama(sessionStorage.menuFormat);
			openmenuutama();
		//}
		toUnderElement();
		openUserMenu();
	},false);
	function usersetting(){
		var option = {
			window:'right',
			width:300
		};
		$.newWindow("modules/setting","User Panel","userpanel",false,false,option);
	}
	
	window.addEventListener('keydown',function(evt){
		evt = evt || window.event;
		if (evt.keyCode == 27){ //if ESC
			if(document.getElementById('jumpMainmenu')){
				jumpMainmenu();
			}
		}
	});
	
	
</script>
</body>
</html>