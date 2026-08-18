<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html style="background-color: #5084bf;">
<head>
<meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="shortcut icon" href="<?php echo $this->base_template();?>assets/images/favicon/favicon.ico" type="image/x-icon">
<link rel="stylesheet" href="<?php echo $this->base_template(); ?>font-awesome/css/font-awesome.css">
<!-- <link rel="stylesheet" href="<?php echo $this->base_template(); ?>font/font-family.css"> -->
<title>Client Secret</title>
</head>
<link rel=stylesheet type='text/css' href='<?php echo $this->base_template('assets/css/login.css?v=1.1'); ?>'>
<link rel=stylesheet type='text/css' href='<?php echo $this->base_template(); ?>assets/css/notifikasi.css?v=1.1'>
<script language="JavaScript1.2" src="<?php echo $this->base_template(); ?>assets/js/owlproject.js?v=<?php echo date("YmdHis"); ?>"></script>
<body>
<div class="block-structor">
	<div class="logo-client"><img id="logomenuutama" src="<?php echo $this->base_template(); ?>assets/images/logo.png"></div>	
	<div class="form-structor" style="height: 250px;">
		<div class="signup">
			<form id="login_form" method ="POST" action="" callback="">
			<h2 class="form-title" id="signup"></h2>
			<div class="form-holder" style="display:none;">
				<input type="text" name="client_id" class="input" value="<?php echo $this->client_id; ?>" placeholder="client id"/>
			</div>
			<div class="form-holder">
				<input type="text" name="client_secret" class="input" placeholder="Client Secret"/>
			</div>
			<button class="submit-btn" urldirect="<?php echo $backUrl;?>">Submit</button>
			<div id="msg" class="owlnotifikasi"></div>
		</form>
		</div>
	</div>	
</div>	
<div id='progress' class='progress' style='display:none;'>
	<div class="progress-body">
		Please wait.....! <br>
		<img src="<?php echo $this->base_template();?>assets/images/progress.gif?v=3">
	</div>
</div>
<script type="text/javascript">
var site_url_php = "<?php echo $this->site_url();?>";
var base_url_php = "<?php echo $this->base_template();?>";
	window.addEventListener("DOMContentLoaded",function(){
		$ = new OwlProject();
		$.scanFormTag(window.signupfield);
	});
</script>
</body></html>
