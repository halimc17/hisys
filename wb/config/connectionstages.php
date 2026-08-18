<?
$dbservererp='103.143.195.136';
$dbport  ='3306';
$dbnameerp  ='owl';
$unameerp	='owlApplication';
$passwderp	='M3r4hMeR0n4';

try{
$owlPDOERP = new PDO('mysql:host='.$dbservererp.';dbname='.$dbnameerp, $unameerp, $passwderp, array(PDO::ATTR_PERSISTENT => false));
$owlPDOERP->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
}
catch (PDOException $e) {
       print " Gagal, could not connect\n";	
       print "Error!: " . $e->getMessage() . "<br/>";
   die();
}
?>
