<?php

if(isset($_POST['username']) && isset($_POST['password'])){
		
	$ldap_hostname = "10.1.1.34";
	$ldap_port = "389";
	$ldap_dn = "cn=admin,dc=ir-group,dc=local";
	$ldap_search = "dc=ir-group,dc=local";
	$ldap_password ="Pa55w0rd";
	
	$username=$_POST['username'];
	$password=$_POST['password'];


	$ldap_con = ldap_connect($ldap_hostname,$ldap_port);
	ldap_set_option($ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);
	ldap_set_option($ldap_con, LDAP_OPT_REFERRALS, 0);

	if(ldap_bind($ldap_con, $ldap_dn, $ldap_password)){
		$filter="(uid=".$username.")";
		$dn=$ldap_search;
		$res = ldap_search($ldap_con, $ldap_search, $filter);
		ldap_sort($ldap_con,$res,"sn");
		$info = ldap_get_entries($ldap_con, $res);
		$first = ldap_first_entry($ldap_con, $res);
		$data = ldap_get_dn($ldap_con, $first);
	}else{
		echo "<br>Error bind getUserDN function<br>" . ldap_error($ldap_con);
	}

	// $info = ldap_get_entries($ldap['conn'], $res);

	$ldap_Userdn = $data;
	if(ldap_bind($ldap_con, $ldap_Userdn, $password)){
		$val = "Berhasil";
	}else{
		$val = "Tidak Berhasil";
	}

	echo"<pre>";
	print_r($info);
	echo"</pre>";
		
	ldap_close($ldap_con);
}
?>

<!DOCTYPE html>
<html>
<head>
  <title></title>
</head>
<body>
<form action="" method="post">
<input name="username">
<input type="password" name="password">
<input type="submit" value="Submit">
</form>
</body>
</html>
