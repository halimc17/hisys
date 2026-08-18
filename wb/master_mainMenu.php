<?php
require_once('config/connection.php');
// echo"<div style='position:fixed;top:5px;right:35px'>".$_SESSION['standard']['username']."</div>";

if($theme=='skyblue' || $theme==''){
  $fileImg='center_tile_skyblue.gif';
}else if($theme=='red'){
  $fileImg='gradient_4.gif';
}else{
  $fileImg='gradient_0.gif';
}

echo "
<div style=\"background-image:url(images/menu/".$fileImg.");position:sticky;top:0;z-index:100\">
	<table cellpadding=0 cellspacing=0 style=\"width:100%;top:0px;\">
	<tr><td> 
		</td><td style=\"width:100%;\">
<ul id=\"qm0\" class=\"qmmc\">";//style=\"\"

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

// if($_SESSION['language']=='EN'){
    // $cell="id, type, class, caption2 as caption, action, access_level, parent, urut, hide, lastupdate, lastuser";
// }
// else if($_SESSION['language']=='KH'){
    // $cell="id, type, class, caption3 as caption, action, access_level, parent, urut, hide, lastupdate, lastuser";
// }
// else{
     $cell="id, type, class, caption as caption, action, access_level, parent, urut, hide, lastupdate, lastuser";
// }


