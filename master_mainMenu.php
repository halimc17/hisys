<?
require_once('config/connection.php');
require_once('lib/nangkoelib.php');
require_once('lib/zLib.php');
?>

<!-- <script src='lib/alertify/js/jquery.min.js'></script> -->
<link href='lib/alertify/css/alertify.css' rel='stylesheet'>
<link href='lib/alertify/css/themes/default.css' rel='stylesheet'>
<script src='lib/alertify/js/alertify.min.js'></script>

<?
if($theme=='skyblue' || $theme==''){
  $fileImg='center_tile_skyblue.gif';
}else if($theme=='red'){
  $fileImg='gradient_4.gif';
}else{
  $fileImg='gradient_0.gif';
}

echo "
<div style=\"position:sticky;top:0px;background:#275370; z-index:9999;\">
<div class='menulinebox' style='margin-top:2.5px;margin-left:10px;cursor:pointer;padding:2px;'><a href='http://owl-plantation.com' target='new'><img src='images/logo.png' style='width:25px;'></a>
</div>";
$mainmenuHTML ="<div class='menulinebox'>
<ul id=\"qm0\" class=\"qmmc\">

<li><a id=\"menu_home\" class=\"qmparent\" href=\"javascript:do_load('master','home');\">HOME</a></li>
";//style=\"\"

//get menu for user by auth type or level

if($_SESSION['security']=='off')
{
	$ssq='';
}
else if($_SESSION['access_type']=='detail')
{
	$ssq=" and id in (".$_SESSION['allpriv'].")";
}
else
{
    $ssq=" and access_level >=".$_SESSION['standard']['access_level'];
}

if($_SESSION['language']=='EN'){
    $cell="id, type, class, caption2 as caption, action, access_level, parent, urut, hide, lastupdate, lastuser";
}
else if($_SESSION['language']=='KH'){
    $cell="id, type, class, caption3 as caption, action, access_level, parent, urut, hide, lastupdate, lastuser";
}
else{
     $cell="id, type, class, caption as caption, action, access_level, parent, urut, hide, lastupdate, lastuser";
}
 

