<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript1.2 src='js/main_privilage_by_table.js?v=1'></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper('Privileges by table').'</span>');
echo OPEN_THEME($_SESSION['lang']['menusettings'].":");
//=================================================================================
//menuSettings

//==================================================================================================================================================================
 echo"<div id=menuOrderContainer style='position:relative;display:block'>
	 <hr><b>Assign a menu for users:</b>:
	";
echo"<ul>
     <div id=ordergroup0>";
$str=$owlPDO->query("select * from ".$dbname.".menu  where type='master' order by urut");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch())
{
	echo "<li class=mmgr><img title=expand class=arrow src='images/foldc_.png' height=17px   onclick=show_sub('orderchild".$bar->id."',this);>
	<a class=lab  title='Click to show this submenu order editor' id=orderlab".$bar->id." onclick=showEditor('".$bar->id."','true',event)>".$bar->caption."</a>";
//=========================================================
	     $str1=$owlPDO->query("select * from ".$dbname.".menu   where parent=".$bar->id." order by urut");
	     $str1->setFetchMode(PDO::FETCH_OBJ);	
 
			 echo"<ul id=orderchild".$bar->id." style='display:none;')>
			      <div id=ordergroup".$bar->id.">";
			 while($bar1=$str1->fetch())
			 {
				if(strtolower($bar1->class)=='devider')
				{
				   $bar1->caption="------------";	
				}
				if(strtolower($bar1->class)=='title' or strtolower($bar1->class)=='devider')
				{
				  echo "<li class=mmgr><img src='images/menu/arrow_10.gif'> 
				  ".$bar1->caption;		
				}
				else{
				   echo "<li class=mmgr><img title=Expand class=arrow src='images/foldc_.png' height=17px   onclick=show_sub('orderchild".$bar1->id."',this);> 
				   <a class=lab title='Click to show this submenu order editor' id=orderlab".$bar1->id." onclick=showEditor('".$bar1->id."','true',event)>".$bar1->caption."</a>";			
				}
			//=========================================================
				     $str2=$owlPDO->query("select * from ".$dbname.".menu  where parent=".$bar1->id." order by urut");
				     $str2->setFetchMode(PDO::FETCH_OBJ);	
 
						 echo"<ul id=orderchild".$bar1->id." style='display:none;')>
						      <div id=ordergroup".$bar1->id.">";
						 while($bar2=$str2->fetch())
						 {
							if(strtolower($bar2->class)=='devider')
							   $bar2->caption="------------";							
							if(strtolower($bar2->class)=='title' or strtolower($bar2->class)=='devider')
							{
							   echo "<li class=mmgr><img src='images/menu/arrow_10.gif'> 
							    ".$bar2->caption;		
							}
							else{
								echo "<li class=mmgr><img title=Expand class=arrow src='images/foldc_.png' height=17px  onclick=show_sub('orderchild".$bar2->id."',this);> 
								 <a class=lab title='Click to show this submenu order editor' id=orderlab".$bar2->id." onclick=showEditor('".$bar2->id."','true',event)>".$bar2->caption."</a>";			
							}
						//=========================================================
							     $str3=$owlPDO->query("select * from ".$dbname.".menu  where parent=".$bar2->id." order by urut");
							     $str3->setFetchMode(PDO::FETCH_OBJ);	
   
									 echo"<ul id=orderchild".$bar2->id." style='display:none;'>
									      <div id=ordergroup".$bar2->id.">";
									 while($bar3=$str3->fetch())
									 {
									 if(strtolower($bar3->class)=='devider')
									   $bar3->caption="------------";							
									 if(strtolower($bar3->class)=='title' or strtolower($bar3->class)=='devider')
									 {
									   echo "<li class=mmgr><img src='images/menu/arrow_10.gif'> 
									   ".$bar3->caption;		
									 }
									 else{
										echo "<li class=mmgr><img title=Expand class=arrow src='images/foldc_.png' height=17px  onclick=show_sub('orderchild".$bar3->id."',this);> 
										 <a class=lab title='Click to show this submenu order editor' id=orderlab".$bar3->id." onclick=showEditor('".$bar3->id."','true',event)>".$bar3->caption."</a>";	
									 }
										//=========================================================
										     $str4=$owlPDO->query("select * from ".$dbname.".menu  where parent=".$bar3->id." order by urut");
										     $str4->setFetchMode(PDO::FETCH_OBJ);	
  
												 echo"<ul id=orderchild".$bar3->id." style='display:none;'>
												      <div id=ordergroup".$bar3->id.">";
												 while($bar4=$str4->fetch())
												 {
												 if(strtolower($bar4->class)=='devider')
												   $bar4->caption="------------";							
												  if(strtolower($bar4->class)=='title' or strtolower($bar4->class)=='devider')
												  {
												     echo "<li class=mmgr><img src='images/menu/arrow_10.gif'> 
													 ".$bar4->caption;	
												  }
												  else{
													 echo "<li class=mmgr><img title=Expand class=arrow src='images/foldc_.png' height=17px  onclick=show_sub('orderchild".$bar4->id."',this);> 
													  <a class=lab title='Click to show this submenu order editor' id=orderlab".$bar4->id." onclick=showEditor('".$bar4->id."','true',event)>".$bar4->caption."</a>";
												  }
												//=========================================================
													     $str5=$owlPDO->query("select * from ".$dbname.".menu  where parent=".$bar4->id." order by urut");
													     $str5->setFetchMode(PDO::FETCH_OBJ);
  
															 echo"<ul id=orderchild".$bar4->id." style='display:none;'>
																  <div id=ordergroup".$bar4->id.">";
															 while($bar5=$str5->fetch())
															 {
															 if(strtolower($bar5->class)=='devider')
															   $bar5->caption="------------";							
															  if(strtolower($bar5->class)=='title' or strtolower($bar5->class)=='devider')
															  {
															     echo "<li class=mmgr><img  src='images/menu/arrow_10.gif'> 
																 ".$bar5->caption;		
															  }
															  else{
																echo "<li class=mmgr><img class=arrow title='Expand' src='images/foldc_.png' height=17px  onclick=show_sub('orderchild".$bar5->id."',this);> 
																   <a class=lab title='Click to show this submenu order editor' id=orderlab".$bar5->id." onclick=showEditor('".$bar5->id."','true',event)>".$bar5->caption."</a>";	
															  }
															//=========================================================
																     $str6=$owlPDO->query("select * from ".$dbname.".menu  where parent=".$bar5->id." order by urut");
																     $str6->setFetchMode(PDO::FETCH_OBJ);	
  
																		 echo"<ul id=orderchild".$bar5->id." style='display:none;'>
																		      <div id=ordergroup".$bar5->id.">";
																		 while($bar6=$str6->fetch())
																		 {
																		 if(strtolower($bar6->class)=='devider')
																		   $bar6->caption="------------";							

																			echo "<li>".$bar6->caption."</li>";
																		 }
																		 echo"</div>
																		 </ul>";
																	 
															//========================================================																			
																echo "</li>";
															 }
															 echo"</div>
															  </ul>";
														 
												//========================================================																
													echo "</li>";
												 }
												 echo"</div>
													  </ul>";
											 
									//========================================================												
										echo "</li>";
									 }
									 echo"</div>
									      </ul>";
								 
						//========================================================
							echo "</li>";
						 }
						 echo"</div>
						      </ul>";
					
			//========================================================
				echo "</li>";
			 }
			 echo"</div>			 
			      </ul>";
		 
//========================================================
	echo "</li>";
}
echo "</ul></div>";	
echo"</div>";

//*****************************
//menu order editor
echo"<div id=ordereditor style='display:none;position:absolute;'>";
echo OPEN_THEME('Choose user:');
  echo"<div id=ordereditorcontent></div>";  
echo CLOSE_THEME();
echo"</div>";

//end menuOrder
//==================================================================================================================================================================

echo CLOSE_THEME();
CLOSE_BOX();
echo close_body();
?>
