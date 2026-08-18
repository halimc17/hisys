<?php
require_once('master_validation.php');
include('lib/nangkoelib.php');
include('lib/zLib.php');
echo open_body();
?>
<script language=javascript1.2 src=js/menusettingbi.js></script>
<?
include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.strtoupper($_SESSION['lang']['menusettings']).'(BI)</span>');
echo OPEN_THEME($_SESSION['lang']['menusettings'].":");

## MENU SETTINGS
echo" <div id=menuSettingContainer>
	<input type=radio name=rad onclick=expandAll()>".$_SESSION['lang']['expandall']."
	<input type=radio name=rad onclick=collapsAll() checked>".$_SESSION['lang']['colapsall']."
	<hr><b>Menu Settings:</b>";

## GET MAX ID OF MENU
$max_id=0;
$str="select max(id) as mx from ".$dbname.".menubi";
$res=fetchdata($str);
$max_id=$res[0]['mx'];

echo"<script langguage=javascript1.2>max_id=".$max_id.";</script>";
	 
## SHOW MENU
echo"<ul>
	<div id=group0>";

$str="select * from ".$dbname.".menubi where type='master' order by urut";
$res=fetchdata($str);
foreach($res as $val){
	### OPEN PARENT (MASTER)###
	echo"<li class=mmgr>
		<img title=expand class=arrow src='images/foldc_.png' height=17px   onclick=show_sub('child".$val['id']."',this);> 
			<a class=lab id=lab".$val['id']." onclick=edit('".$val['id']."') title='Click to Change'>".$val['caption']."</a>
			<a class=formeditcaption id=edit".$val['id']."></a>";
	if($val['hide']==0){
		echo"<input class=cbox type=checkbox id=check".$val['id']." onclick=\"activate('".$val['id']."');\" checked title='Click to deActivate!'>";
	}else{
		echo"<input class=cbox type=checkbox id=check".$val['id']." onclick=\"activate('".$val['id']."');\" title='Click to Activate!'>";
	}
	echo"&nbsp;&nbsp;<img class=dellicon title='Delete!' src='images/menu/delete1.jpg' onclick=\"delet('".$val['id']."');\" id=img".$val['id'].">";	
	
	############ CHILD 2 ############
	echo"<ul id=child".$val['id']." style='display:none;')>
		<div id=group".$val['id'].">";
		$str2="select * from ".$dbname.".menubi where parent='".$val['id']."' order by urut";
		$res2=fetchdata($str2);
		foreach($res2 as $val2){
			if(strtolower($val2['class'])=='devider'){
			   $val2['caption']="------------";	
			}
			if(strtolower($val2['class'])=='title' or strtolower($val2['class'])=='devider'){
				echo "<li class=mmgr>
					<img src='images/menu/arrow_10.gif'> 
						<a class=lab id=lab".$val2['id']." onclick=edit('".$val2['id']."') title='Click to Change'>".$val2['caption']."</a><a class=formeditcaption id=edit".$val2['id']."></a>";		
			}else{
				echo "<li class=mmgr>
					<img title=Expand class=arrow src='images/foldc_.png' height=17px onclick=show_sub('child".$val2['id']."',this);> 
						<a class=lab id=lab".$val2['id']." onclick=edit('".$val2['id']."') title='Click to Change'>".$val2['caption']."</a><a class=formeditcaption id=edit".$val2['id']."></a>";
			}
			if($val2['hide']==0){
				echo"<input class=cbox type=checkbox id=check".$val2['id']." onclick=\"activate('".$val2['id']."');\" checked title='Click to deActivate!'>";
				
			}else{
				echo"<input class=cbox type=checkbox id=check".$val2['id']." onclick=\"activate('".$val2['id']."');\" title='Click to Activate!'>";
			}
			echo"&nbsp;&nbsp;<img class=dellicon title='Delete!' src='images/menu/delete1.jpg' onclick=\"delet('".$val2['id']."');\" id=img".$val2['id'].">";
			
			############ CHILD 3 ############
			echo"<ul id=child".$val2['id']." style='display:none;')>
				<div id=group".$val2['id'].">";
				$str3="select * from ".$dbname.".menubi where parent='".$val2['id']."' order by urut";
				$res3=fetchdata($str3);
				foreach($res3 as $val3){
					if(strtolower($val3['class'])=='devider'){
						$val3['caption']="------------";							
					}
					if(strtolower($val3['class'])=='title' or strtolower($val3['class'])=='devider'){
						echo "<li class=mmgr>
							<img src='images/menu/arrow_10.gif'>
								<a class=lab id=lab".$val3['id']." onclick=edit('".$val3['id']."') title='Click to Change'>".$val3['caption']."</a><a class=formeditcaption id=edit".$val3['id']."></a>";		
					}else{
						echo "<li class=mmgr>
							<img title=Expand class=arrow src='images/foldc_.png' height=17px  onclick=show_sub('child".$val3['id']."',this);> 
								<a class=lab id=lab".$val3['id']." onclick=edit('".$val3['id']."') title='Click to Change'>".$val3['caption']."</a><a class=formeditcaption id=edit".$val3['id']."></a>";			
					}
					if($val3['hide']==0){
						echo" <input class=cbox type=checkbox id=check".$val3['id']." onclick=\"activate('".$val3['id']."');\" checked title='Click to deActivate!'>";						
					}else{
						echo" <input class=cbox type=checkbox id=check".$val3['id']." onclick=\"activate('".$val3['id']."');\" title='Click to Activate!'>";						
					}
					echo"&nbsp;&nbsp;<img class=dellicon title='Delete!' src='images/menu/delete1.jpg' onclick=\"delet('".$val3['id']."');\" id=img".$val3['id'].">";
					
					############ CHILD 4 ############
					echo"<ul id=child".$val3['id']." style='display:none;')>
						<div id=group".$val3['id'].">";
						$str4="select * from ".$dbname.".menubi where parent='".$val3['id']."' order by urut";
						$res4=fetchdata($str4);
						foreach($res4 as $val4){
							if(strtolower($val4['class'])=='devider'){
								$val4['caption']="------------";							
							}
							if(strtolower($val4['class'])=='title' or strtolower($val4['class'])=='devider'){
								echo "<li class=mmgr>
									<img src='images/menu/arrow_10.gif'>
										<a class=lab id=lab".$val4['id']." onclick=edit('".$val4['id']."') title='Click to Change'>".$val4['caption']."</a><a class=formeditcaption id=edit".$val4['id']."></a>";		
							}else{
								echo "<li class=mmgr>
									<img title=Expand class=arrow src='images/foldc_.png' height=17px  onclick=show_sub('child".$val4['id']."',this);> 
										<a class=lab id=lab".$val4['id']." onclick=edit('".$val4['id']."') title='Click to Change'>".$val4['caption']."</a><a class=formeditcaption id=edit".$val4['id']."></a>";			
							}
							if($val4['hide']==0){
								echo" <input class=cbox type=checkbox id=check".$val4['id']." onclick=\"activate('".$val4['id']."');\" checked title='Click to deActivate!'>";						
							}else{
								echo" <input class=cbox type=checkbox id=check".$val4['id']." onclick=\"activate('".$val4['id']."');\" title='Click to Activate!'>";						
							}
							echo"&nbsp;&nbsp;<img class=dellicon title='Delete!' src='images/menu/delete1.jpg' onclick=\"delet('".$val4['id']."');\" id=img".$val4['id'].">";
							
							############ CHILD 5 ############
							echo"<ul id=child".$val4['id']." style='display:none;')>
								<div id=group".$val4['id'].">";
								$str5="select * from ".$dbname.".menubi where parent='".$val4['id']."' order by urut";
								$res5=fetchdata($str5);
								foreach($res5 as $val5){
									if(strtolower($val5['class'])=='devider'){
										$val5['caption']="------------";							
									}
									if(strtolower($val5['class'])=='title' or strtolower($val5['class'])=='devider'){
										echo "<li class=mmgr>
											<img src='images/menu/arrow_10.gif'>
												<a class=lab id=lab".$val5['id']." onclick=edit('".$val5['id']."') title='Click to Change'>".$val5['caption']."</a><a class=formeditcaption id=edit".$val5['id']."></a>";		
									}else{
										echo "<li class=mmgr>
											<img title=Expand class=arrow src='images/foldc_.png' height=17px  onclick=show_sub('child".$val5['id']."',this);> 
												<a class=lab id=lab".$val5['id']." onclick=edit('".$val5['id']."') title='Click to Change'>".$val5['caption']."</a><a class=formeditcaption id=edit".$val5['id']."></a>";			
									}
									if($val5['hide']==0){
										echo" <input class=cbox type=checkbox id=check".$val5['id']." onclick=\"activate('".$val5['id']."');\" checked title='Click to deActivate!'>";						
									}else{
										echo" <input class=cbox type=checkbox id=check".$val5['id']." onclick=\"activate('".$val5['id']."');\" title='Click to Activate!'>";						
									}
									echo"&nbsp;&nbsp;<img class=dellicon title='Delete!' src='images/menu/delete1.jpg' onclick=\"delet('".$val5['id']."');\" id=img".$val5['id'].">";
									
									############ CHILD 6 ############
									echo"<ul id=child".$val5['id']." style='display:none;')>
										<div id=group".$val5['id'].">";
										$str6="select * from ".$dbname.".menubi where parent='".$val5['id']."' order by urut";
										$res6=fetchdata($str6);
										foreach($res6 as $val6){
											if(strtolower($val6['class'])=='devider'){
												$val6['caption']="------------";							
											}
											if(strtolower($val6['class'])=='title' or strtolower($val6['class'])=='devider'){
												echo "<li class=mmgr>
													<img src='images/menu/arrow_10.gif'>
														<a class=lab id=lab".$val6['id']." onclick=edit('".$val6['id']."') title='Click to Change'>".$val6['caption']."</a><a class=formeditcaption id=edit".$val6['id']."></a>";		
											}else{
												echo "<li class=mmgr>
													<img title=Expand class=arrow src='images/foldc_.png' height=17px  onclick=show_sub('child".$val6['id']."',this);> 
														<a class=lab id=lab".$val6['id']." onclick=edit('".$val6['id']."') title='Click to Change'>".$val6['caption']."</a><a class=formeditcaption id=edit".$val6['id']."></a>";			
											}
											if($val6['hide']==0){
												echo" <input class=cbox type=checkbox id=check".$val6['id']." onclick=\"activate('".$val6['id']."');\" checked title='Click to deActivate!'>";						
											}else{
												echo" <input class=cbox type=checkbox id=check".$val6['id']." onclick=\"activate('".$val6['id']."');\" title='Click to Activate!'>";						
											}
											echo"&nbsp;&nbsp;<img class=dellicon title='Delete!' src='images/menu/delete1.jpg' onclick=\"delet('".$val6['id']."');\" id=img".$val6['id'].">";
											
											############ CHILD 7 ############
											echo"<ul id=child".$val6['id']." style='display:none;')>
												<div id=group".$val6['id'].">";
												$str7="select * from ".$dbname.".menubi where parent='".$val6['id']."' order by urut";
												$res7=fetchdata($str7);
												foreach($res7 as $val7){
													if(strtolower($val7['class'])=='devider'){
														$val7['caption']="------------";							
													}
													if(strtolower($val7['class'])=='title' or strtolower($val7['class'])=='devider'){
														echo "<li class=mmgr>
															<img src='images/menu/arrow_10.gif'>
																<a class=lab id=lab".$val7['id']." onclick=edit('".$val7['id']."') title='Click to Change'>".$val7['caption']."</a><a class=formeditcaption id=edit".$val7['id']."></a>";		
													}else{
														echo "<li class=mmgr>
															<img title=Expand class=arrow src='images/foldc_.png' height=17px  onclick=show_sub('child".$val7['id']."',this);> 
																<a class=lab id=lab".$val7['id']." onclick=edit('".$val7['id']."') title='Click to Change'>".$val7['caption']."</a><a class=formeditcaption id=edit".$val7['id']."></a>";			
													}
													if($val7['hide']==0){
														echo" <input class=cbox type=checkbox id=check".$val7['id']." onclick=\"activate('".$val7['id']."');\" checked title='Click to deActivate!'>";						
													}else{
														echo" <input class=cbox type=checkbox id=check".$val7['id']." onclick=\"activate('".$val7['id']."');\" title='Click to Activate!'>";						
													}
													echo"&nbsp;&nbsp;<img class=dellicon title='Delete!' src='images/menu/delete1.jpg' onclick=\"delet('".$val7['id']."');\" id=img".$val7['id'].">
													</li>";
												}
												
												######## 6 ########
												echo"</div>
													<li>
													<div id=inputmenu".$val6['id']." class=menuinput  style='display:none;'>
														<select id=type".$val6['id']." onchange=checkType('".$val6['id']."',this)>
															<option>Type...</option>
															<option>click</option>
															<option>title</option>
															<option>devider</option>
														</select>
														<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption".$val6['id']." size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
														<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption2".$val6['id']."x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
														<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption3".$val6['id']."x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
														<input type=text value='Action...' maxlength=40 class=myinputtext title='Filename (without extension) that will be execute when menu clicked' id=newAction".$val6['id']." size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
														<input type=checkbox  title='Check to create this file' id=newFile".$val6['id'].">
															<font color=white>".$_SESSION['lang']['createfile']."</font>
														<input type=hidden id=master_menu".$val6['id']." value=".$val6['id'].">
														<input type=button class=mybutton value=".$_SESSION['lang']['save']." onclick=saveMenu('master_menu".$val6['id']."','newCaption".$val6['id']."','newCaption2".$val6['id']."x','newCaption3".$val6['id']."x','newAction".$val6['id']."','link".$val6['id']."','inputmenu".$val6['id']."','type".$val6['id']."','newFile".$val6['id']."');>
														<input type=button class=mybutton value=".$_SESSION['lang']['close']." onclick=showById('inputmenu".$val6['id']."','link".$val6['id']."')>
													</div>
													<a class=newMenu title='Create New Link' id=link".$val6['id']." onclick=\"javascript:hideObject(this);showObject(document.getElementById('inputmenu".$val6['id']."'));\">".$_SESSION['lang']['new']."</a>
													</li>
											</ul>
											</li>";
										}
										
										######## 5 ########
										echo"</div>
											<li>
											<div id=inputmenu".$val5['id']." class=menuinput  style='display:none;'>
												<select id=type".$val5['id']." onchange=checkType('".$val5['id']."',this)>
													<option>Type...</option>
													<option>click</option>
													<option>title</option>
													<option>devider</option>
												</select>
												<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption".$val5['id']." size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
												<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption2".$val5['id']."x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
												<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption3".$val5['id']."x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
												<input type=text value='Action...' maxlength=40 class=myinputtext title='Filename (without extension) that will be execute when menu clicked' id=newAction".$val5['id']." size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
												<input type=checkbox  title='Check to create this file' id=newFile".$val5['id'].">
													<font color=white>".$_SESSION['lang']['createfile']."</font>
												<input type=hidden id=master_menu".$val5['id']." value=".$val5['id'].">
												<input type=button class=mybutton value=".$_SESSION['lang']['save']." onclick=saveMenu('master_menu".$val5['id']."','newCaption".$val5['id']."','newCaption2".$val5['id']."x','newCaption3".$val5['id']."x','newAction".$val5['id']."','link".$val5['id']."','inputmenu".$val5['id']."','type".$val5['id']."','newFile".$val5['id']."');>
												<input type=button class=mybutton value=".$_SESSION['lang']['close']." onclick=showById('inputmenu".$val5['id']."','link".$val5['id']."')>
											</div>
											<a class=newMenu title='Create New Link' id=link".$val5['id']." onclick=\"javascript:hideObject(this);showObject(document.getElementById('inputmenu".$val5['id']."'));\">".$_SESSION['lang']['new']."</a>
											</li>
									</ul>
									</li>";
								}
								
								######## 4 ########
								echo"</div>
									<li>
									<div id=inputmenu".$val4['id']." class=menuinput  style='display:none;'>
										<select id=type".$val4['id']." onchange=checkType('".$val4['id']."',this)>
											<option>Type...</option>
											<option>click</option>
											<option>title</option>
											<option>devider</option>
										</select>
										<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption".$val4['id']." size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
										<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption2".$val4['id']."x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
										<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption3".$val4['id']."x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
										<input type=text value='Action...' maxlength=40 class=myinputtext title='Filename (without extension) that will be execute when menu clicked' id=newAction".$val4['id']." size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
										<input type=checkbox  title='Check to create this file' id=newFile".$val4['id'].">
											<font color=white>".$_SESSION['lang']['createfile']."</font>
										<input type=hidden id=master_menu".$val4['id']." value=".$val4['id'].">
										<input type=button class=mybutton value=".$_SESSION['lang']['save']." onclick=saveMenu('master_menu".$val4['id']."','newCaption".$val4['id']."','newCaption2".$val4['id']."x','newCaption3".$val4['id']."x','newAction".$val4['id']."','link".$val4['id']."','inputmenu".$val4['id']."','type".$val4['id']."','newFile".$val4['id']."');>
										<input type=button class=mybutton value=".$_SESSION['lang']['close']." onclick=showById('inputmenu".$val4['id']."','link".$val4['id']."')>
									</div>
									<a class=newMenu title='Create New Link' id=link".$val4['id']." onclick=\"javascript:hideObject(this);showObject(document.getElementById('inputmenu".$val4['id']."'));\">".$_SESSION['lang']['new']."</a>
									</li>
							</ul>
							</li>";
						}
						
						######## 3 ########
						echo"</div>
							<li>
							<div id=inputmenu".$val3['id']." class=menuinput  style='display:none;'>
								<select id=type".$val3['id']." onchange=checkType('".$val3['id']."',this)>
									<option>Type...</option>
									<option>click</option>
									<option>title</option>
									<option>devider</option>
								</select>
								<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption".$val3['id']." size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
								<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption2".$val3['id']."x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
								<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption3".$val3['id']."x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
								<input type=text value='Action...' maxlength=40 class=myinputtext title='Filename (without extension) that will be execute when menu clicked' id=newAction".$val3['id']." size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
								<input type=checkbox  title='Check to create this file' id=newFile".$val3['id'].">
									<font color=white>".$_SESSION['lang']['createfile']."</font>
								<input type=hidden id=master_menu".$val3['id']." value=".$val3['id'].">
								<input type=button class=mybutton value=".$_SESSION['lang']['save']." onclick=saveMenu('master_menu".$val3['id']."','newCaption".$val3['id']."','newCaption2".$val3['id']."x','newCaption3".$val3['id']."x','newAction".$val3['id']."','link".$val3['id']."','inputmenu".$val3['id']."','type".$val3['id']."','newFile".$val3['id']."');>
								<input type=button class=mybutton value=".$_SESSION['lang']['close']." onclick=showById('inputmenu".$val3['id']."','link".$val3['id']."')>
							</div>
							<a class=newMenu title='Create New Link' id=link".$val3['id']." onclick=\"javascript:hideObject(this);showObject(document.getElementById('inputmenu".$val3['id']."'));\">".$_SESSION['lang']['new']."</a>
							</li>
					</ul>
					</li>";
				}
				
				######## 2 ########
				echo"</div>
					<li>
					<div id=inputmenu".$val2['id']." class=menuinput  style='display:none;'>
						<select id=type".$val2['id']." onchange=checkType('".$val2['id']."',this)>
							<option>Type...</option>
							<option>click</option>
							<option>title</option>
							<option>devider</option>
						</select>
						<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption".$val2['id']." size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
						<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption2".$val2['id']."x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
						<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption3".$val2['id']."x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
						<input type=text value='Action...' maxlength=40 class=myinputtext title='Filename (without extension) that will be execute when menu clicked' id=newAction".$val2['id']." size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
						<input type=checkbox  title='Check to create this file' id=newFile".$val2['id'].">
							<font color=white>".$_SESSION['lang']['createfile']."</font>
						<input type=hidden id=master_menu".$val2['id']." value=".$val2['id'].">
						<input type=button class=mybutton value=".$_SESSION['lang']['save']." onclick=saveMenu('master_menu".$val2['id']."','newCaption".$val2['id']."','newCaption2".$val2['id']."x','newCaption3".$val2['id']."x','newAction".$val2['id']."','link".$val2['id']."','inputmenu".$val2['id']."','type".$val2['id']."','newFile".$val2['id']."');>
						<input type=button class=mybutton value=".$_SESSION['lang']['close']." onclick=showById('inputmenu".$val2['id']."','link".$val2['id']."')>
					</div>
					<a class=newMenu title='Create New Link' id=link".$val2['id']." onclick=\"javascript:hideObject(this);showObject(document.getElementById('inputmenu".$val2['id']."'));\">".$_SESSION['lang']['new']."</a>
					</li>
			</ul>
			</li>";
		}
		
		######## 1 ########
		echo"</div>
			<li>
			<div id=inputmenu".$val['id']." class=menuinput  style='display:none;'>
				<select id=type".$val['id']." onchange=checkType('".$val['id']."',this)>
					<option>Type...</option>
					<option>click</option>
					<option>title</option>
					<option>devider</option>
				</select>
				<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption".$val['id']." size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
				<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption2".$val['id']."x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
				<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption3".$val['id']."x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
				<input type=text value='Action...' maxlength=40 class=myinputtext title='Filename (without extension) that will be execute when menu clicked' id=newAction".$val['id']." size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
				<input type=checkbox  title='Check to create this file' id=newFile".$val['id'].">
					<font color=white>".$_SESSION['lang']['createfile']."</font>
				<input type=hidden id=master_menu".$val['id']." value=".$val['id'].">
				<input type=button class=mybutton value=".$_SESSION['lang']['save']." onclick=saveMenu('master_menu".$val['id']."','newCaption".$val['id']."','newCaption2".$val['id']."x','newCaption3".$val['id']."x','newAction".$val['id']."','link".$val['id']."','inputmenu".$val['id']."','type".$val['id']."','newFile".$val['id']."');>
				<input type=button class=mybutton value=".$_SESSION['lang']['close']." onclick=showById('inputmenu".$val['id']."','link".$val['id']."')>
			</div>
			<a class=newMenu title='Create New Link' id=link".$val['id']." onclick=\"javascript:hideObject(this);showObject(document.getElementById('inputmenu".$val['id']."'));\">".$_SESSION['lang']['new']."</a>
			</li>
	</ul>";
	
	### CLOSE PARENT (MASTER)###
	echo"</li>";
}

