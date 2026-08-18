
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="shortcut icon" href="<?php echo $this->base_template();?>assets/images/favicon/favicon.ico" type="image/x-icon">
<link rel="stylesheet" href="<?php echo $this->base_template(); ?>font-awesome/css/font-awesome.css">
<title>Login</title>
</head>
<link rel=stylesheet type='text/css' href='<?php echo $this->base_template('assets/css/login.css?v=1.1'); ?>'>
<link rel=stylesheet type='text/css' href='<?php echo $this->base_template(); ?>assets/css/notifikasi.css?v=1.1'>
<script language="JavaScript1.2" src="<?php echo $this->base_template(); ?>assets/js/generic.js?v=1.2"></script>
<script language="JavaScript1.2" src="<?php echo $this->base_template(); ?>assets/js/owlproject.js?v=<?php echo date("YmdHis"); ?>"></script>

<body>
<div id="signupfield" class="block-structor">
<div class="form-structor">
	<div class="logo-client"><img id="logomenuutama" src="<?php echo $this->base_template(); ?>assets/images/logo.png"></div>	
	<div class="signup">
		<form id="login_form" method ="POST" action="process" callback="signupcallback">
		<h2 class="form-title" id="signup">Log in</h2>
		<div class="form-holder">
			<input type="text" name="username" class="input" placeholder="Username" autocomplete="username" id="name" onkeypress="return enter(event);" />
			<input type="password" name="password" class="input" placeholder="Password" autocomplete="current-password" id="pwd" onkeypress="return enter(event);"/>
		</div>	
		
		<div class="form-holder" style="margin-top:15px;">
				
				<select id="language" class="input" name="language">
				<?
				$namabahasa = $this->query("select * from ".$this->dbname.".namabahasa order by code"); 
				if($namabahasa and $namabahasa->rowCount() > 0){
					while($bar=$namabahasa->fetch())
					{ 
					echo "<option value='".$bar->code."'";
					if($bar->code=='ID') {
						echo " selected";
					}
					echo ">".$bar->name."</option>";
					}
				}else{
					echo "<option value='ID'>Bahasa</option>";
				}
				?>

			</select>
			<select id="theme" class="input" style="display:none;">
				<option value='skyblue'>Skyblue(Default)</option>
				<option value='gray'>Gray</option>		
				<option value='red'>Dark Red</option>
			</select>
		</div>
		<?php 
		$backUrl = "";
		if(isset($this->get) and count($this->get) > 0){
			$backUrl = http_build_query($this->get);
		}
		 ?>
		<button class="submit-btn" urldirect="<?php echo $backUrl;?>">Log in</button>
		<div id="msg" class="owlnotifikasi"></div>
	</from>
	</div>
	<div class="login slide-up">
		<div class="center">
			<h2 class="form-title" id="login">Powered By : <a href=http://www.owl-plantation.com target=new>nangkoel</a></h2>
		</div>
	</div>
</div>	

</div>	

<script type="text/javascript">
var myVar=setInterval(function(){myTimer()},300000); // update tiap 5 menit
var site_url_php = "<?php echo $this->site_url();?>";
var base_url_php = "<?php echo $this->base_template();?>";
function myTimer()
{
    var d = new Date();
}
window.onload=myTimer();
if ('serviceWorker' in navigator) {
	navigator.serviceWorker.register("<?php echo $this->base_template(); ?>assets/js/serviceworker-owl.js")
	.then (function () {
		console.log('sw registered bgt');
	
	});
}

window.addEventListener("DOMContentLoaded",function(){
	$ = new OwlProject();
	// $.setUrlFake(window.location.href,'login','owlproject',[]);
	$.scanFormTag(window.signupfield);
});
function signupcallback(evb){
	console.log(evb);
	if(typeof sessionStorage.api_key != 'undefined'){
		sessionStorage.removeItem("api_key");
	}
	if(typeof sessionStorage.token != 'undefined'){
		sessionStorage.removeItem("token");
	}
	if(evb.response.error == true && evb.response.message.trim() != "") {
		document.getElementById('msg').innerHTML = evb.response.message.trim();
		document.getElementById('msg').setAttribute('status','error');
		document.getElementById('msg').classList.add('onpost');
	}else{
		window.location.href = "index";
	}
	
}
</script>
<div id="progress_login_form" class="progress" style="display: none;">
<div class="progress-body">
<img src="<?php echo $this->base_template(); ?>assets/images/progress.gif?v=2">
</div>
</div>
<marquee style='width:100%;height:17px;color:#000;position: fixed;left: 0px;right: 0px;bottom: 5px;' title='Drag to move' scrolldelay='500'>
	OWL-Plantation System v.2.02
</marquee>
</body>

</html>
