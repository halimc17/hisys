<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
//replace DB Name
$dbname_mobile = 'owlMobile';
echo open_body();
?>
<script language=javascript1.2 src=js/menusettingmobile.js></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['menusettings']).'(Mobile)</span>');
echo OPEN_THEME($_SESSION['lang']['menusettings'].":");
//=================================================================================
//menuSettings
echo" <div id=menuSettingContainer>
     <input type=radio name=rad onclick=expandAll()>".$_SESSION['lang']['expandall']."
	 <input type=radio name=rad onclick=collapsAll() checked>".$_SESSION['lang']['colapsall']."
	 <hr><b>Menu Settings:</b>
	";
//get max id of menu=================================
//default id 0
$max_id=0;
$strx=$owlPDO->query("select max(id) as mx from ".$dbname_mobile.".menu");
$strx->setFetchMode(PDO::FETCH_NUM);
while($barx=$strx->fetch())
{
	$max_id=$barx[0];
}
echo"<script langguage=javascript1.2>
     max_id=".$max_id.";
	 </script>";
//====================================================
$str=$owlPDO->query("select * from ".$dbname_mobile.".menu where type='master' order by urut");
$str->setFetchMode(PDO::FETCH_OBJ);

echo"<ul>
     <div id=group0>";
	 while($bar=$str->fetch()){
		echo "<li class=mmgr><img title=expand class=arrow src='images/foldc_.png' height=17px   onclick=show_sub('child".$bar->id."',this);>
			<a class=lab id=lab".$bar->id." onclick=edit('".$bar->id."') title='Click to Change'>".$bar->caption."</a><a class=formeditcaption id=edit".$bar->id."></a>";
			
			if($bar->hide==0)
				echo" <input class=cbox type=checkbox id=check".$bar->id." onclick=\"activate('".$bar->id."');\" checked title='Click to deActivate!'>";
			else
				echo" <input class=cbox type=checkbox id=check".$bar->id." onclick=\"activate('".$bar->id."');\" title='Click to Activate!'>";
			
			echo" &nbsp &nbsp <img class=dellicon title='Delete!' src='images/menu/delete1.jpg' onclick=\"delet('".$bar->id."');\" id=img".$bar->id.">";
			
			//=========================================================
			$str1=$owlPDO->query("select * from ".$dbname_mobile.".menu where parent=".$bar->id." order by urut");
			$str1->setFetchMode(PDO::FETCH_OBJ);
			
			echo"<ul id=child".$bar->id." style='display:none;')>
				<div id=group".$bar->id.">";
				
				while($bar1=$str1->fetch()){
					if(strtolower($bar1->class)=='devider'){
						$bar1->caption="------------";	
					}
			
					if(strtolower($bar1->class)=='title' or strtolower($bar1->class)=='devider'){
						echo "<li class=mmgr><img src='images/menu/arrow_10.gif'> 
							<a class=lab id=lab".$bar1->id." onclick=edit('".$bar1->id."') title='Click to Change'>".$bar1->caption."</a><a class=formeditcaption id=edit".$bar1->id."></a>";
					}else{
						echo "<li class=mmgr><img title=Expand class=arrow src='images/foldc_.png' height=17px   onclick=show_sub('child".$bar1->id."',this);> 
						<a class=lab id=lab".$bar1->id." onclick=edit('".$bar1->id."') title='Click to Change'>".$bar1->caption."</a><a class=formeditcaption id=edit".$bar1->id."></a>";			
					}
				
					if($bar1->hide==0)
						echo" <input class=cbox type=checkbox id=check".$bar1->id." onclick=\"activate('".$bar1->id."');\" checked title='Click to deActivate!'>";
					else
						echo" <input class=cbox type=checkbox id=check".$bar1->id." onclick=\"activate('".$bar1->id."');\" title='Click to Activate!'>";
					
					echo" &nbsp &nbsp <img class=dellicon title='Delete!' src='images/menu/delete1.jpg' onclick=\"delet('".$bar1->id."');\" id=img".$bar1->id.">";			
					echo "</li>";
					
					//=========================================================
					$str2=$owlPDO->query("select * from ".$dbname_mobile.".menu  where parent=".$bar1->id." order by urut");
					$str2->setFetchMode(PDO::FETCH_OBJ);
					
					echo"<ul id=child".$bar1->id." style='display:none;')>
					<div id=group".$bar1->id.">";
					
					while($bar2=$str2->fetch()){
						if(strtolower($bar2->class)=='devider')
							$bar2->caption="------------";
						
						if(strtolower($bar2->class)=='title' or strtolower($bar2->class)=='devider'){
							echo "<li class=mmgr><img src='images/menu/arrow_10.gif'> 
							<a class=lab id=lab".$bar2->id." onclick=edit('".$bar2->id."') title='Click to Change'>".$bar2->caption."</a><a class=formeditcaption id=edit".$bar2->id."></a>";		
						}else{
							echo "<li class=mmgr><img title=Expand class=arrow src='images/foldc_.png' height=17px  onclick=show_sub('child".$bar2->id."',this);> 
							<a class=lab id=lab".$bar2->id." onclick=edit('".$bar2->id."') title='Click to Change'>".$bar2->caption."</a><a class=formeditcaption id=edit".$bar2->id."></a>";			
						}
						
						if($bar2->hide==0)
							echo" <input class=cbox type=checkbox id=check".$bar2->id." onclick=\"activate('".$bar2->id."');\" checked title='Click to deActivate!'>";
						else
							echo" <input class=cbox type=checkbox id=check".$bar2->id." onclick=\"activate('".$bar2->id."');\" title='Click to Activate!'>";
						
						echo" &nbsp &nbsp <img class=dellicon title='Delete!' src='images/menu/delete1.jpg' onclick=\"delet('".$bar2->id."');\" id=img".$bar2->id.">";
					}
					
					echo"</div>
					<li class=mmgr><div id=inputmenu".$bar1->id." class=menuinput  style='display:none;'>
					<img src='images/foldc_.png' height=17px >
					<select style='display:none' id=type".$bar1->id." onchange=checkType('".$bar1->id."',this)>
						<option>click</option><option>title</option><option>devider</option>
					</select> 
					<input type=text placeholder='Caption...'  maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption".$bar1->id." size=12 onkeypress=\"return tanpa_kutip(event);\">
					<input type=text placeholder='PanelName'  maxlength=40 class=myinputtext title='Panel Name' id=newCaption2".$bar1->id."x size=12 onkeypress=\"return tanpa_kutip(event);\">
					<input type=text placeholder='FunctionName'  maxlength=200 class=myinputtext title='Function Name and Variable' id=newCaption3".$bar1->id."x size=12 onkeypress=\"return tanpa_kutip(event);\">     
					<input type=text placeholder='FormName'  maxlength=40 class=myinputtext title='Form Name' id=newAction".$bar1->id." size=12 onkeypress=\"return tanpa_kutip(event);\">
					<input style='display:none' type=checkbox  title='Check to create this file' id=newFile".$bar1->id.">
					<input type=hidden id=master_menu".$bar1->id." value=".$bar1->id.">
					<input type=button class=mybutton value=".$_SESSION['lang']['save']." onclick=saveMenu('master_menu".$bar1->id."','newCaption".$bar1->id."','newCaption2".$bar1->id."x','newCaption3".$bar1->id."x','newAction".$bar1->id."','link".$bar1->id."','inputmenu".$bar1->id."','type".$bar1->id."','newFile".$bar1->id."');>
					<input type=button class=mybutton value=".$_SESSION['lang']['close']." onclick=showById('inputmenu".$bar1->id."','link".$bar1->id."')>
					</div>
					
					<a class=newMenu title='Create New Link' id=link".$bar1->id." onclick=\"javascript:hideObject(this);showObject(document.getElementById('inputmenu".$bar1->id."'));\">".$_SESSION['lang']['new']."</a></li>
					</ul>";
				}
			
				echo"</div>
				<li class=mmgr>
					<div id=inputmenu".$bar->id." class=menuinput  style='display:none;'>
						<img src='images/foldc_.png' height=17px >
						<select style='display:none' id=type".$bar->id." onchange=checkType('".$bar->id."',this)>
							<option>click</option>
							<option>title</option>
							<option>devider</option>
						</select>
						<input type=text placeholder='Caption...'  maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption".$bar->id." size=12 onkeypress=\"return tanpa_kutip(event);\" >
						
						<input type=text placeholder='PanelName'  maxlength=40 class=myinputtext title='Panel Name' id=newCaption2".$bar->id."x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this) disabled>
						
						<input type=text placeholder='FunctionName'  maxlength=200 class=myinputtext title='Function Name and Variable' id=newCaption3".$bar->id."x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this) disabled>    
						
						<input type=text placeholder='FormName'  maxlength=40 class=myinputtext title='Form Name' id=newAction".$bar->id." size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this) disabled>
						
						<input style='display:none' type=checkbox  title='Check to create this file' id=newFile".$bar->id.">
						
						<input type=hidden id=master_menu".$bar->id." value=".$bar->id.">
						
						<input type=button class=mybutton value=".$_SESSION['lang']['save']." onclick=saveMenu('master_menu".$bar->id."','newCaption".$bar->id."','newCaption2".$bar->id."x','newCaption3".$bar->id."x','newAction".$bar->id."','link".$bar->id."','inputmenu".$bar->id."','type".$bar->id."','newFile".$bar->id."');>
						
						<input type=button class=mybutton value=".$_SESSION['lang']['close']." onclick=showById('inputmenu".$bar->id."','link".$bar->id."')>
					</div>
					<a class=newMenu title='Create New Link' id=link".$bar->id." onclick=\"javascript:hideObject(this);showObject(document.getElementById('inputmenu".$bar->id."'));\">".$_SESSION['lang']['new']."</a>
				</li>
			</ul>";
		 
		//========================================================
		echo "</li>";
	}
	echo "</div>
	<li class=mmgr>
		<div id=inputmenu0  class=menuinput style='display:none;'>
			<img src='images/foldc_.png' height=17px >
			<select style='display:none' id=type0 onchange=checkType('0',this)>
				<option>click</option>
			</select> 
			
			<input type=text placeholder='Caption...'  maxlength=40 class=myinputtext title='Text to be shown on top most of menu' id=newCaption0 size=12 onkeypress=\"return tanpa_kutip(event);\">
			<input type=text placeholder='PanelName'  maxlength=40 class=myinputtext title='Panel Name' id=newCaption2x size=12 onkeypress=\"return tanpa_kutip(event);\">
			<input type=text placeholder='FunctionName'  maxlength=200 class=myinputtext title='Function Name and Variable' id=newCaption3x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this) disabled>
			<input type=text placeholder='FormName'  maxlength=40 class=myinputtext title='Form Name' id=newAction0 size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this) disabled>
			<input style='display:none' type=checkbox  title='Check to create this file' id=newFile0 disabled>
			<input type=hidden id=master_menu0 value=0>
			<input type=button class=mybutton value=".$_SESSION['lang']['save']." onclick=saveMenu('master_menu0','newCaption0','newCaption2x','newCaption3x','newAction0','link0','inputmenu0','type0','newFile0');>
			<input type=button class=mybutton value=".$_SESSION['lang']['close']." onclick=showById('inputmenu0','link0')>
		</div>
		
		<a class=newMenu id=link0  title='Create New Link'  onclick=\"javascript:hideObject(this);showObject(document.getElementById('inputmenu0'));\">".$_SESSION['lang']['new']."</a>
	</li>";

