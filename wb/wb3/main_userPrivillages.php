<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src="js/menusetting.js?v=<?php echo time(); ?>"></script>
<link rel=stylesheet type=text/css HREF=style/privillages.css>
<?
include('master_mainMenu.php');

//set max_id for menu============
$str=$owlPDO->query("select max(id) as id from ".$dbname.".menu");
$str->setFetchMode(PDO::FETCH_NUM);
$max_id=0;
while($bar=$str->fetch())
{
	$max_id=$bar[0];
}
echo"<script language=javascript1.2>
     max_id=".$max_id."
	 </script>";
//*************************************	 
OPEN_BOX('','<span class=judul>'.strtoupper('Pengaturan hak Akses').'</span>');
echo OPEN_THEME('Pengaturan hak Akses:');
//**********************************
//Main content
echo"<div class=privillageIcon><img src='images/useraccounts.png' height=40px style='vertical-align:middle;'><b>Pengaturan Hak:</b></div>";
echo"<fieldset>
    <legend>Levelisasi dan Pilihan Hak:</legend> 
    <ul>
    <li class=mmgr><img src='images/menu/arrow_10.gif'>Atur levelisasi</li><br>
		<button style=\"background-color: #e6e6e6;color: black; border: 1px solid #183999;padding: 3px 10px;text-align: center;text-decoration: none;display: inline-block;vertical-align:middle;font-size: 12px;margin: 2px 1px;transition-duration: 0.4s;cursor: pointer;border-radius: 5px;\" id=lab3 title='Use detail privileges'  onclick=loadDetailPrivillageSetting(this,event,'')>Gunakan Hak-hak Detail Per Pengguna</button>
	 </fieldset><br>"; 
//====================  
 
echo CLOSE_THEME();
echo"<div id=ctr style='position:absolute;display:none;'>";
        echo OPEN_THEME('Menu/User Level And Privileges:');
                echo"<div id=content>";
                echo"</div>";
        echo CLOSE_THEME();
echo"</div>";
echo"<div id=ctrmenu style='position:absolute;display:none;'>";
        echo OPEN_THEME('Menu Mapping:');
                echo"<div id=contentmenu>";
                echo"</div>";
        echo CLOSE_THEME();
echo"</div>";
echo"<div id=globalakses style='position:absolute;display:none;'>";
        echo OPEN_THEME('Choose Menu:');
                echo"<div id=contentglobal>";
                echo"</div>";
        echo CLOSE_THEME();
echo"</div>";        
CLOSE_BOX();	
echo close_body();
?>
