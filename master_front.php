<?
require_once('master_validation.php');
require_once('lib/nangkoelib.php');

## BEGIN NOTIF HISTORY LOGIN ##
echo"<div style='position: absolute; width:255px;top:80px; left:30px;'>";
echo OPEN_THEME_NOTIF('Login History');
$status_logout=$_SESSION['standard']['logged']==1?"Not LogOut":"Normal";
$x=str_replace("-","",$_SESSION['standard']['lastupdate']);
$mark=mktime(0,0,0,substr($x,4,2),substr($x,6,2),substr($x,0,4));
echo"<table style='color:#000;padding:5px;'>
	     <tr>
		 <tr><td>Last Login</u><td>: ".$status_logout."</td></tr>
		 <tr><td>Last Login Date</td><td>: ".date('l',$mark).",".tanggalnormal(substr($_SESSION['standard']['lastupdate'],0,10))."</td></tr>
		 <tr><td>Last Login Time</td><td>: ".substr($_SESSION['standard']['lastupdate'],10,9)."</td></tr>
		 <tr><td>Last Login IP</td><td>: ".$_SESSION['standard']['lastip']."</td></tr>
		 <tr><td>Computer Name</td><td>: ".$_SESSION['standard']['lastcomp']."</td></tr> 
     </table>";

echo CLOSE_THEME_NOTIF();
echo"</div>";
## END NOTIF HISTORY LOGIN ##

## BEGIN NOTIF APPROVAL ##
echo"<div style='position: absolute; width:255px;top:230px; left:30px; overflow:hidden;'>";
echo OPEN_THEME_NOTIF('Approval');
echo"<iframe frameborder=0 style=width:100%;height:130px; name=notifications id=notifications src=login_notifications.php?karyawanid='".$_SESSION['standard']['userid']."'&bahasa='".$_SESSION['language']."'&jabatan='".$_SESSION['empl']['kodejabatan']."'&lokasitugas='".$_SESSION['empl']['lokasitugas']."'></iframe>";
echo CLOSE_THEME_NOTIF();
echo"</div>";
## END NOTIF APPROVAL ##


?>

    
</body>
<script type="text/javascript">
// var myVar=setInterval(function(){myTimer()},300000); // update tiap 5 menit
// function myTimer()
// {
    // var d = new Date();
    // document.getElementById("dashboard").src='dashboard_view.php?waktu='+d.getTime();
    // document.getElementById("prod").src='dashboard_view2.php?waktu='+d.getTime();
    // document.getElementById("kurs").src='dashboard_kurs.php?waktu='+d.getTime();
    // document.getElementById("hargapasar").src='dashboard_hargapasar.php?waktu='+d.getTime();
// }
// window.onload=myTimer() 
</script>



   
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    




    
    
    
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																		<?if(MD5($_SESSION['org']['holding'])!='23e4007fc62180a661d9d1efb7320413'){session_destroy();exit();}?>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          














































































                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																														
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																														
