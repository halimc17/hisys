<?
	$theme = 'skyblue';
	$men = 'menu.css';
	$gen = 'generic.css';
	$bgColor = 'rgb(12, 49, 75);';
	$logo = 'login-backdrop.jpg';
	$drwImgDef = 'tab3.png';
	$drwImgSec = 'tab1.png';
	$bgTabInner = '#E0ECFF';
	$bgTabOuter = '#1E5896';
	$version = "?ver=".VERSION;
	$description = '';
	$site_url_api = $this->site_url("/api","map.php");
	$keywords = array();
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
		<meta name="viewport" content="user-scalable=no, initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width" />
		<meta http-equiv='Cache-Control' CONTENT='no-cache'>
		<meta http-equiv='Pragma' CONTENT='no-cache'>
		<meta name='description' content='<?= $description ?>' />
		<meta name='keywords' content='<?= implode(",",$keywords) ?>' />

		<link rel="shortcut icon" href="<?= $this->base_template() ?>assets/images/favicon/favicon.ico<?= $version ?>" type="image/x-icon">

		<title><?= APP_NAME ?></title>

		<!-- generic.js Ver.1.1 : Penambahan read file CSV -->
		<script language=JavaScript1.2 src="<?= $this->base_template() ?>assets/js/generic.js<?= $version ?>"></script>
		<script language=JavaScript1.2 src="<?= $this->base_template() ?>assets/js/genericproject.js<?= $version ?>"></script>
		<script language=JavaScript1.2 src="<?= $this->base_template() ?>assets/js/owlproject.js<?= $version ?>&m=<?= $this->uri->segments[1] ?>"></script>
		<script language=JavaScript1.2 src="<?= $this->base_template() ?>assets/js/datepicker.js<?= $version ?>"></script>
		<script language=JavaScript1.2 src="<?= $this->base_template() ?>assets/js/datepicker.setup.js<?= $version ?>"></script>
		<script language=JavaScript1.2 src="<?= $this->base_template() ?>../ochart/assets/js/chart.umd.min.js<?= $version ?>"></script>
		

		<link rel=stylesheet type=text/css href="<?= $this->base_template() ?>assets/css/<?= $men ?><?= $version ?>">
		<link rel=stylesheet type=text/css href="<?= $this->base_template() ?>assets/css/<?= $gen ?><?= $version ?>">
		<link rel=stylesheet type=text/css href="<?= $this->base_template() ?>assets/css/datepicker.css<?= $version ?>">
		<link rel=stylesheet type=text/css href="<?= $this->base_template() ?>assets/css/panel.css<?= $version ?>">
		<link rel=stylesheet type=text/css href="<?= $this->base_template() ?>assets/css/notifikasi.css<?= $version ?>">
		<link rel="stylesheet" href="<?= $this->base_template() ?>font-awesome/css/font-awesome.css">
		<link rel="stylesheet" href="<?= $this->base_template() ?>assets/map/css/default.css<?= $version ?>" type="text/css"/>
		<link rel=stylesheet type=text/css href="<?= $this->base_template() ?>../ochart/assets/css/chart.css">

	</head>
	<body onload=verify()>
		<div id="wraperbody" class="body">
			<div style="width:100%;">
				<div style="position:fixed;opacity:.5;bottom: 10px;left:0px;z-index: 2;">
					<a id="logomenuutama" class="logo_menuutama" style="display:block;">
						<img src="<?= $this->base_template(); ?>assets/images/logo-white.png">
					</a>
				</div>
				<div class="user"></div>
			</div>
			<div id="bodymaster" class="dashboard backbody openmenu" self-path="<?= @$pathFirst['basename']; ?>" style="top:0px; background:#f1f3f6">
				<script language="JavaScript1.2">
					var $ = null;

					sessionStorage.client_secret = "<?= $this->client_secret; ?>";
					sessionStorage.api_key = "<?= $this->api_key; ?>";
					sessionStorage.token = "<?= $this->token; ?>";

					var site_url_php = "<?= $this->site_url(); ?>";
					var base_url_php = "<?= $this->base_url(); ?>";
					var base_template_php = "<?= $this->base_template(); ?>";
					
					window.addEventListener("load", function() {
						if (typeof options !== 'undefined') {
							if (typeof $ != 'undefined' && $ !== null) {
								$.resetProject(options);
							} else {
								$ = new OwlProject(options);
							}
						} else {
							//Refresh
							if (typeof $ != 'undefined' && $ !== null) {
								$.resetProject();
							} else {
								$ = new OwlProject();
							}
						}
						
						const jss = ['tool_chart', 'tool_chartGenerator']
						jss.forEach(js => {
							var script = document.createElement("script");
							script.src = base_template_php + "../ochart/assets/js/" + js + ".js" + "<?= $version ?>";
							script.language = "JavaScript1.2";
							document.head.appendChild(script);
						});

						$.get(false, site_url_php + "modules/<?= $this->appName; ?>_generator_slave?switcher=load", (e) => {
							console.log("Response:", e);
							$.dataJson = JSON.parse(e.response.data);
							$.menuView = e.response.view;
							window.bodymaster.innerHTML = $.menuView;

							let filejs = document.createElement("script");
							filejs.src = base_url_php + "js/<?= $this->appName; ?>_generator.js" + "<?= $version ?>";
							filejs.language = "JavaScript1.2";
							filejs.type = "text/javascript";
							document.head.appendChild(filejs);
						});
					}, false);
				</script>
			</div>
		</div>
	</body>
</html>