echo"</div>
	<li class=mmgr>
		<div id=inputmenu0  class=menuinput style='display:none;'>
			<img src='images/foldc_.png' height=17px >
			<select id=type0 onchange=checkType('0',this)><option>click</option></select> 
			<input type=text value='Caption...'  maxlength=40 class=myinputtext title='Text to be shown on top most of menu' id=newCaption0 size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
			<input type=text value='Caption...'  maxlength=40 class=myinputtext title='Text to be shown on top most of menu' id=newCaption2x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
			<input type=text value='Caption...'  maxlength=40 class=myinputtext title='Text to be shown on top most of menu' id=newCaption3x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>
			<input type=text value='null'  maxlength=40 class=myinputtext title='Filename (without extension) that will be execute when menu clicked' id=newAction0 size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this) disabled>
			<input type=checkbox  title='Check to create this file' id=newFile0 disabled><font color=white>".$_SESSION['lang']['createfile']."</font>
			<input type=hidden id=master_menu0 value=0>\
			
			<input type=button class=mybutton value=".$_SESSION['lang']['save']." onclick=saveMenu('master_menu0','newCaption0','newCaption2x','newCaption3x','newAction0','link0','inputmenu0','type0','newFile0');>
			<input type=button class=mybutton value=".$_SESSION['lang']['close']." onclick=showById('inputmenu0','link0')>
		</div>
		<a class=newMenu id=link0  title='Create New Link'  onclick=\"javascript:hideObject(this);showObject(document.getElementById('inputmenu0'));\">".$_SESSION['lang']['new']."</a>
	</li>";
