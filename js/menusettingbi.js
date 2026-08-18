function showById(objtohide,objtoshow){
	//used in menu settings
	document.getElementById(objtoshow).style.display = '';
	document.getElementById(objtohide).style.display = 'none';
}

function checkType(v,obj){
	type=obj.options[obj.selectedIndex].text;
	document.getElementById('newCaption'+v).disabled=false;
	document.getElementById('newAction'+v).disabled=false;
	document.getElementById('newFile'+v).disabled=false;
	
	if(type=='devider'){
		document.getElementById('newCaption'+v).disabled=true;
		document.getElementById('newAction'+v).disabled=true;
		document.getElementById('newFile'+v).checked=false;
		document.getElementById('newFile'+v).disabled=true;
	}else if(type=='title'){
		document.getElementById('newAction'+v).disabled=true;
		document.getElementById('newFile'+v).checked=false;
		document.getElementById('newFile'+v).disabled=true;		
	}
}

function inputText(val,obj){
	if(val=='Caption...' || val=='Action...'){
		obj.value='';
	}
}

function saveMenu(parent,caption,caption2,caption3,action,showlink,hideinput,type,newfile){
	objToHide=document.getElementById(showlink);
	objToShow=document.getElementById(hideinput);
	objType=document.getElementById(type);
	id_parent=document.getElementById(parent).value;
	_caption=document.getElementById(caption).value;
	_caption2=document.getElementById(caption2).value;
	_caption3=document.getElementById(caption3).value;
	_action=document.getElementById(action).value;
	clas=document.getElementById(type).options[document.getElementById(type).selectedIndex].text;
	createFile=document.getElementById(newfile).checked?'yes':'no';
	if(clas=='devider'){
		_caption='---------';
	}
	
	if(_caption2==''){
		_caption2='unspesified';
	}
	
	if(_caption3==''){
		_caption3='unspesified';
	}	
	
	if (clas == 'Type...'){
		alert('Choose type..!');
		objType.focus();
	}else if (clas == 'click' && (_caption=='Caption...' || _action=='Action...')){
		alert('Fill the title and/or action');
	}else if (clas == 'title' && _caption=='Caption...'){
		alert('Fill the title and/or action');
	}else{
		if(confirm('Are you sure ...')){
			param='id_parent='+id_parent+'&caption='+_caption+'&action='+_action+'&class='+clas+'&create='+createFile+'&caption2='+_caption2+'&caption3='+_caption3+'&method=saveMenu';
			post_response_text('slave_menusettingbi.php', param, respog);
		}
	}
	
	function respog(){
		if(con.readyState==4){
			if(con.status==200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert('ERROR TRANSACTION,\n'+con.responseText);
					hideObject(objToShow);
                    showObject(objToHide);
				}

				if(con.responseText.lastIndexOf('Warning') > -1){
					alert('ERROR TRANSACTION,\n'+con.responseText);
					showObject(objToHide);
                    showObject(objToShow);
				}else{
					arr=con.responseText.split(',');
					
					_id=parseInt(arr[0]);
                    _ischildable=arr[1];
                    max_id=_id;					
                    _in=document.getElementById('group'+id_parent).innerHTML;
					

				   if(_ischildable=='stop'){
						_in+="<li>";
						if(clas == 'title' || clas=='devider'){
							_in+="<a class=lab id=lab"+_id+" onclick=edit('"+_id+"') title='Click to Change'>"+_caption+"</a><a id=edit"+_id+"></a>";	
						}else{
							_in+="<a class=lab id=lab"+_id+" onclick=edit('"+_id+"') title='Click to Change'>"+_caption+"</a><a id=edit"+_id+"></a>";
						}
						
						_in+="<input class=cbox type=checkbox id=check"+_id+" onclick=\"activate('"+_id+"');\" title='Click to Display!'>";
						_in+="&nbsp &nbsp <img class=dellicon title='Delete!' src='images/menu/delete1.jpg' onclick=\"delet('"+_id+"');\" id=img"+_id+">";						
					}else{
						_in+="<li class=mmgr>";
						if(clas == 'title' || clas=='devider'){
							_in+="<img  src='images/menu/arrow_10.gif'> ";
							_in+="<a class=lab id=lab"+_id+" onclick=edit('"+_id+"') title='Click to Change'>"+_caption+"</a><a id=edit"+_id+"></a>";	
						}else{
							_in+="<img  src='images/foldc_.png' onclick=show_sub('child"+_id+"',this); class=arrow title='Expand' height=17px> ";
							_in+="<a class=lab id=lab"+_id+" onclick=edit('"+_id+"') title='Click to Change'>"+_caption+"</a><a id=edit"+_id+"></a>";
						}

						_in+="<input class=cbox type=checkbox id=check"+_id+" onclick=\"activate('"+_id+"');\" title='Click to Display!'>";
						_in+="&nbsp &nbsp <img class=dellicon title='Delete!' src='images/menu/delete1.jpg' onclick=\"delet('"+_id+"');\" id=img"+_id+">";
						_in += "<ul id=child" + _id + " style='display:none;'><div id=group" + _id + "></div>";
						_in += "<li><div id=inputmenu" + _id + " class=menuinput  style='display:none;'>";
						_in += "<select id=type" + _id + " onchange=checkType('" + _id + "',this)>";
						_in += "<option>Type...</option><option>click</option><option>title</option><option>devider</option>";
						_in += "</select>";
						_in += "<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption" + _id + " size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>";
						_in += "<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption2" + _id + "x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>";
						_in += "<input type=text value='Caption...' maxlength=40 class=myinputtext title='Text to be shown on menu' id=newCaption3" + _id + "x size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>";
						_in += "<input type=text value='Action...' maxlength=40 class=myinputtext title='Filename (without extension) that will be execute when menu clicked' id=newAction" + _id + " size=12 onkeypress=\"return tanpa_kutip(event);\" onfocus=inputText(this.value,this) onblur=leaveText(this.value,this)>";
						_in += "<input type=checkbox  title='Check to create this file' id=newFile" + _id +"><font color=white>Create File</font>";
						_in += "<input type=hidden id=master_menu" + _id + " value=" + _id + ">";
						//_in += "<center>";
						_in += "<input type=button class=mybutton value=Save onclick=saveMenu('master_menu" + _id + "','newCaption" + _id + "','newCaption2" + _id + "x','newCaption3" + _id + "x','newAction" + _id + "','link" + _id + "','inputmenu" + _id + "','type" + _id + "','newFile"+ _id +"');>";
						_in += "<input type=button class=mybutton value=Close onclick=showById('inputmenu" + _id + "','link" + _id + "')>";
						//_in += "</center>";
						_in += "</div>";
						_in += "<a class=newMenu title='Create New Link' id=link" + _id + " onclick=\"javascript:hideObject(this);showObject(document.getElementById('inputmenu" + _id + "'));\">New</a></li>";
						_in += "</ul>";
					}
					
					document.getElementById('group'+id_parent).innerHTML=_in;
					document.getElementById(caption).value='Caption...';
					document.getElementById(action).value='Action...';					
					objToHide.style.display='';
					objToShow.style.display='none';
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function show_sub(id,obj){
	if(document.getElementById(id).style.display == 'none'){
		document.getElementById(id).style.display = '';
		obj.src='images/foldo.png';
		obj.setAttribute('title','Collaps');
	}else{
		document.getElementById(id).style.display = 'none';
		obj.src='images/foldc.png';
		obj.setAttribute('title','Expand');
	}
}

function activate(menu_id){
	obj=document.getElementById('check'+menu_id);
	if(obj.checked){
		param='setHide=0&id='+menu_id;
	}else{
		param='setHide=1&id='+menu_id;	
	}
	
	param+='&method=activate'
	
	document.getElementById('lab'+menu_id).style.backgroundColor='#E36707';
    post_response_text('slave_menusettingbi.php', param, respog);
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					if(obj.checked){
						obj.checked = false;
                        obj.setAttribute('title', 'Click to Activate');
					}else{
						obj.checked = true;
                        obj.setAttribute('title', 'Click to deActivate');
					}
					alert(con.responseText);
				}else{
					if(obj.checked){
						obj.setAttribute('title', 'Click to deActivate');
					}else{
						obj.setAttribute('title', 'Click to Activate');
					}
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
			
			//set back the tex backgroud
            document.getElementById('lab'+menu_id).style.backgroundColor='#FFFFFF';	
		}	
	}	
}

function delet(m_id){
	obj=document.getElementById(m_id);
    document.getElementById('lab'+m_id).style.backgroundColor='#E36707';
	
	param='id='+m_id+'&method=delet';
    
	if(confirm('Are you sure deleting this menu....?')){
		post_response_text('slave_menusettingbi.php', param, respog);
	}else{
		document.getElementById('lab'+m_id).style.backgroundColor='#FFFFFF';
	}
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					new_item=parseInt(m_id);
                    clearFormEdit('edit'+m_id);
                    document.getElementById('lab'+m_id).innerHTML='<i style=\'background-color:#FF0000;\'>deleted</i>';
                    document.getElementById('img'+m_id).style.display='none';
                    document.getElementById('check'+m_id).style.display='none';
					
					try{
						document.getElementById('inputmenu' + m_id).style.display = 'none';
					}catch(e){}//do nothing on eror
					
					try{
						document.getElementById('link' + new_item).innerHTML = 'Closed';
					}catch(e){}//do nothing on eror
					
					try{
						document.getElementById('link' + new_item).setAttribute('onclick', 'alert(\'This link has been closed\');');
					}catch(e){}//do nothing on eror
					
					try{
						document.getElementById('link' + new_item).setAttribute('title', 'This link has been closed');
					}catch(e){}//do nothing on eror
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
			
			//set back the tex backgroud
			document.getElementById('lab'+m_id).style.backgroundColor='#FFFFFF';	
		}	
	}		
}

