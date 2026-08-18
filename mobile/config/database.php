<?
	$active_group = 'default';
	$query_builder = TRUE;
	if (isset($sysconf['application_folder'])) {
		$active_group = $sysconf['application_folder'];
	}

	$config['default'] = array(
		'development' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'developer',
			'password' => '123456',
			'database' => 'ksp',
			'dbdriver' => 'mysqli',
			'dbprefix' => '',
			'pconnect' => FALSE,
			'db_debug' => (ENVIRONMENT !== 'production'),
			'cache_on' => FALSE,
			'cachedir' => '',
			'char_set' => 'utf8',
			'dbcollat' => 'utf8_general_ci',
			'swap_pre' => '',
			'encrypt' => FALSE,
			'compress' => FALSE,
			'stricton' => FALSE,
			'failover' => array(),
			'save_queries' => TRUE
		),
		'testing' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'localuser',
			'password' => '0wL5quadD3f1n1tion',
			'database' => 'dma',
			'dbdriver' => 'mysqli',
			'dbprefix' => '',
			'pconnect' => FALSE,
			'db_debug' => (ENVIRONMENT !== 'production'),
			'cache_on' => FALSE,
			'cachedir' => '',
			'char_set' => 'utf8',
			'dbcollat' => 'utf8_general_ci',
			'swap_pre' => '',
			'encrypt' => FALSE,
			'compress' => FALSE,
			'stricton' => FALSE,
			'failover' => array(),
			'save_queries' => TRUE
		),
		'production' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'owlUser',
			'password' => ';0mTh.K%tlectCZvseqkddp#!lriexJU',
			'database' => 'owl',
			'dbdriver' => 'mysqli',
			'dbprefix' => '',
			'pconnect' => FALSE,
			'db_debug' => (ENVIRONMENT !== 'production'),
			'cache_on' => FALSE,
			'cachedir' => '',
			'char_set' => 'utf8',
			'dbcollat' => 'utf8_general_ci',
			'swap_pre' => '',
			'encrypt' => FALSE,
			'compress' => FALSE,
			'stricton' => FALSE,
			'failover' => array(),
			'save_queries' => TRUE
		)
	);
	
	$config['auth'] = array(
		'development' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'developer',
			'password' => '123456',
			'database' => 'b_auth',
			'dbdriver' => 'mysqli'
		),
		'testing' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'localuser',
			'password' => '0wL5quadD3f1n1tion',
			'database' => 'sandabiMobile',
			'dbdriver' => 'mysqli'
		),
		'production' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'owlUser',
			'password' => ';0mTh.K%tlectCZvseqkddp#!lriexJU',
			'database' => 'owlMobile',
			'dbdriver' => 'mysqli'
		)
	);
	
	$config['mharvest'] = array(
		'development' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'developer',
			'password' => '123456',
			'database' => 'b_auth',
			'dbdriver' => 'mysqli'
		),
		'testing' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'localuser',
			'password' => '0wL5quadD3f1n1tion',
			'database' => 'sandabiMobile',
			'dbdriver' => 'mysqli'
		),
		'production' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'owlUser',
			'password' => ';0mTh.K%tlectCZvseqkddp#!lriexJU',
			'database' => 'owlMobile',
			'dbdriver' => 'mysqli'
		)
	);
	
	$config['sdm'] = array(
		'development' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'developer',
			'password' => '123456',
			'database' => 'ksp',
			'dbdriver' => 'mysqli'
		),
		'testing' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'localuser',
			'password' => '0wL5quadD3f1n1tion',
			'database' => 'dma',
			'dbdriver' => 'mysqli'
		),
		'production' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'owlUser',
			'password' => ';0mTh.K%tlectCZvseqkddp#!lriexJU',
			'database' => 'owl',
			'dbdriver' => 'mysqli'
		)
	);
	
	$config['map'] = array(
		'development' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'owlMap',
			'password' => '123456',
			'database' => 'map',
			'dbdriver' => 'mysqli'
		),
		'testing' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'owlMap',
			'password' => 'NeWu53Rm',
			'database' => 'owlMap',
			'dbdriver' => 'mysqli'
		),
		'production' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'owlMap',
			'password' => 'NeWu53Rm@p',
			'database' => 'owlMap',
			'dbdriver' => 'mysqli'
		)
	);
	
	$config['prc'] = array(
		'development' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'developer',
			'password' => '123456',
			'database' => 'b_auth',
			'dbdriver' => 'mysqli'
		),
		'testing' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'localuser',
			'password' => '0wL5quadD3f1n1tion',
			'database' => 'sandabiMobile',
			'dbdriver' => 'mysqli'
		),
		'production' => array(
			'dsn'	=> '',
			'hostname' => 'localhost',
			'username' => 'owlUser',
			'password' => ';0mTh.K%tlectCZvseqkddp#!lriexJU',
			'database' => 'owlMobile',
			'dbdriver' => 'mysqli'
		)
	);