echo"</ul></div>";
//end menuSettingss





//==================================================================================================================================================================
 echo"<div id=menuOrderContainer style='position:relative;display:none'>
     <input type=radio name=rad1 onclick=expandAllOrder()>".$_SESSION['lang']['expandall']."
	 <input type=radio name=rad1 onclick=collapsAllOrder() checked>".$_SESSION['lang']['colapsall']."
	 <hr><b>Menu Arranger</b>:
	";
echo"<ul>
     <a  class=lab id=orderlab0 href=# onclick=showEditor('0','false',event) title='Click to arrange master menu (the top most menu)'>".$_SESSION['lang']['mastermenu']."</a>
     <div id=ordergroup0>";
$str=$owlPDO->query("select * from ".$dbname_mobile.".menu   where type='master' order by urut");
$str->setFetchMode(PDO::FETCH_OBJ);
while($bar=$str->fetch())
{
	echo "<li class=mmgr><img title=expand class=arrow src='images/foldc_.png' height=17px   onclick=show_sub('orderchild".$bar->id."',this);>
	<a class=lab  title='Click to show this submenu order editor' id=orderlab".$bar->id." onclick=showEditor('".$bar->id."','true',event)>".$bar->caption."</a>";
//=========================================================
	     $str1=$owlPDO->query("select * from ".$dbname_mobile.".menu where parent=".$bar->id." order by urut");
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
				     $str2=$owlPDO->query("select * from ".$dbname_mobile.".menu  where parent=".$bar1->id." order by urut");
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
echo OPEN_THEME(''.$_SESSION['lang']['menuordereditor'].':');
  echo"<div id=ordereditorcontent></div>";  
echo CLOSE_THEME();
echo"</div>";

//end menuOrder
//==================================================================================================================================================================
echo "<hr><center><input type=button class=mybutton value=".$_SESSION['lang']['apply']." onclick=window.location.reload()><hr></center>
      <fieldset><legend>".$_SESSION['lang']['options'].":</legend>";
     echo"<img src=images/menu/star.png> <span class=elink onclick=showMenuOrder() title='Click to manage menu order' id=optionController>Order Arrangement</span><br>";
echo "</fieldset>";
echo CLOSE_THEME();
CLOSE_BOX();
echo close_body();
?>
