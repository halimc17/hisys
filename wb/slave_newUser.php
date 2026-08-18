<?
require_once('master_validation.php');
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');

$uname=$_POST['uname'];
$sendmail=$_POST['sendmail'];
$pw=$_POST['pw'];
$userid=$_POST['userid'];
$active=$_POST['active'];
//Has the password
$hpw=MD5($pw);
if($sendmail==1)
	$email=getUserEmail($userid,$userid,$conn);
else
    $email='';
if($active==1)
   $ac_comment='Active';
else
   $ac_comment='Inactive';   
   $str="insert into ".$dbname.".user (uname,password,lastuser,status)
	      values('".$uname."','".$hpw."','".$_SESSION['standard']['username']."',".$active.")";
                    try{
                              $owlPDO->exec($str);   
		echo "*Account ".$uname." has been created.<br>";
		//if email is available then send an email to user
		if($email!='')
		{
			$subject='Your User Account has been created';
			$content="Dear ".$uname.",<br><br>
			          <dd>Your Account has been created as follow:
					  <table>
					  <tr><td><i>UserName</i></td><td>:".$uname."</td></tr>
					  <tr><td><i>Password</i></td><td>:".$pw."</td></tr>
					  <tr><td><i>UserId(Empl.ID)</i></td><td>:".$userid."</td></tr>
					  <tr><td><i>AccountStatus</i></td><td>:".$ac_comment."</td></tr>
					  </table><br>
					  Please maintain your password periodically.
					  <br>
					  Regards,
					  System, at ".date('d-m-YYY H:i:s');
			//$from   ='administrator@'.$_SERVER['HOST'].'.local';
			$to     =$email;	  
			kirimEmail($to,$cc = "",$subject,$content,$mailType='text/html');
		}
                    }
                    catch (PDOException $e) {
                               print " Gagal  !: " . $e->getMessage() . "<br/>";
                               die();
                        }
?>