function leaveText(val,obj){
	if(val=='' || val==''){
		if(obj.id.lastIndexOf('Caption')>-1){
			obj.value='Caption...';
		}else{
			obj.value='Action...';	
		}
	}
}

function clearFormEdit(objid){
	document.getElementById(objid).innerHTML='';
}

function showMenuOrder(){
	ctrl=document.getElementById('optionController');
    if (ctrl.innerHTML == 'Order Arrangement'){
		collapsAll();
		ctrl.innerHTML = 'Menu Settings';
		ctrl.setAttribute('title','Click to manage menu settings');
		document.getElementById('menuSettingContainer').style.display = 'none';
		document.getElementById('menuOrderContainer').style.display='';	
	}else{
		collapsAllOrder();
		ctrl.innerHTML = 'Order Arrangement';
		ctrl.setAttribute('title','Click to manage menu order');
		document.getElementById('menuOrderContainer').style.display='none';	
		document.getElementById('menuSettingContainer').style.display = '';
	}
}

function collapsAll(){
	for(x=0;x<=max_id;x++){
		try{
			document.getElementById('child'+x).style.display='none';
       }catch(e){}
	}	
}

function collapsAllOrder(){
	for(x=0;x<=max_id;x++){
		try{
			document.getElementById('orderchild'+x).style.display='none';
       }catch(e){}
	}	
}

