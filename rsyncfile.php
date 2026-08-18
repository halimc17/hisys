<?php 
	exec('rsync --ignore-existing -raz --progress /var/www/html/owl/fileupload/* /mnt/fileupload/');
?>