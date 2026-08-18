<?php
require_once('master_validation.php');
$contents = file_get_contents('/var/www/html/svnUpdateSth.sh');
$var=shell_exec($contents);
echo "<pre>";
print_r($var);
echo "</pre>";
?>