function edit(_id){
	param='id='+_id+'&method=edit';
    document.getElementById('lab'+_id).style.backgroundColor='#E36707';
	
	post_response_text('slave_menusettingbi.php', param, respog);
	showObject(document.getElementById('edit'+_id));	
    
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('edit'+_id).innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
			
			//set back the tex backgroud
            document.getElementById('lab'+_id).style.backgroundColor='#FFFFFF';	
		}	
	}		
}

function showObject(obj){
	for(x=0;x<=max_id;x++){
		vx='inputmenu'+x;
        vy='link'+x;
        vz='edit'+x;
        try{
			//try onebyone
            document.getElementById(vx).style.display='none';
		}catch(e){}
		
		try{ 
			//try onebyone
			document.getElementById(vz).innerHTML='';		 	
		}catch(e){}
		
		try{
			//try onebyone
			if(obj!=document.getElementById(vx)){
				document.getElementById(vy).style.display='';
			}else{
				document.getElementById(vy).style.display='none';
			}
		}catch(e){}	
	}
	obj.style.display='';
}

function saveEditedMenu(id){
	newCaption=document.getElementById('editcaption'+id).value;
	newCaption2=document.getElementById('editcaption2'+id+'x').value;
	newCaption3=document.getElementById('editcaption3'+id+'x').value;
	newAction=document.getElementById('editaction'+id).value;
	
	param='id='+id+'&caption='+newCaption+'&action='+newAction+'&caption2='+newCaption2+'&caption3='+newCaption3+'&method=saveEditedMenu';
	
	if(document.getElementById('editaction'+id).disabled){
		newAction='';
	}
	
	if (confirm('Are you sure changing the menu?')){
		post_response_text('slave_menusettingbi.php', param, respog);
		document.getElementById('lab'+id).style.backgroundColor='#E36707';
	}else{
		clearFormEdit('edit' + id);	
	}
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('lab'+id).innerHTML=newCaption;
					clearFormEdit('edit'+id);
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
			
			//set back the tex backgroud
			document.getElementById('lab'+id).style.backgroundColor='#FFFFFF';	
		}	
	}	
}

function showEditor(id,sub,e){
	document.getElementById('ordereditorcontent').innerHTML='';
	pos= new Array();
    pos=getMouseP(e);
    
	// 	Get id submenu 
	param='parent='+id+'&sub='+sub+'&method=showEditor';
	post_response_text('slave_menusettingbi.php', param, respog);
	document.getElementById('orderlab'+id).style.backgroundColor='#E36707';
   
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//Displays order editor
                    document.getElementById('ordereditor').style.top=pos[1]+'px';
                    document.getElementById('ordereditor').style.left=pos[0]+'px';
                    document.getElementById('ordereditor').style.display='';
                    document.getElementById('ordereditorcontent').innerHTML=con.responseText;
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
			
			//set back the tex backgroud
			document.getElementById('orderlab'+id).style.backgroundColor='#FFFFFF';	
		}	
	}
}

