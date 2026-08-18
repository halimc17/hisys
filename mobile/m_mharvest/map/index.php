<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="format-detection" content="telephone=no" />
    <meta name="msapplication-tap-highlight" content="no" />
    <meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width" />
	<meta http-equiv=“Content-Security-Policy” content=“default-src ‘self’ gap://ready file://* *; style-src ‘self’ ‘unsafe-inline’; script-src ‘self’ ‘unsafe-inline’ ‘unsafe-eval’”/>
    <title>OWL Mobile</title>
    <script type="text/javascript" src="cordova.js"></script>
	
    <link rel="stylesheet" href="style/owlMobile.css" type="text/css"/>
	<link rel="stylesheet" href="style/owlMobilePlus.css" type="text/css"/>
	<link rel="stylesheet" href="style/owlMobileGrid.css" type="text/css"/>
    <link rel="stylesheet" href="style/calendar-mobile.css" type="text/css"/>
	<link rel="stylesheet" href="style/touch-sideswipe.css">
	<link rel="stylesheet" href="style/slidetouch.css">
	<link rel="stylesheet" href="style/map.css">
	<link rel="stylesheet" href="font-awesome/css/font-awesome.css">
	<script type="text/javascript" src="js/bluetooth.js"></script>
	<script type="text/javascript" src="js/notification.js"></script>
	<script type="text/javascript" src="js/connection.js"></script>
	<script type="text/javascript" src="js/generate_md5.js"></script>
    <script type="text/javascript" src='js/owlMobile.js?ver=1.2'></script>
    <script type="text/javascript" src='js/mobileTransaction.js'></script>
    <script type="text/javascript" src='js/calendar-mobile.js'></script>
	<script id="language" type="text/javascript" src='lang/ID.js'></script>
    <script type="text/javascript" src='js/svg-pan-zoom.js'></script>
    <script type="text/javascript" src='js/formjs/gpsCanvas.js'></script>
    </head>
<body onresize="resizeAllPanel()" onload="resizeAllPanel()">
<div id="owl-content" class="owl-container bg-white full-height">
	<div id="header" class="header-content"> 	
		<div class="container-fluid relative">
			<div class="pull-right full-height visible-sm visible-xs">
				<div class="both_search_map">
				<a id="backsearchmap" class="backsearchmap"><i class="fa fa-arrow-left" aria-hidden="true"></i></a>
				<input id="search_map" class="search_map" type="search" placeholder="Search" onfocus="searchmap('open');"> <!--onfocusout="searchmap('close');"-->
				</div>
			</div>
			<div class="clearfix"></div>
		</div>
		<div id="cache-search-both">
			<ul class="cache-search-list">
				<li class="pin"><a>Cache List</a></li>
				
			</ul>
		
		</div>
	</div>
	<div id="owl-content-wrapper" class="owl-content-wrapper">
		<div id="content" class="content">
			<div class="col-xl-12 col-lg-12  col-sm-12 col-xs-12 no-padding" id="home_map" style="left:0px;top:0px;overflow: hidden;position:fixed;bottom:0px;">
			</div>
		</div>
	</div>	 
	<div id="footermap" class="footer-content">
		<div class="footer-act">
			<div id="openclosenavboth" class="fa fa-arrow-up btnact btnact1"></div>
			<div id="openfloatnavboth" class="fa fa-arrow-right btnact btnact2"></div>
		</div>
		<div class="footer-nav">
			<a class="col-xl-4 col-lg-4 col-sm-4 col-xs-4 no-padding foot-nav-map active" content-id="map-work">
				<i class="fa fa-briefcase" aria-hidden="true"></i>
			</a>
			<a class="col-xl-4 col-lg-4 col-sm-4 col-xs-4 no-padding foot-nav-map" content-id="map-infra">
				<i class="fa fa-road" aria-hidden="true"></i>
			</a>
			<a class="col-xl-4 col-lg-4 col-sm-4 col-xs-4 no-padding foot-nav-map" content-id="map-setting">
				<i class="fa fa-cog" aria-hidden="true"></i>
			</a>
			<div class="clearfix"></div>
		</div>
		<div class="clearfix"></div>
		<div class="footer-nav-both">
			<div id="map-work" class="footer-nav-content active">
				
			</div>
			<div id="map-infra" class="footer-nav-content">
				<ul class="col-xl-6 col-lg-6 col-sm-6 col-xs-6 no-padding">
					<li>Sungai</li>
					<li>Jalan</li>
					<li>Jalan Raya</li>
				</ul>	
				<ul class="col-xl-6 col-lg-6 col-sm-6 col-xs-6 no-padding">
					<li>Jempatan</li>
				</ul>
				<div class="clearfix"></div>
			</div>
			<div id="map-setting" class="footer-nav-content">
				<ul class="col-xl-6 col-lg-6 col-sm-6 col-xs-6 no-padding">
					<li><a class="" onclick="reGettingBlockSvg();"><i class="fa fa-refresh m-r-10"></i>Download Offline</a></li>
					<li><a class="" onclick=""><i class="fa fa-refresh m-r-10"></i>Syn Data</a></li>
				</ul>	
				
				
				<div class="clearfix"></div>
			</div>
		</div>
	</div>