$arrow_location="<img src='images/menu/arrow_4.gif' class=arrow>";
$str_m1=$owlPDO->query("select ".$cell." from ".$dbname.".menu  where type='master' ".$ssq."  and hide=0 order by urut");
$str_m1->setFetchMode(PDO::FETCH_OBJ);	
echo"<li><a id='menu_home' class=\"qmparent\" href=\"javascript:do_load('master','home')\">HOME</a></li><li><span class=\"qmdivider qmdividery\"></span></li>";	 
while($bar_m1=$str_m1->fetch())
{
        $master_id=$bar_m1->id;
        echo"<li><a id='menu_".$bar_m1->id."' parentid='".$bar_m1->parent."' class=\"qmparent\" href=\"javascript:void(0)\">".strtoupper($bar_m1->caption)."</a>";
        //=======================================================	
 
                $str_m2=$owlPDO->query("select ".$cell." from ".$dbname.".menu where parent=".$master_id."  ".$ssq." and hide=0 order by urut");
                $str_m2->setFetchMode(PDO::FETCH_OBJ);
                $count = owlBaris($str_m2);
                if($count>0)
                {	
                        echo"<ul>";
                        while($bar_m2=$str_m2->fetch())
                        {
                                $master_m2=$bar_m2->id;
                                if($bar_m2->class=='devider')
                                  echo"<li><span class=\"qmdivider qmdividerx\" ></span></li>";
                                else if($bar_m2->class=='title')  
                                  echo"<li><span class=\"qmtitle\" >".$bar_m2->caption."</span></li>";
                            else 
                                        {

                                                if($bar_m2->type=='parent')
                                                {				
                                                 echo "<li><a id='menu_".$bar_m2->id."' parentid='".$bar_m2->parent."' class=\"qmparent\" href=\"javascript:void(0);\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m2->caption."  ".$arrow_location."</a>";	
                                                     //===============================================
                                                         $str_m3=$owlPDO->query("select ".$cell." from ".$dbname.".menu
                                                                 where parent=".$master_m2."  ".$ssq."
                                                                          and hide=0 order by urut");			 	 
                                                        $str_m3->setFetchMode(PDO::FETCH_OBJ);
                                                        $count = owlBaris($str_m3);
                                                        if($count>0)
                                                        {	
                                                                echo"<ul>";
                                                                while($bar_m3=$str_m3->fetch())
                                                                {
                                                                        $master_m3=$bar_m3->id;
                                                                        if($bar_m3->class=='devider')
                                                                          echo"<li><span class=\"qmdivider qmdividerx\" ></span></li>";
                                                                        else if($bar_m3->class=='title')  
                                                                          echo"<li><span class=\"qmtitle\" >".$bar_m3->caption."</span></li>";
                                                                    else 
                                                                                {

                                                                                        if($bar_m3->type=='parent')
                                                                                        {
                                                                                        echo "<li><a id='menu_".$bar_m3->id."' parentid='".$bar_m3->parent."' class=\"qmparent\" href=\"javascript:void(0);\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m3->caption."  ".$arrow_location."</a>";	
                                                                                             //===============================================
                                                                                                 $str_m4=$owlPDO->query("select ".$cell." from ".$dbname.".menu
                                                                                                         where parent=".$master_m3."  ".$ssq."
                                                                                                                  and hide=0 order by urut");
                                                                                                $str_m4->setFetchMode(PDO::FETCH_OBJ);
                                                                                                $count = owlBaris($str_m4);
                                                                                                if($count>0)
                                                                                                {	
                                                                                                        echo"<ul>";
                                                                                                        while($bar_m4=$str_m4->fetch())
                                                                                                        {
                                                                                                                $master_m4=$bar_m4->id;
                                                                                                                if($bar_m4->class=='devider')
                                                                                                                  echo"<li><span class=\"qmdivider qmdividerx\" ></span></li>";
                                                                                                                else if($bar_m4->class=='title')  
                                                                                                                  echo"<li><span class=\"qmtitle\" >".$bar_m4->caption."</span></li>";
                                                                                                            else 
                                                                                                                  {							  	
                                                                                                                        if($bar_m4->type=='parent')
                                                                                                                        {
                                                                                                                        echo "<li><a id='menu_".$bar_m4->id."' parentid='".$bar_m4->parent."' class=\"qmparent\" href=\"javascript:void(0);\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m4->caption."  ".$arrow_location."</a>";	
                                                                                                                             //===============================================
                                                                                                                                 $str_m5=$owlPDO->query("select ".$cell." from ".$dbname.".menu
                                                                                                                                         where parent=".$master_m4."  ".$ssq."
                                                                                                                                                  and hide=0 order by urut");
                                                                                                                                $str_m5->setFetchMode(PDO::FETCH_OBJ);
                                                                                                                                $count = owlBaris($str_m5);
                                                                                                                                if($count>0)
                                                                                                                                {	
                                                                                                                                        echo"<ul>";
                                                                                                                                        while($bar_m5=$str_m5->fetch())
                                                                                                                                        {
                                                                                                                                                $master_m5=$bar_m5->id;
                                                                                                                                                if($bar_m5->class=='devider')
                                                                                                                                                  echo"<li><span class=\"qmdivider qmdividerx\" ></span></li>";
                                                                                                                                                else if($bar_m5->class=='title')  
                                                                                                                                                  echo"<li><span class=\"qmtitle\" >".$bar_m5->caption."</span></li>";
                                                                                                                                            else 
                                                                                                                                                  {
                                                                                                                                                                if($bar_m5->type=='parent')
                                                                                                                                                                {
                                                                                                                                                                echo "<li><a id='menu_".$bar_m5->id."' parentid='".$bar_m5->parent."' class=\"qmparent\" href=\"javascript:void(0);\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m5->caption."  ".$arrow_location."</a>";	
                                                                                                                                                                     //===============================================
                                                                                                                                                                         $str_m6=$owlPDO->query("select ".$cell." from ".$dbname.".menu
                                                                                                                                                                                 where parent=".$master_m5."   ".$ssq."
                                                                                                                                                                                          and hide=0 order by urut");
                                                                                                                                                                        $str_m6->setFetchMode(PDO::FETCH_OBJ);
                                                                                                                                                                        $count = owlBaris($str_m6);
                                                                                                                                                                        if($count>0)
                                                                                                                                                                        {	
                                                                                                                                                                                echo"<ul>";
                                                                                                                                                                                while($bar_m6=$str_m6->fetch())
                                                                                                                                                                                {
                                                                                                                                                                                        $master_m6=$bar_m6->id;
                                                                                                                                                                                        if($bar_m6->class=='devider')
                                                                                                                                                                                          echo"<li><span class=\"qmdivider qmdividerx\" ></span></li>";
                                                                                                                                                                                        else if($bar_m6->class=='title')  
                                                                                                                                                                                          echo"<li><span class=\"qmtitle\" >".$bar_m6->caption."</span></li>";
                                                                                                                                                                                    else
                                                                                                                                                                                          {
                                                                                                                                                                                                if($bar_m6->type=='parent')
                                                                                                                                                                                                {
                                                                                                                                                                                                echo "<li><a id='menu_".$bar_m6->id."' parentid='".$bar_m6->parent."' class=\"qmparent\" href=\"javascript:void(0);\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m6->caption."  ".$arrow_location."</a>";	
                                                                                                                                                                                                     //===============================================
                                                                                                                                                                                                         $str_m7=$owlPDO->query("select ".$cell." from ".$dbname.".menu
                                                                                                                                                                                                                 where parent=".$master_m6."  ".$ssq."
                                                                                                                                                                                                                          and hide=0 order by urut");
                                                                                                                                                                                                        $str_m7->setFetchMode(PDO::FETCH_OBJ);
                                                                                                                                                                                                        $count = owlBaris($str_m7);
                                                                                                                                                                                                        if($count>0)
                                                                                                                                                                                                        {	
                                                                                                                                                                                                                echo"<ul>";
                                                                                                                                                                                                                while($bar_m7=$str_m7->fetch())
                                                                                                                                                                                                                {
                                                                                                                                                                                                                        $master_m7=$bar_m7->id;
                                                                                                                                                                                                                        if($bar_m7->class=='devider')
                                                                                                                                                                                                                          echo"<li><span class=\"qmdivider qmdividerx\" ></span></li>";
                                                                                                                                                                                                                        else if($bar_m7->class=='title')  
                                                                                                                                                                                                                          echo"<li><span class=\"qmtitle\" >".$bar_m7->caption."</span></li>";
                                                                                                                                                                                                                    else
                                                                                                                                                                                                                    {
                                                                                                                                                                                                                          echo "<li><a id='menu_".$bar_m7->id."' parentid='".$bar_m7->parent."' href=\"javascript:do_load('".$bar_m7->action."','".$bar_m7->id."')\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m7->caption."</a></li>"; 
                                                                                                                                                                                                                          $menu_jump['id'][]=$bar_m7->id;
                                                                                                                                                                                                                          $menu_jump['action'][]=$bar_m7->action;
                                                                                                                                                                                                                          if($_SESSION['language']=='EN'){
                                                                                                                                                                                                                            $menu_jump['caption'][]=$bar_m7->caption2;
                                                                                                                                                                                                                          }else{
                                                                                                                                                                                                                            $menu_jump['caption'][]=$bar_m7->caption;
                                                                                                                                                                                                                          }
                                                                                                                                                                                                                    }
                                                                                                                                                                                                                }
                                                                                                                                                                                                                echo"</ul>";									
                                                                                                                                                                                                        }
                                                                                                                                                                                                         //===============================================
                                                                                                                                                                                                echo "</li>";
                                                                                                                                                                                                }
                                                                                                                                                                                                else
                                                                                                                                                                                                {
                                                                                                                                                                                                 echo "<li><a id='menu_".$bar_m6->id."' parentid='".$bar_m6->parent."' href=\"javascript:do_load('".$bar_m6->action."','".$bar_m6->id."')\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m6->caption."</a></li>";	
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
                                                                                                                                                                                echo"</ul>";									
                                                                                                                                                                        }                                                                                                                                                                                                                                                                                                                                                  
                                                                                                                                                                         //===============================================
                                                                                                                                                                echo "</li>";
                                                                                                                                                                }
                                                                                                                                                                else
                                                                                                                                                                {
                                                                                                                                                                   echo "<li><a id='menu_".$bar_m5->id."' parentid='".$bar_m5->parent."' href=\"javascript:do_load('".$bar_m5->action."','".$bar_m5->id."')\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m5->caption."</a></li>";	
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
                                                                                                                                        echo"</ul>";									
                                                                                                                                }
                                                                                                                                 //===============================================
                                                                                                                        echo "</li>";
                                                                                                                        }
                                                                                                                        else
                                                                                                                        {
                                                                                                                         echo "<li><a id='menu_".$bar_m4->id."' parentid='".$bar_m4->parent."' href=\"javascript:do_load('".$bar_m4->action."','".$bar_m4->id."')\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m4->caption."</a></li>";	
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
                                                                                                        echo"</ul>";									
                                                                                                }
                                                                                                 //===============================================
                                                                                        echo "</li>";
                                                                                        }
                                                                                        else
                                                                                        {
                                                                                         echo "<li><a id='menu_".$bar_m3->id."' parentid='".$bar_m3->parent."' href=\"javascript:do_load('".$bar_m3->action."','".$bar_m3->id."')\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m3->caption."</a></li>";	
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
                                                                echo"</ul>";									
                                                        }
                                                         //===============================================
                                                echo "</li>";

                                                }
                                                else
                                                {
                                                 echo "<li><a id='menu_".$bar_m2->id."' parentid='".$bar_m2->parent."' href=\"javascript:do_load('".$bar_m2->action."','".$bar_m2->id."')\"><img src=images/menu/star.png style='border:0px;vertical-align:middle;height:11px'> ".$bar_m2->caption."</a></li>";	
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
                        echo"</ul></li>";
                }
        //=========================================================	

        echo"<li><span class=\"qmdivider qmdividery\"></span></li>";
}		 		
echo"
<li class=\"qmclear\">&nbsp;</li></ul>
<!-- Ending Page Content [menu nests within] -->
</td>
<td>
<li class='fa fa-sign-out' onclick='logout()' title='Logout' style='color:white;cursor:pointer;font-size:12px;'><span style='padding-left:5px;font-family: Arial,Fixedsys,Tahoma;font-size:10px'>LOGOUT</span></li>
</td>
<td>&nbsp;
&nbsp;&nbsp;</td>
</tr>
</table>
</div>
<!-- Create Menu Settings: (Menu ID, Is Vertical, Show Timer, Hide Timer, On Click (options: 'all' * 'all-always-open' * 'main' * 'lev2'), Right to Left, Horizontal Subs, Flush Left, Flush Top) -->
<script type=\"text/javascript\">qm_create(0,false,0,500,false,false,false,false,false);</script>
";
?>

<div id='progress' class='progress' style='display:none;'>
<div class="progress-body">
Please wait.....! <br>
<img src='images/progress.gif?v=2'>
</div>
</div>



   
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    




    
    
    
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    <?//if(MD5($_SESSION['org']['holding'])!='6716abf250eed41a0e74d04b91706c3a'){session_destroy();exit();}?>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          














































































                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        
																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																																														
			