echo"</ul></div>";

echo"</ul>
</div>";


################################
echo"<div id=menuOrderContainer style='position:relative;display:none'>
	<input type=radio name=rad1 onclick=expandAllOrder()>".$_SESSION['lang']['expandall']."
	<input type=radio name=rad1 onclick=collapsAllOrder() checked>".$_SESSION['lang']['colapsall']."
	<hr><b>Menu Arranger</b>:
	<ul>
		<a  class=lab id=orderlab0 href=# onclick=showEditor('0','false',event) title='Click to arrange master menu (the top most menu)'>".$_SESSION['lang']['mastermenu']."</a>
		<div id=ordergroup0>";
		
		$str="select * from ".$dbname.".menubi where type='master' order by urut";
		$res=fetchdata($str);
		foreach($res as $val){
			echo"<li class=mmgr>
				<img title=expand class=arrow src='images/foldc_.png' height=17px   onclick=show_sub('orderchild".$val['id']."',this);>
					<a class=lab  title='Click to show this submenu order editor' id=orderlab".$val['id']." onclick=showEditor('".$val['id']."','true',event)>".$val['caption']."</a>";
					
			################################
			echo"<ul id=orderchild".$val['id']." style='display:none;')>
				<div id=ordergroup".$val['id'].">";
			$str2="select * from ".$dbname.".menubi where parent=".$val['id']." order by urut";
			$res2=fetchdata($str2);
			foreach($res2 as $val2){
				if(strtolower($val2['class'])=='devider'){
					$val2['caption']="------------";	
				}
				if(strtolower($val2['class'])=='title' or strtolower($val2['class'])=='devider'){
					echo"<li class=mmgr><img src='images/menu/arrow_10.gif'>".$val2['caption'];		
				}else{
					echo"<li class=mmgr><img title=Expand class=arrow src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$val2['id']."',this);> 
				   <a class=lab title='Click to show this submenu order editor' id=orderlab".$val2['id']." onclick=showEditor('".$val2['id']."','true',event)>".$val2['caption']."</a>";
				}
				
				################################
				echo"<ul id=orderchild".$val2['id']." style='display:none;')>
					<div id=ordergroup".$val2['id'].">";
				$str3="select * from ".$dbname.".menubi where parent=".$val2['id']." order by urut";
				$res3=fetchdata($str3);
				foreach($res3 as $val3){
					if(strtolower($val3['class'])=='devider'){
						$val3['caption']="------------";	
					}
					if(strtolower($val3['class'])=='title' or strtolower($val3['class'])=='devider'){
						echo"<li class=mmgr><img src='images/menu/arrow_10.gif'>".$val3['caption'];		
					}else{
						echo"<li class=mmgr><img title=Expand class=arrow src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$val3['id']."',this);> 
					   <a class=lab title='Click to show this submenu order editor' id=orderlab".$val3['id']." onclick=showEditor('".$val3['id']."','true',event)>".$val3['caption']."</a>";			
					}
					
					################################
					echo"<ul id=orderchild".$val3['id']." style='display:none;')>
						<div id=ordergroup".$val3['id'].">";
					$str4="select * from ".$dbname.".menubi where parent=".$val3['id']." order by urut";
					$res4=fetchdata($str4);
					foreach($res4 as $val4){
						if(strtolower($val4['class'])=='devider'){
							$val4['caption']="------------";	
						}
						if(strtolower($val4['class'])=='title' or strtolower($val4['class'])=='devider'){
							echo"<li class=mmgr><img src='images/menu/arrow_10.gif'>".$val4['caption'];		
						}else{
							echo"<li class=mmgr><img title=Expand class=arrow src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$val4['id']."',this);> 
						   <a class=lab title='Click to show this submenu order editor' id=orderlab".$val4['id']." onclick=showEditor('".$val4['id']."','true',event)>".$val4['caption']."</a>";			
						}
						
						################################
						echo"<ul id=orderchild".$val4['id']." style='display:none;')>
							<div id=ordergroup".$val4['id'].">";
						$str5="select * from ".$dbname.".menubi where parent=".$val4['id']." order by urut";
						$res5=fetchdata($str5);
						foreach($res5 as $val5){
							if(strtolower($val5['class'])=='devider'){
								$val5['caption']="------------";	
							}
							if(strtolower($val5['class'])=='title' or strtolower($val5['class'])=='devider'){
								echo"<li class=mmgr><img src='images/menu/arrow_10.gif'>".$val5['caption'];		
							}else{
								echo"<li class=mmgr><img title=Expand class=arrow src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$val5['id']."',this);> 
							   <a class=lab title='Click to show this submenu order editor' id=orderlab".$val5['id']." onclick=showEditor('".$val5['id']."','true',event)>".$val5['caption']."</a>";			
							}
							
							################################
							echo"<ul id=orderchild".$val5['id']." style='display:none;')>
								<div id=ordergroup".$val5['id'].">";
							$str6="select * from ".$dbname.".menubi where parent=".$val5['id']." order by urut";
							$res6=fetchdata($str6);
							foreach($res6 as $val6){
								if(strtolower($val6['class'])=='devider'){
									$val6['caption']="------------";	
								}
								if(strtolower($val6['class'])=='title' or strtolower($val6['class'])=='devider'){
									echo"<li class=mmgr><img src='images/menu/arrow_10.gif'>".$val6['caption'];		
								}else{
									echo"<li class=mmgr><img title=Expand class=arrow src='images/foldc_.png' height=17px onclick=show_sub('orderchild".$val6['id']."',this);> 
								   <a class=lab title='Click to show this submenu order editor' id=orderlab".$val6['id']." onclick=showEditor('".$val6['id']."','true',event)>".$val6['caption']."</a>";			
								}
								
								################################
								echo"<ul id=orderchild".$val6['id']." style='display:none;')>
									<div id=ordergroup".$val6['id'].">";
								$str7="select * from ".$dbname.".menubi where parent=".$val6['id']." order by urut";
								$res7=fetchdata($str7);
								foreach($res7 as $val7){
									if(strtolower($val7['class'])=='devider'){
										$val7['caption']="------------";	
									}
									echo "<li>".$val7['caption']."</li>";
								}
								echo"</div></ul></li>";
							}
							echo"</div></ul></li>";
						}
						echo"</div></ul></li>";
					}
					echo"</div></ul></li>";
				}
				echo"</div></ul></li>";
			}
			echo"</div></ul></li>";
		}
echo "</ul></div>";	
echo"</div>";


##*****************************
##menu order editor
echo"<div id=ordereditor style='display:none;position:absolute;'>";
echo OPEN_THEME(''.$_SESSION['lang']['menuordereditor'].':');
  echo"<div id=ordereditorcontent></div>";  
echo CLOSE_THEME();
echo"</div>";

##end menuOrder
##==================================================================================================================================================================
// echo "<hr><center><input type=button class=mybutton value=".$_SESSION['lang']['apply']." onclick=window.location.reload()><hr></center>
      // <fieldset><legend>".$_SESSION['lang']['options'].":</legend>";
     // echo"<img src=images/menu/star.png> <span class=elink onclick=showMenuOrder() title='Click to manage menu order' id=optionController>Order Arrangement</span><br>";
// echo "</fieldset>";
echo CLOSE_THEME();
CLOSE_BOX();
echo close_body();
?>