</div>
<div id='detailmenu'></div>

<div class="progress">
	<img src="images/progress-circle-success.svg" height="50px" style="background:unset;box-shadow:unset;">
</div>

<div id="lihatlog">
	<div id="lihatlogdata" >
		<!-- console log -->
	</div>
</div>
<div id="touchSideSwipe" class="touch-side-swipe">
<!-- header NEW author Atwal Arifin -->
<nav id="owl-sidebar" class="owl-sidebar " data-pages="sidebar">
	<div class="sidebar-header">
		<a href="index.html"><img src="img/logo.png" class="mainmenulogo m-l-20 m-t-20"></a>
		<div class="sidebar-header-controls pull-right">
		<!-- <a class="btn-link margin-5 icon-set-dark menu-times m-r-10 m-t-10" onclick="closeMainMenu()"></a>-->
		</div>
	</div>
	<br>
	<div class="clearfix"></div>
	<div class="sidebar-menu p-t-20" >
		<ul id="identity" class="menu-content">
			<li id="lokasi_user" class="list-menuside"></li>
			<!--<li id="nama_user" class="list-menuside"></li>-->
		</ul>
		<!--<ul id="allmenu" class="menu-content"></ul>-->
		<ul id="menumodule" class="menu-content">
			
		</ul>
		<div class="clearfix"></div>
	</div>
</nav>

</div>
</body>
	<script type="text/javascript" src="js/touch-sideswipe.js"></script>
    <script type="text/javascript">
		//* detect CSS text-shadow support in JavaScript
		function onDeviceReadySwipe() {
			//menuside_start();
			//Check_tabel('loginonfo');
			//Check_tabel('menumobile');
			
			var config = {
				elementID: 'touchSideSwipe',
				elementWidth: 400, //px
				elementMaxWidth: 0.8, // *100%
				sideHookWidth: 0, //px
				moveSpeed: 0.2, //sec
				opacityBackground: 0.6,
				shiftForStart: 50, // px
				windowMaxWidth: 1024, // px
			}
			var touchSideSwipe = new TouchSideSwipe(config);
			console.log("map");
			footerNaveMapSwipe();
			trackingGPSNow();
			//writeConsole();
		}
		function onDeviceReadyOld() {
			//menuside_start();
			//Check_tabel('loginonfo');
			//Check_tabel('menumobile');
			console.log("map");
			trackingGPSNow();
			//notifAlert('Your android version is too old','{perhatian}');
		}
		if (document.createElement("detect").style.animationName !== undefined) {
			//document.addEventListener("deviceready", onDeviceReady,false);
			document.addEventListener("DOMContentLoaded", onDeviceReadySwipe,false);
			if(window.location.href.search("map.html") != -1){
				document.addEventListener("DOMContentLoaded", footerNaveMapSwipe,false);
			}
		}else{
			//document.addEventListener("deviceready", onDeviceReadyOld,false);
			document.addEventListener("DOMContentLoaded", onDeviceReadyOld,false);
			
			
		}
		
    </script>
	<script id="myscript" type="text/javascript" src=""></script>
</html>