function change(dest,x,mx){
	x=parseInt(x);
	mx=parseInt(mx);
	
	if(dest=='up' && (x-1)==0){
		alert('It is on top');
	}else if(dest=='down' && (x-1)>mx){
		alert('It is at te bottom');
	}else{
		if(dest=='up'){
			y=x-1;
		}else{
			y=x+1;	
		}
		
		ox=document.getElementById('orderurut'+x).innerHTML;
		oy=document.getElementById('orderurut'+y).innerHTML;
		fromId=document.getElementById('orderid'+x).innerHTML;
		toId=document.getElementById('orderid'+y).innerHTML;
		
		function respog(){
			if(con.readyState==4){
				if(con.status == 200){
					busy_off();
                    if(!isSaveResponse(con.responseText)){
						alert(con.responseText);
					}else{
						//if success, then change display
                        cangeOrderDisplay();	
					}
				}else{
					busy_off();
                    error_catch(con.status);
				}
			}	
		}

		param='from='+fromId+'&to='+toId+'&orderfrom='+ox+'&orderto='+oy+'&method=change';
		post_response_text('slave_menusettingbi.php', param, respog);
		
		function cangeOrderDisplay(){
			xid=document.getElementById('orderid'+x);
			xtype=document.getElementById('ordertype'+x);
			xcaption=document.getElementById('ordercaption'+x);
			xaction=document.getElementById('orderaction'+x);
			xurut=document.getElementById('orderurut'+x);
			
			yid=document.getElementById('orderid'+y);
			ytype=document.getElementById('ordertype'+y);
			ycaption=document.getElementById('ordercaption'+y);
			yaction=document.getElementById('orderaction'+y);
			yurut=document.getElementById('orderurut'+y);
			
			//penampungan
			xoid=xid.innerHTML;
			xotype=xtype.innerHTML;
			xocaption=xcaption.innerHTML;
			xoaction=xaction.innerHTML;
			xourut=xurut.innerHTML; 
			
			//replace//change positon
			xid.innerHTML     =yid.innerHTML;
			xtype.innerHTML   =ytype.innerHTML;
			xcaption.innerHTML=ycaption.innerHTML;
			xaction.innerHTML =yaction.innerHTML;
			//  xurut.innerHTML   =yurut.innerHTML; 
			yid.innerHTML     =xoid;
			ytype.innerHTML   =xotype;
			ycaption.innerHTML=xocaption;
			yaction.innerHTML =xoaction;
			//  yurut.innerHTML   =xourut;
		}	  	
	}
}

function closeOrderEditor(){
	document.getElementById('ordereditorcontent').innerHTML='';
	document.getElementById('ordereditor').style.display='none';  	
}

function expandAll(){
	for(x=0;x<=max_id;x++){
		try{
			document.getElementById('child'+x).style.display='';
       }catch(e){}
	}		
}

function expandAllOrder(){
	for(x=0;x<=max_id;x++){
		try{
			document.getElementById('orderchild'+x).style.display='';
       }catch(e){}
	}		
}

//========= USER PRIVILIGES ==========
function setMapUserMenu(ev,rowobj,uname){
	rowobj.style.backgroundColor='#E36707';
	pos=getMouseP(ev);
	param='uname='+uname+'&method=setMapUserMenu';
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					//alert(con.responseText);
                    document.getElementById('contentmenu').innerHTML=con.responseText;
                    document.getElementById('ctrmenu').style.display='';
                    document.getElementById('ctrmenu').style.top=pos[1]+'px';
                    document.getElementById('ctrmenu').style.left=pos[0]+'px';
                    rowobj.style.backgroundColor='#E8F2FE';//class standardrow color
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}	
	}
	post_response_text('slave_userprivillagesbi.php', param, respog);	
}

function resetDetailPrivillage(uname){
	param='uname='+uname+'&method=resetDetailPrivillage';
	
	function respog(){
		if(con.readyState==4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					clearCheckBox();
				}
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}
	if(confirm('Are yous sure clearing '+uname+' Privillage..?'))	 
		post_response_text('slave_userprivillagesbi.php', param, respog);		
}

function clearCheckBox(){
	for (x = 0; x <= max_id; x++) {
		vz = 'cx' + x;
		
		try { 
			//try onebyone
            document.getElementById(vz).checked = false;
		}catch (e) {}
	}
}

function changePrivillage(menuid,uname,obj){
	if(obj.checked)
		action='add';
	else
		action='remove';
	
	document.getElementById('orderlab'+menuid).style.backgroundColor='#E36707';	
    param='uname='+uname+'&menuid='+menuid+'&action='+action+'&method=changePrivillage';		
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
                    if(obj.checked)
						obj.checked=false;
					else
						obj.checked=true;
				}else{}
			}else {
				busy_off();
                error_catch(con.status);
			}
		}
		document.getElementById('orderlab'+menuid).style.backgroundColor='#FFFFFF';		  	
	}
    post_response_text('slave_userprivillagesbi.php', param, respog);	
}