$arrow_location="<i class=\"fa fa-caret-right arrow\" aria-hidden=\"true\"></i>";
$str_m1=$owlPDO->query("select ".$cell." from ".$dbname.".menu  where type='master' ".$ssq."  and hide=0 order by urut");
$str_m1->setFetchMode(PDO::FETCH_OBJ);		 
while($bar_m1=$str_m1->fetch())
{
        $master_id=$bar_m1->id;
        $mainmenuHTML .= "<li><a id='menu_".$bar_m1->id."' parentid='".$bar_m1->parent."' class=\"qmparent\" href=\"javascript:void(0)\">".strtoupper($bar_m1->caption)."</a>";
        //=======================================================	
 
                $str_m2=$owlPDO->query("select ".$cell." from ".$dbname.".menu where parent=".$master_id."  ".$ssq." and hide=0 order by urut");
                $str_m2->setFetchMode(PDO::FETCH_OBJ);
                $count = owlBaris($str_m2);
                if($count>0)
                {	
                        $mainmenuHTML .= "<ul class=\"qm_bodychild\">";
                        while($bar_m2=$str_m2->fetch())
                        {
                                $master_m2=$bar_m2->id;
                                if($bar_m2->class=='devider')
                                  $mainmenuHTML .= "<li><span class=\"qmdivider qmdividerx\" ></span></li>";
                                else if($bar_m2->class=='title')  
                                  $mainmenuHTML .= "<li><span class=\"qmtitle\" >".$bar_m2->caption."</span></li>";
                            else 
                                        {

                                                if($bar_m2->type=='parent')
                                                {				
                                                 $mainmenuHTML .=  "<li><a id='menu_".$bar_m2->id."' parentid='".$bar_m2->parent."' class=\"qmparent\" href=\"javascript:void(0);\"><i class=\"fa fa-file\" ></i> ".$bar_m2->caption."  ".$arrow_location."</a>";	
                                                     //===============================================
                                                         $str_m3=$owlPDO->query("select ".$cell." from ".$dbname.".menu
                                                                 where parent=".$master_m2."  ".$ssq."
                                                                          and hide=0 order by urut");			 	 
                                                        $str_m3->setFetchMode(PDO::FETCH_OBJ);
                                                        $count = owlBaris($str_m3);
                                                        if($count>0)
                                                        {	
                                                                $mainmenuHTML .= "<ul class=\"qm_bodychild\">";
                                                                while($bar_m3=$str_m3->fetch())
                                                                {
                                                                        $master_m3=$bar_m3->id;
                                                                        if($bar_m3->class=='devider')
                                                                          $mainmenuHTML .= "<li><span class=\"qmdivider qmdividerx\" ></span></li>";
                                                                        else if($bar_m3->class=='title')  
                                                                          $mainmenuHTML .= "<li><span class=\"qmtitle\" >".$bar_m3->caption."</span></li>";
                                                                    else 
                                                                                {

                                                                                        if($bar_m3->type=='parent')
                                                                                        {
                                                                                        $mainmenuHTML .=  "<li><a id='menu_".$bar_m3->id."' parentid='".$bar_m3->parent."' class=\"qmparent\" href=\"javascript:void(0);\"><i class=\"fa fa-file\" ></i> ".$bar_m3->caption."  ".$arrow_location."</a>";	
                                                                                             //===============================================
                                                                                                 $str_m4=$owlPDO->query("select ".$cell." from ".$dbname.".menu
                                                                                                         where parent=".$master_m3."  ".$ssq."
                                                                                                                  and hide=0 order by urut");
                                                                                                $str_m4->setFetchMode(PDO::FETCH_OBJ);
                                                                                                $count = owlBaris($str_m4);
                                                                                                if($count>0)
                                                                                                {	
                                                                                                        $mainmenuHTML .= "<ul class=\"qm_bodychild\">";
                                                                                                        while($bar_m4=$str_m4->fetch())
                                                                                                        {
                                                                                                                $master_m4=$bar_m4->id;
                                                                                                                if($bar_m4->class=='devider')
                                                                                                                  $mainmenuHTML .= "<li><span class=\"qmdivider qmdividerx\" ></span></li>";
                                                                                                                else if($bar_m4->class=='title')  
                                                                                                                  $mainmenuHTML .= "<li><span class=\"qmtitle\" >".$bar_m4->caption."</span></li>";
                                                                                                            else 
                                                                                                                  {							  	
                                                                                                                        if($bar_m4->type=='parent')
                                                                                                                        {
                                                                                                                        $mainmenuHTML .=  "<li><a id='menu_".$bar_m4->id."' parentid='".$bar_m4->parent."' class=\"qmparent\" href=\"javascript:void(0);\"><i class=\"fa fa-file\" ></i> ".$bar_m4->caption."  ".$arrow_location."</a>";	
                                                                                                                             //===============================================
                                                                                                                                 $str_m5=$owlPDO->query("select ".$cell." from ".$dbname.".menu
                                                                                                                                         where parent=".$master_m4."  ".$ssq."
                                                                                                                                                  and hide=0 order by urut");
                                                                                                                                $str_m5->setFetchMode(PDO::FETCH_OBJ);
                                                                                                                                $count = owlBaris($str_m5);
                                                                                                                                if($count>0)
                                                                                                                                {	
                                                                                                                                        $mainmenuHTML .= "<ul class=\"qm_bodychild\">";
                                                                                                                                        while($bar_m5=$str_m5->fetch())
                                                                                                                                        {
                                                                                                                                                $master_m5=$bar_m5->id;
                                                                                                                                                if($bar_m5->class=='devider')
                                                                                                                                                  $mainmenuHTML .= "<li><span class=\"qmdivider qmdividerx\" ></span></li>";
                                                                                                                                                else if($bar_m5->class=='title')  
                                                                                                                                                  $mainmenuHTML .= "<li><span class=\"qmtitle\" >".$bar_m5->caption."</span></li>";
                                                                                                                                            else 
                                                                                                                                                  {
                                                                                                                                                                if($bar_m5->type=='parent')
                                                                                                                                                                {
                                                                                                                                                                $mainmenuHTML .=  "<li><a id='menu_".$bar_m5->id."' parentid='".$bar_m5->parent."' class=\"qmparent\" href=\"javascript:void(0);\"><i class=\"fa fa-file\" ></i> ".$bar_m5->caption."  ".$arrow_location."</a>";	
                                                                                                                                                                     //===============================================
                                                                                                                                                                         $str_m6=$owlPDO->query("select ".$cell." from ".$dbname.".menu
                                                                                                                                                                                 where parent=".$master_m5."   ".$ssq."
                                                                                                                                                                                          and hide=0 order by urut");
                                                                                                                                                                        $str_m6->setFetchMode(PDO::FETCH_OBJ);
                                                                                                                                                                        $count = owlBaris($str_m6);
                                                                                                                                                                        if($count>0)
                                                                                                                                                                        {	
                                                                                                                                                                                $mainmenuHTML .= "<ul class=\"qm_bodychild\">";
                                                                                                                                                                                while($bar_m6=$str_m6->fetch())
                                                                                                                                                                                {
                                                                                                                                                                                        $master_m6=$bar_m6->id;
                                                                                                                                                                                        if($bar_m6->class=='devider')
                                                                                                                                                                                          $mainmenuHTML .= "<li><span class=\"qmdivider qmdividerx\" ></span></li>";
                                                                                                                                                                                        else if($bar_m6->class=='title')  
                                                                                                                                                                                          $mainmenuHTML .= "<li><span class=\"qmtitle\" >".$bar_m6->caption."</span></li>";
                                                                                                                                                                                    else
                                                                                                                                                                                          {
                                                                                                                                                                                                if($bar_m6->type=='parent')
                                                                                                                                                                                                {
                                                                                                                                                                                                $mainmenuHTML .=  "<li><a id='menu_".$bar_m6->id."' parentid='".$bar_m6->parent."' class=\"qmparent\" href=\"javascript:void(0);\"><i class=\"fa fa-file\" ></i> ".$bar_m6->caption."  ".$arrow_location."</a>";	
                                                                                                                                                                                                     //===============================================
                                                                                                                                                                                                         $str_m7=$owlPDO->query("select ".$cell." from ".$dbname.".menu
                                                                                                                                                                                                                 where parent=".$master_m6."  ".$ssq."
                                                                                                                                                                                                                          and hide=0 order by urut");
                                                                                                                                                                                                        $str_m7->setFetchMode(PDO::FETCH_OBJ);
                                                                                                                                                                                                        $count = owlBaris($str_m7);
                                                                                                                                                                                                        if($count>0)
                                                                                                                                                                                                        {	
                                                                                                                                                                                                                $mainmenuHTML .= "<ul class=\"qm_bodychild\">";
                                                                                                                                                                                                                while($bar_m7=$str_m7->fetch())
                                                                                                                                                                                                                {
                                                                                                                                                                                                                        $master_m7=$bar_m7->id;
                                                                                                                                                                                                                        if($bar_m7->class=='devider')
                                                                                                                                                                                                                          $mainmenuHTML .= "<li><span class=\"qmdivider qmdividerx\" ></span></li>";
                                                                                                                                                                                                                        else if($bar_m7->class=='title')  
                                                                                                                                                                                                                          $mainmenuHTML .= "<li><span class=\"qmtitle\" >".$bar_m7->caption."</span></li>";
                                                                                                                                                                                                                    else
                                                                                                                                                                                                                    {
                                                                                                                                                                                                                          $mainmenuHTML .=  "<li><a id='menu_".$bar_m7->id."' parentid='".$bar_m7->parent."' href=\"javascript:do_load('".$bar_m7->action."','".$bar_m7->id."')\"><i class=\"fa fa-file\" ></i> ".$bar_m7->caption."</a></li>"; 
                                                                                                                                                                                                                          $menu_jump['id'][]=$bar_m7->id;
                                                                                                                                                                                                                          $menu_jump['action'][]=$bar_m7->action;
                                                                                                                                                                                                                          if($_SESSION['language']=='EN'){
                                                                                                                                                                                                                            $menu_jump['caption'][]=$bar_m7->caption2;
                                                                                                                                                                                                                          }else{
                                                                                                                                                                                                                            $menu_jump['caption'][]=$bar_m7->caption;
                                                                                                                                                                                                                          }
                                                                                                                                                                                                                    }
                                                                                                                                                                                                                }
                                                                                                                                                                                                                $mainmenuHTML .= "</ul>";									
                                                                                                                                                                                                        }
                                                                                                                                                                                                         //===============================================
                                                                                                                                                                                                $mainmenuHTML .=  "</li>";
                                                                                                                                                                                                }
                                                                                                                                                                                                else
                                                                                                                                                                                                {
                                                                                                                                                                                                 $mainmenuHTML .=  "<li><a id='menu_".$bar_m6->id."' parentid='".$bar_m6->parent."' href=\"javascript:do_load('".$bar_m6->action."','".$bar_m6->id."')\"><i class=\"fa fa-file\" ></i> ".$bar_m6->caption."</a></li>";	
                                                                                                                                                                                                  $menu_jump['id'][]=$bar_m6->id;
                                                                                                                                                                                                  $menu_jump['action'][]=$bar_m6->action;
                                                                                                                                                                                                  if($_SESSION['language']=='EN'){
                                                                                                                                                                                                    $menu_jump['caption'][]=$bar_m6->caption2;
                                                                                                                                                                                                  }else{
                                                                                                                                                                                                    $menu_jump['caption'][]=$bar_m6->caption;
                                                                                                                                                                                                  }
                                                                                                                                                                                                }
                                                                                                                                                                                          } 
                                                                                                                                                                                }
                                                                                                                                                                                $mainmenuHTML .= "</ul>";									
                                                                                                                                                                        }                                                                                                                                                                                                                                                                                                                                                  
                                                                                                                                                                         //===============================================
                                                                                                                                                                $mainmenuHTML .=  "</li>";
                                                                                                                                                                }
                                                                                                                                                                else
                                                                                                                                                                {
                                                                                                                                                                   $mainmenuHTML .=  "<li><a id='menu_".$bar_m5->id."' parentid='".$bar_m5->parent."' href=\"javascript:do_load('".$bar_m5->action."','".$bar_m5->id."')\"><i class=\"fa fa-file\" ></i> ".$bar_m5->caption."</a></li>";	
                                                                                                                                                                   $menu_jump['id'][]=$bar_m5->id;
                                                                                                                                                                   $menu_jump['action'][]=$bar_m5->action;
                                                                                                                                                                    if($_SESSION['language']=='EN'){
                                                                                                                                                                      $menu_jump['caption'][]=$bar_m5->caption2;
                                                                                                                                                                    }else{
                                                                                                                                                                      $menu_jump['caption'][]=$bar_m5->caption;
                                                                                                                                                                    }
                                                                                                                                                                }																			  	
                                                                                                                                                  }

                                                                                                                                        }
                                                                                                                                        $mainmenuHTML .= "</ul>";									
                                                                                                                                }
                                                                                                                                 //===============================================
                                                                                                                        $mainmenuHTML .=  "</li>";
                                                                                                                        }
                                                                                                                        else
                                                                                                                        {
                                                                                                                         $mainmenuHTML .=  "<li><a id='menu_".$bar_m4->id."' parentid='".$bar_m4->parent."' href=\"javascript:do_load('".$bar_m4->action."','".$bar_m4->id."')\"><i class=\"fa fa-file\" ></i> ".$bar_m4->caption."</a></li>";	
                                                                                                                          $menu_jump['id'][]=$bar_m4->id;
                                                                                                                          $menu_jump['action'][]=$bar_m4->action;
                                                                                                                          if($_SESSION['language']=='EN'){
                                                                                                                            $menu_jump['caption'][]=$bar_m4->caption2;
                                                                                                                          }else{
                                                                                                                            $menu_jump['caption'][]=$bar_m4->caption;
                                                                                                                          } 
                                                                                                                        }

                                                                                                                  }

                                                                                                        }
                                                                                                        $mainmenuHTML .= "</ul>";									
                                                                                                }
                                                                                                 //===============================================
                                                                                        $mainmenuHTML .=  "</li>";
                                                                                        }
                                                                                        else
                                                                                        {
                                                                                         $mainmenuHTML .=  "<li><a id='menu_".$bar_m3->id."' parentid='".$bar_m3->parent."' href=\"javascript:do_load('".$bar_m3->action."','".$bar_m3->id."')\"><i class=\"fa fa-file\" ></i> ".$bar_m3->caption."</a></li>";	
                                                                                          $menu_jump['id'][]=$bar_m3->id;
                                                                                          $menu_jump['action'][]=$bar_m3->action;
                                                                                          if($_SESSION['language']=='EN'){
                                                                                            $menu_jump['caption'][]=$bar_m3->caption2;
                                                                                          }else{
                                                                                            $menu_jump['caption'][]=$bar_m3->caption;
                                                                                          }
                                                                                        }

                                                                                }
                                                                }
                                                                $mainmenuHTML .= "</ul>";									
                                                        }
                                                         //===============================================
                                                $mainmenuHTML .=  "</li>";

                                                }
                                                else
                                                {
                                                 $mainmenuHTML .=  "<li><a id='menu_".$bar_m2->id."' parentid='".$bar_m2->parent."' href=\"javascript:do_load('".$bar_m2->action."','".$bar_m2->id."')\"><i class=\"fa fa-file\" ></i> ".$bar_m2->caption."</a></li>";	
                                                 $menu_jump['id'][]=$bar_m2->id;
                                                 $menu_jump['action'][]=$bar_m2->action;
                                                  if($_SESSION['language']=='EN'){
                                                    $menu_jump['caption'][]=$bar_m2->caption2;
                                                  }else{
                                                    $menu_jump['caption'][]=$bar_m2->caption;
                                                  } 
                                                }	

                                        }					
                        }
                        $mainmenuHTML .= "</ul></li>";
                }
        //=========================================================	

       // $mainmenuHTML .= "<li><span class=\"qmdivider qmdividery\"></span></li>";
}		 		
$mainmenuHTML .= "
</ul>
<!-- Ending Page Content [menu nests within] -->
</div>";

$str = "select * from ".$dbname.".sdm_5jabatan where kodejabatan  ='".$_SESSION['empl']['jabatan']."'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_OBJ);
while($bar=$res->fetch()){
	$namajabatan[$bar->kodejabatan]=$bar->namajabatan;
}
echo"
<style>
	.menulinebox li{
		list-style: none;
		float: right;
		padding-right: 10px;
		padding-left: 10px;
	}
	li.useridentifyandlogout{cursor:pointer;border-left:solid 1px #60809e;}
	li.useridentifyandlogout:hover>.useridentifyandlogoutBody{display:unset !important;}
	.menulinebox .useridentifyandlogoutBody{display:none;
	position: absolute;
    top: 30px;
    right: 0px;
	z-index: 9999;
		padding-top:5px;
	}
	.useridentifyandlogoutBody .userboth{
		padding: 5px 10px;background: #e8f4f4;border-radius: 0px 0px 5px 5px;box-shadow: 0px 0px 3px rgb(0 0 0 / 50%);
	}
	
	li.useremail{cursor:pointer;border-left:solid 1px #60809e;padding-top:5px;padding-bottom:5px}
	li.useremail:hover>.useremailBody{display:unset !important;}
	.menulinebox .useremailBody{display:none;
	position: absolute;
    top: 30px;
    right: 45px;
	z-index: 9999;
		padding-top:5px;
	}
	.useremailBody .userboth{
		padding: 5px 10px;background: #e8f4f4;border-radius: 0px 0px 5px 5px;box-shadow: 0px 0px 3px rgb(0 0 0 / 50%);
		width:250px;
		max-height:300px;
		overflow:hidden;
		overflow-y:auto;
	}
	
	.notificationx {
	  color: white;
	  text-decoration: none;
	  position: relative;
	  display: inline-block;
	  border-radius: 2px;
	}
	.notificationx .badgex {
	  position: absolute;
	  top: -5px;
	  right: 7px;
	  padding: 2px 5px;
	  border-radius: 50%;
	  background: red;
	  color: white;
	  font-size:8px;
	}
</style>
<ul class='menulinebox' style='float:right;margin-top:2.5px;padding:2px;'>
	<li class='useremail' title='Keluar'>
		<a class='notificationx' onclick='logout()'>
			<i class='fa fa-sign-out' aria-hidden='true' style='color:white;font-size:15px'></i>
		</a>
	</li>
	
	
	<li class='useridentifyandlogout' target='new'><svg viewBox=\"-4.5 -4.5 35 35\" height='24' version='1.1' width='24' xmlns='http://www.w3.org/2000/svg' xmlns:cc='http://creativecommons.org/ns#' xmlns:dc='http://purl.org/dc/elements/1.1/' xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns#'><g transform='translate(0 -1028.4)'><path d='m8.4062 1041.1c-2.8856 1.3-4.9781 4-5.3437 7.3 0 1.1 0.8329 2 1.9375 2h14c1.105 0 1.938-0.9 1.938-2-0.366-3.3-2.459-6-5.344-7.3-0.649 1.3-2.011 2.3-3.594 2.3s-2.9453-1-3.5938-2.3z' fill='#FFFFFF'/><path d='m17 4a5 5 0 1 1 -10 0 5 5 0 1 1 10 0z' fill='#FFFFFF' transform='translate(0 1031.4)'/><path d='m12 11c-1.277 0-2.4943 0.269-3.5938 0.75-2.8856 1.262-4.9781 3.997-5.3437 7.25 0 1.105 0.8329 2 1.9375 2h14c1.105 0 1.938-0.895 1.938-2-0.366-3.253-2.459-5.988-5.344-7.25-1.1-0.481-2.317-0.75-3.594-0.75z' fill='#FFFFFF' transform='translate(0 1028.4)'/></g></svg>
		<div class='useridentifyandlogoutBody' >
			<div class='userboth'>";
				
				$nmrole = makeOption($dbname,'admin_rolemenuht','id,name');
				$dtrole=array();
				$w = "select * from ".$dbname.".auth_role where namauser  ='".$_SESSION['standard']['username']."'";
				$res = fetchdata($w);
				$no=0;
				foreach($res as $bar){
					$no++;
					$rolekary[$no.". ".$nmrole[$bar['idrole']]]=$no.". ".$nmrole[$bar['idrole']];
					$dtrole[$bar['idrole']]=$bar['idrole'];
				}
				$tab.="<center>";
				$w = "select * from ".$dbname.".user where namauser  ='".$_SESSION['standard']['username']."'";
				$res = fetchdata($w);
				$tab.="<label><b><i>Informasi</i></b></label><br/>";
				
				if(getKary($res[0]['karyawanid'],'photo')!=''){
					$namafile= "photokaryawan/".getKary($res[0]['karyawanid'],'photo');
					if (file_exists($namafile)) {
						$tab.="<br>";
						$tab.="
							<div>
								<img style='height:150px;width:150px;border: 2px solid rgb(255, 255, 255);border-radius: 10px;' src='".$namafile."'>
							</div>";
					}else{
						$tab.="<br>";
						$imgpic="images/userfemale.png";
						if($_SESSION['empl']['sex']=='L'){
							$imgpic="images/usermale.png";							
						}
						$tab.="
							<div>
								<img style='height:150px;width:auto;' src='".$imgpic."'>
							</div>";
					}
				}else{
					$tab.="<br>";
					$imgpic="images/userfemale.png";
					if($_SESSION['empl']['sex']=='L'){
						$imgpic="images/usermale.png";							
					}
					$tab.="
						<div>
							<img style='height:150px;width:auto;' src='".$imgpic."'>
						</div>";
				}
				$tab.="</center>";
				$tab.="
					<table border=0 cellpadding=3 cellspacing=1 style=font-size:10px;text-align:left;width:100%;>
					<tr>
						<td colspan=3 align=center style=background-color:#c6dff2d9;><b><i>Data Pengguna</i></b></td>
					</tr>
					<tr>
						<td>User</td><td>:</td><td>".$_SESSION['standard']['username']."</td>
					</tr>
					<tr>
						<td>Lokasi</td><td>:</td><td>".$res[0]['kodeorg']." - ".getNamaOrg($res[0]['kodeorg'])."</td>
					</tr>
					<tr>
						<td>Admin</td><td>:</td><td>".(!$admin?'No':'Yes')."</td>
					</tr>
					<tr>
						<td colspan=3 align=center style=background-color:#c6dff2d9;><b><i>Data Karyawan</i></b></td>
					</tr>
					<tr>
						<td>NIK</td><td>:</td><td>".getKary($res[0]['karyawanid'],'nik')."</td>
					</tr>
					<tr>
						<td>Nama</td><td>:</td><td>".getKary($res[0]['karyawanid'],'namakaryawan')."</td>
					</tr>
					<tr>
						<td>Jabatan</td><td>:</td><td>".getNamaJabatan(getKary($res[0]['karyawanid'],'kodejabatan'))." (".getKary($res[0]['karyawanid'],'kodejabatan').")</td>
					</tr>
					<tr>
						<td>Lokasi</td><td>:</td><td>".getKary($res[0]['karyawanid'],'lokasitugas')." - ".getNamaOrg(getKary($res[0]['karyawanid'],'lokasitugas'))."</td>
					</tr>
					";
					if(getKary($res[0]['karyawanid'],'subbagian')!=''){
						$tab.="<tr>
							<td>Divisi</td><td>:</td><td>".getKary($res[0]['karyawanid'],'subbagian')." - ".getNamaOrg(getKary($res[0]['karyawanid'],'subbagian'))."</td>
						</tr>";
					}
				$tab.="<tr>
						<td>Dept</td><td>:</td><td>".getNamaDept(getKary($res[0]['karyawanid'],'bagian'))." (".getKary($res[0]['karyawanid'],'bagian').")</td>
					</tr>
					<tr>
						<td valign=top>Role</td><td valign=top>:</td>
						<td valign=top>";
						foreach($dtrole as $role){
							$n++;
							$tab.="<div onclick=setMapUserMenuDet('".$role."') style=color:blue;cursor:pointer;>".$n.". ".$nmrole[$role]."</div>";
						}
					$tab.="</td>";
					$tab.="</tr>";
					
					$tanggalabsen=date('Y-m-d');
					
					$w = "select scan_date from ".$dbname.".att_pegawai a left join ".$dbname.".att_log b on a.sn=b.sn and a.pin=b.pin where karyawan  ='".$res[0]['karyawanid']."' and scan_date like '".$tanggalabsen."%' order by scan_date asc limit 1";
					$res = fetchdata($w);
					if(count($res)>0){
						foreach($res as $val){
							$n = "select masuk, toleransi from ".$dbname.".sdm_5shiftanggota a left join ".$dbname.".sdm_5shift b on a.idshift=b.id where karyawanid ='".$res[0]['karyawanid']."' order by tanggal asc LIMIT 1";
							$s = fetchdata($n);
							$color="";
							if(!empty($s)){						
								$masuk = $s[0]['masuk'];
								$toleransi = $s[0]['toleransi'];
								$date = tanggalnormal($tanggalabsen)." ".$masuk.":00";
								$maxmasuk = tambahmenitshift($date,$toleransi);
								if(strtotime($val['scan_date'])>strtotime($maxmasuk)){
									$diff      = (strtotime($maxmasuk)-strtotime($val['scan_date']));
									$hari      = floor($diff/(60*60*24));
									$jam       = floor(($diff-($hari*(60*60*24)))/ (60 * 60));
									$menit     = floor(($diff-(($hari*(60*60*24))+($jam*(60*60))))/60);
									$color="style=color:red; title='Terlambat'";
								}
								
								$tab.="<tr>";
								$tab.="<td valign=top>Jam Masuk</td><td valign=top>:</td>";
								$tab.="<td valign=top>".substr($maxmasuk,-8)."</td>";
								$tab.="</tr>";
							}
							$tab.="<tr><td valign=top>Jam Finger</td><td valign=top>:</td>";
							$tab.="<td valign=top ".$color.">".substr($val['scan_date'],-8)."</td>";
							$tab.="</tr>";
						}
					}
					
				$tab.="<tr>
						<td colspan=3 align=center style=background-color:#c6dff2d9;><b><i>Account Periode</i></b></td>
					</tr>";
				$str = "select periode from ".$dbname.".setup_periodeakuntansi where kodeorg ='".$_SESSION['empl']['lokasitugas']."' and tutupbuku='0' order by periode asc limit 1";
				$res = fetchdata($str);	
				$tab.="<tr>
						<td>Aktif</td><td>:</td><td>".$res[0]['periode']."</td>
					</tr>";	
					
				$tab.="</table>";
			echo $tab;
		
		
		echo"</div>
		  </li>
		  
		<li class='useremail' target='new'>
			<a class='notificationx' onclick='shownotifx()'>
			<i class='fa fa-bell' aria-hidden='true' style='color:white;font-size:15px'></i>
			<span class='badgex' id='countnotifx' style='display:none'>0</span>
			</a>
			<div class='useremailBody'>
				<div class='userboth' id='notifuserboth'></div>
			</div>
		</li>
		  
		  <li> <input type=select class=myinputtext oninput=jump(this.value,event) list=jump style='width:75px;background-color:#9EAEC7' title='Shortcut to specific menu'></input>
			 <span class=\"qmdivider qmdividery\"></span>
			  <datalist id=jump>";
			  if(count($menu_jump['id'])>0)
			  {  
			   foreach($menu_jump['id'] as $key=>$val){
				  echo"<option id='".$val."' value='".$val."' action='".$menu_jump['action'][$key]."'>".$menu_jump['caption'][$key]."</option>";
				}
			  }
			echo"   
			  </datalist>
			  <select id=jumpList style='display:none;'>";
			  if(count($menu_jump['id'])>0)
			  {  
			   foreach($menu_jump['id'] as $key=>$val){
				  echo"<option id='".$val."' value='".$val."' action='".$menu_jump['action'][$key]."'>".$menu_jump['caption'][$key]."</option>";
				}
			  }
			echo"   
			  </select></li>
</ul>";
echo $mainmenuHTML;
echo "</div>
<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click (options: 'all' * 'all-always-open' * 'main' * 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->
<script type=\"text/javascript\">qm_create(0,false,0,500,false,false,false,false,false);</script>
";
?>

<script>
jumlahnotif('x');
</script>

<div id='progress' class='progress' style='display:none;'>
<div class="progress-body">
Please wait.....! <br>
<img src='images/progress.gif?v=3' style='width:50px;'>
</div>
</div>

<!--
<div id='progress' style='display:none;border:orange solid 1px;width:150px;position:fixed;right:20px;top:65px;color:#ff0000;font-family:Tahoma;font-size:13px;font-weight:bolder;text-align:center;background-color:#FFFFFF;z-index:10000;'>
Please wait.....! <br>
<img src='images/progress.gif'>
</div>

-->

   

   
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    




    
    
    
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <? if(MD5($_SESSION['org']['holding'])!='23e4007fc62180a661d9d1efb7320413'){session_destroy();exit();} ?>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          














































































                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																														
			
