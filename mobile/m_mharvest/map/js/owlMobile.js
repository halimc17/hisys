/**
 * @author repindra.ginting
 */
 //option Action Menu //===================================
 // all type to lower case
 var actRolePermission = {
							'sync':{
								'style'	: '',
								'text'	: '{sync_title}',
								'icon'	: 'action_sync',
							},
							'ok':{
								'style'	: '',
								'text'	: '{ok_title}',
								'icon'	: 'action_ok',
							},
							'cancel':{
								'style'	: '',
								'text'	: '{cancel_title}',
								'icon'	: 'action_cancel',
							},
							'approve':{
								'style'	: '',
								'text'	: 'Approve',
								'icon'	: 'action_approve',
							},
							'unapprove':{
								'style'	: '',
								'text'	: 'Unapprove',
								'icon'	: 'action_unapprove',
							},
							'sign':{
								'style'	: '',
								'text'	: '{sign_title}',
								'icon'	: 'action_sign',
							},
							'pin':{
								'style'	: '',
								'text'	: '{pin_title}',
								'icon'	: 'action_pin',
							},
							'detail':{
								'style'	: '',
								'text'	: '{detail_title}',
								'icon'	: 'action_detail',
							},
							'edit':{
								'style'	: '',
								'text'	: '{edit_title}',
								'icon'	: 'action_edit',
							},
							'view':{
								'style'	: '',
								'text'	: '{view_title}',
								'icon'	: 'action_view',
							},
							'print':{
								'style'	: '',
								'text'	: '{print_title}',
								'icon'	: 'action_print',
							},
							'hapus':{
								'style'	: '',
								'text'	: '{hapus_title}',
								'icon'	: 'action_hapus',
							},
							
							'more':{
								'style'	: '',
								'text'	: '{more_title}',
								'icon'	: 'action_more',
							}
						};
 
 //===================================
 /***  
Author : Atwal Arifin  
tahun  : 2019 
Definisi mobile 
 ***/
var isMobile = {
    Android: function() {
        return navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function() {
        return navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function() {
        return navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function() {
        return navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function() {
        return navigator.userAgent.match(/IEMobile/i) || navigator.userAgent.match(/WPDesktop/i);
    },
    any: function() {
        return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
    }
};
 if(typeof language === 'undefined'){
	var language = {};
 }
if(!window.openDatabase){
	notifAlert('Your browser does not support SQL Lite','{error}')
} else{
	try{
		var db = openDatabase('owlMobile', '1.0', 'OWL Mobile Database', 10 * 512);
	}catch(e){
		notifAlert(e.message,'{error}');
	}
}
var FILE='owlMobile.php';// destination file on server

function createXMLHttpRequest() {
   try { return new ActiveXObject("Msxml2.XMLHTTP"); } 
   catch (e) {}
   try { return new ActiveXObject("Microsoft.XMLHTTP"); } 
   catch (e) {}
   try { return new XMLHttpRequest(); } 
   catch(e) {}
   notifAlert("XMLHttpRequest Tidak didukung oleh browser",'{error}');
   return null;
 }

var con = createXMLHttpRequest();
function post_response_text(tujuan,param,functiontoexecute){
	if(onOnline()){
		// check connection
		//par=window.location.href.replace("http://","");
		//par=par.replace("https://","");
		//param+='&par='+par;  
		con.open("POST", tujuan, true);
		con.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		console.log(param);
		con.setRequestHeader("Content-length", param.length);
		con.setRequestHeader("Connection", "close");
		showProgress();
		con.onreadystatechange = eval(functiontoexecute);
		con.send(param);
	}
}
function post_response_textGPS(tujuan,param,functiontoexecute)
{
	if(onOnline()){
		con.open("POST", tujuan, true);
		con.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		con.setRequestHeader("Content-length", param.length);
		con.setRequestHeader("Connection", "close");
		con.onreadystatechange = eval(functiontoexecute);
		con.send(param);
	}
}
function readXml(xmlFile){
	var xmlDoc;
	if(typeof window.DOMParser != "undefined") {
		xmlhttp=new XMLHttpRequest();
		xmlhttp.open("GET",xmlFile,false);
		if (xmlhttp.overrideMimeType){
			xmlhttp.overrideMimeType('text/xml');
		}
		xmlhttp.send();
		xmlDoc=xmlhttp.responseXML;
	}else{
		xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
		xmlDoc.async="false";
		xmlDoc.load(xmlFile);
	}
	return xmlDoc;
}
function error_catch(x)
{
	hideProgress();
	switch (x){
      case 203:
		notifAlert('Dibutuhkan Authority','{error}');
	  break;
	  case 400:
		notifAlert('Error Server','{error}');
	  break;
	  case 403:
		notifAlert('Anda dilarang masuk','{error}');
	  break;
	  case 404:
		notifAlert('File tidak ditemukan','{error}');
	  break;
	  case 405:
		notifAlert('Method tidak diijinkan','{error}');
	  break;
	  case 407:
		notifAlert('Proxy Error','{error}');
	  break;
	  case 408:
		notifAlert('Permintaan terlalu lama','{error}');
	  break;
	  case 409:
		notifAlert('Query Conflict','{error}');
	  break;
	  case 414:
		notifAlert('ULI terlalu panjang','{error}');
	  break;
	  case 412:
		notifAlert('Variable terlalu banyak','{error}');
	  break;
	  case 415:
		notifAlert('Unsupported Media Type','{error}');
	  break;
	  case 500:
		notifAlert('Server busy, try submit later','{error}');
	  break;
	  case 502:
		notifAlert('Bad gateway','{error}');
	  break;
	  case 505:
	  notifAlert('Browser anda terlalu tua','{error}');	    
      break;
	}
}


function isSaveResponse(txt)
{
	txt=txt.toUpperCase();
	if (txt.lastIndexOf('GAGAL') > -1 || txt.lastIndexOf('ERROR') > -1 || txt.lastIndexOf('WARNING') > -1)
      return false
	else
	  return true;  
}


function addNumberOnlyAction(obj){
	obj.removeAttribute("onkeypress");
	obj.setAttribute("onkeypress","return angka_doang(event)");
	obj.removeAttribute("onPaste");
	obj.setAttribute("onPaste","return false");
	obj.removeAttribute("autocomplete");
	obj.setAttribute("autocomplete","off");
}
//=============================================================
function getKey(e)//get key code e is event
{
        var key;
        if(window.event) {
               // for IE, e.keyCode or window.event.keyCode can be used
               key = e.keyCode;
        }
        else if(e.which) {
               key = e.which;
        }
        else {
               // no event, so pass through
               return true;
        }
      return key;
}
//========================================================================
function tanpa_kutip(e)//block quote and doublequote e is event
{
  key=getKey(e);
  if(key==39 || key==34 || key==38)
  return false;
  else
  return true;
}
function char_only(e)
{
  key=getKey(e);
  if((key <65 || key>122) && (key!=true && key!=32 && key!=8))
  return false;
  else
  return true;  	
}

function charAndNum(e)
{
  key=getKey(e);
  if((key <48 || key>122) && (key!=8 && key!=127 && key!=47 && key!=32 && key!=true))
  return false;
  else
  return true;  	
}
function charAndNumAndStrip(e)
{
  key=getKey(e);
  if((key <48 || key>122) && (key!=8 && key!=127 && key!=47 && key!=32 && key!=true&& key!=45))
  return false;
  else
  return true;  	
}

//===========================================================================
function angka_doang(e)//only numeric e is event
{
 key=getKey(e);
 if((key<48 || key>57) && (key!=8 && key!=46  && key!=127 && key!=true))
  return false;
 else
 {
     return true;
 }
}
function charIs(e)
{
 key=getKey(e);
 return (String.fromCharCode(key));	
}
//=============================================================================
function tanpa_kutip_dan_sepasi(e)//block quote and doublequote and space e is event
{
 key=getKey(e);
 if(key==39 || key==34 || key==38 || key==32)
    return false;
 else
    return true;
}

function getScreen(){
	//var doc=[document.body.clientHeight,document.body.clientWidth];
    var doc=[window.innerHeight-120,window.innerWidth-20]
  //var doc=[screen.availHeight,screen.availWidth];
	return doc;//Height, Width
}

function getValue(id) {
    var tmp = document.getElementById(id);
    if(!tmp) {
        notifAlert("DOM Definition Error : "+id,'{error}');
        return false;
    }
    if(tmp.getAttribute('type')=='checkbox') {
                if(tmp.checked==true) {
            return 1;
        } else {
            return 0;
        }
    } else if(tmp.options) {
			if(tmp.options[tmp.selectedIndex]) {
					return tmp.options[tmp.selectedIndex].value;
			} else {
					return false;
			}
	} else if(tmp.getAttribute('type')=='text') {
			return tmp.value;
	} else if(tmp.getAttribute('type')=='textarea') {
			return tmp.value;
	} else if(tmp.getAttribute('type')=='button') {
			return tmp.value;
	} else if(tmp.hasAttribute('value')) {
			if(tmp.getAttribute('value')!='') {
					return tmp.getAttribute('value');
			} else {
					return tmp.value;
			}
    } else {
              if(tmp.innerHTML!='')
            {return tmp.innerHTML;}
                  else
             {return tmp.value;	}	
    }
}

/* Function getAttr
 * Fungsi mengambil value attribute dari object
 * I : id element
 * O : nilai
 */
function getAttr(id, attrName) {
        return getById(id).getAttribute(attrName);
}

/* Function getInner
 * Fungsi mengambil innerHTML dari object
 * I : id element
 * O : nilai
 */
function getInner(id) {
        return getById(id).innerHTML;
}

/* Function getById
 * Fungsi mengambil object berdasar ID
 * I : id element
 * O : object
 */
function getById(id) {
	if(typeof document.getElementById(id) != "undefined") {
		return document.getElementById(id);
	} else {
		if(console) {
			console.log("DOM Definition Error: "+id,'{error}');
		} else {
			notifAlert("DOM Definition Error: "+id,'{error}');
		}
		return false;
	}
}

/**
 * setValue
 * Set Value to spesific element
 * @param	string	id		String ID of target element
 * @param	string	value	Value to be set
 */
function clearLocalStorage(){
    return localStorage= null;
}
function setValue(id, value) {
        var el = getById(id);
        if(el) {
                if(el.options) {
                        for(i in el.options) {
                                if(el.options[i].value==value) {
                                        el.selectedIndex = i;
                                }
                        }
                } else {
                        el.value = value;
                }
        }
        return el;
}

function trim(stringToTrim){//trim space not support by IE
    retval=stringToTrim.replace(/^\s+|\s+$/g, "");
	return (retval);
}

function clearSpace(str){
	str = str.replace(/\s/g,'');
	return str;
}

var timer;
var z=0
var panelId='';

function resizeAllPanel(){
	obj=document.getElementsByClassName('panel');
	for(x=0;x<obj.length;x++){
			//obj[x].style.width=(getScreen()[1]-5)+'px';
			//obj[x].style.height=(getScreen()[0]-45)+'px';			
	}
	//#recenter progress icon
	obj1=document.getElementsByClassName('progress');
	ele = obj1[0];
	img = document.getElementsByTagName('img');
	//wEle = img[0].clientWidth;
	hEle = img[0].clientHeight;
	//ele.style.paddingLeft=(parseInt(getScreen()[1]/2)-(wEle/2))+'px';
	//ele.style.paddingRight=(parseInt(getScreen()[1]/2)-(wEle/2))+'px';
	ele.style.paddingTop=(parseInt(getScreen()[0]/2)-(hEle/2))+'px';

	//for scrollable data
	obj2=document.getElementsByClassName('scrollable');
	for(x=0;x<obj2.length;x++){
			obj2[x].style.width=parseInt(getScreen()[1]-(5/100)*getScreen()[1])+'px';
			//obj[x].style.height=(getScreen()[0]-120)+'px';			
	}	
	/*/for scrollableInner data (dibuang sementara, di gantikan CSS)
	obj3=document.getElementsByClassName('scrollableInner');
	for(x=0;x<obj3.length;x++){
		obj3[x].style.width=parseInt(getScreen()[1]-(15/100)*getScreen()[1])+'px';			
	}	
	*/
}
//* Remove Element ex: document.getElementById("my-element").remove();
Element.prototype.remove = function() {
    this.parentElement.removeChild(this);
}
NodeList.prototype.remove = HTMLCollection.prototype.remove = function() {
    for(var i = this.length - 1; i >= 0; i--) {
        if(this[i] && this[i].parentElement) {
            this[i].parentElement.removeChild(this[i]);
        }
    }
}
function removeAll(elements){
	allElements = document.getElementsByClassName(elements);
	if(allElements.length > 0){
		allElements[0].remove();
		removeAll(elements);
	}
}
/* LPAD */
String.prototype.lpad= function(len, c){
    var s= '', c= c || '0', len= (len || 2)-this.length;
    while(s.length<len) s+= c;
    return s+this;
}
Number.prototype.lpad= function(len, c){
    return String(this).lpad(len,c);
}
/*
Ex: LPAD
var str = "5";
alert(str.lpad("0", 4)); //result "0005"
var str = "10"; // note this is string type
alert(str.lpad("0", 4)); //result "0010"

 END:
/* Atwal - sementara 
function action_table(ele,actionArray){

	
	function find_act(name,action)
	{
		switch (name){
		  case 'detail':
			html = "<img src=images/edit.png title=edit pp class=buttonLine " + action + ">";
			return html;
		  break;
		  case 'verify':
			html = "<img src=images/edit.png title=edit pp class=buttonLine " + action + ">";
			return html;
		  break;
		  case 'edit':
			html = "<img src=images/edit.png title=edit pp class=buttonLine " + action + ">";
			return html;
		  break;
		  case 'delete':
			html = "<img src=images/edit.png title=edit pp class=buttonLine " + action + ">";
			return html;
		  break;
		  
		}
	}
	var actNumJsn = actionArray.length;
	var html_act = "";
	for(i=0; i<actNumJsn; i++){
		html_act += find_act(actionArray[i].actname,actionArray[i].act);
	}
	var both = document.getElementById(ele);
	if(both.getElementsByClassName('jumbotron')[0]){
		var jumbotron = both.getElementsByClassName('jumbotron')[0];
		if(jumbotron.getElementsByClassName('Action_table')[0]){
			jumbotron.getElementsByClassName('Action_table')[0].remove();
			var div = document.createElement('div');
			div.setAttribute('class','Action_table');
			div.style.display = 'block';
			jumbotron.appendChild(div);
		}else{
			var div = document.createElement('div');
			div.setAttribute('class','Action_table');
			div.style.display = 'block';
			div.style.position = 'relative';
			div.innerHTML = html_act;
			jumbotron.appendChild(div);
		}
	}
}
*/
function setDimention(width,height,id){
  obj=document.getElementById(id);
  obj.style.height=height+'px';
  obj.style.width=width+'px';
  return true;
}
function toggle(id,command){
	var ele = document.getElementById(id);
	if(command){
		ele.style.display = command;
	}else{
		var display_ele = ele.style.display;
		if(display_ele.toUpperCase() == 'BLOCK'){
			ele.style.display = "none";
		}else{
			ele.style.display = "block";
		}
	}
}
function toggle_password(e,target){
	function swapInput(tag, type) {
		var el = document.createElement('input');
		el.id = tag.id;
		el.type = type;
		el.name = tag.name;
		el.value = tag.value; 
		tag.parentNode.insertBefore(el, tag);
		tag.parentNode.removeChild(tag);
	}
	var d = document;
	var tag = d.getElementById(target);
	var status = e.getAttribute('status');
	var hidetext =  e.getAttribute('hidetext');
	if (status == 'Show'){
		swapInput(tag,'text');
		e.setAttribute('status','Hide');
		e.setAttribute('hidetext',e.innerHTML);
		e.innerHTML = hidetext;
	}else{
		swapInput(tag,'password');
		e.setAttribute('status','Show');
		e.setAttribute('hidetext',e.innerHTML);
		e.innerHTML = hidetext;
	}
}
function showProgress(){
	obj=document.getElementsByClassName('progress');
	if(obj.length > 0){
		obj[0].style.display='block';
	}
}
function hideProgress(){
	obj=document.getElementsByClassName('progress');
	if(obj.length > 0){
		obj[0].style.display='none';
		obj[0].setAttribute("class","progress");
	}
}
/*
function displayMainMenu(){
  getLoginInfo();
  dRF=document.getElementById('dropDown');
  if(dRF.style.display=='none' || dRF.style.display.toString()=='undefined' || dRF.style.display==''){
    dRF.style.display='inline-block';
  }else{
    dRF.style.display='none';
  }
}
*/
//1 Show Menu SideBar @author Atwal
function displayMainMenu(){
 // getLoginInfo();
	var sidebar_id = 'owl-sidebar';
	
	overlay = document.createElement("div");
	overlay.setAttribute('id','overlay');
	overlay.setAttribute('class','overlay');
	overlay.setAttribute('onclick','closeMainMenu()');
	overlay.style.opacity =0;
	//overlay.style.width = (parseInt(getScreen()[1]-280))+'px';
	document.body.insertBefore(overlay, mainmenu);
	
	var mainmenu = document.getElementById(sidebar_id);
	var animate_open = window.setInterval(open_menu,1);
	var animate_overlay = window.setInterval(_overlay,1);
	function open_menu(){
		var obj = document.getElementById(sidebar_id);
		var curLeft_obj = obj.offsetLeft;
		var x = 7;
		curLeft_obj = curLeft_obj + x;
		if(curLeft_obj <= 0){
			obj.style.left=curLeft_obj+'px';
		}else{
			obj.style.left= 0+'px';
			clearInterval(animate_open);
		}
	}
	function _overlay(){
		var obj = document.getElementById('overlay');
		var curOpa_obj = window.getComputedStyle(obj).getPropertyValue("opacity");
		var x = 0.1;
		curOpa_obj = Number(curOpa_obj) + Number(x);
		
		if(curOpa_obj < 1){
			obj.style.opacity= curOpa_obj;
		}else{
			obj.style.opacity= 1;
			clearInterval(animate_overlay);
		}
	}
	
}
function stopPropaganda(e){
	if (!e)
		e = window.event;
    //IE9 & Other Browsers
    if (typeof e !== 'undefined') {
		if (e.stopPropagation){
			e.stopPropagation();
		}else {
		  e.cancelBubble = true;
		}
    }
    //IE8 and Lower
    
}
//1 End;
if(typeof sessionStorage.oldlocation=='undefined'){
	sessionStorage.oldlocation="HOME"; //first
}else{
	sessionStorage.oldlocation="HOME"; //first
}
if(typeof sessionStorage.panel=='undefined'){
	sessionStorage.panel="home"; //first
}else{
	sessionStorage.panel="home"; //first
}
function frame_panel(id,title,e,newFunction){

	ev = this.event;
	stopPropaganda(ev);
	var urlForm = "forms/"+id+".html";
	if(e && e.nodeType){
		formjs = e.getAttribute("formjs");
		urlformAttr = e.getAttribute("urlform");
		if(urlformAttr != null){
			urlForm = urlformAttr;
		}
		//console.log(formjs);
		if(formjs != null){
			create_script(id,formjs,function(){
				create_frame(id,title,e,newFunction,urlForm);
			});
		}else{
			create_frame(id,title,e,newFunction,urlForm);
		}
	}else{
		create_frame(id,title,e,newFunction,urlForm);
	}
}

function create_frame(id,title,elem,newFunction,urlformAttr){
	var urlForm = urlformAttr;
	//showProgress();
	function frameLoadAction(id,title,elem,newFunction,ele){
		goTransition(id);
		//console.log('create_frame 1');
		if(elem){
			var textString = "";
			if(typeof elem ==='string'){
				if(typeof newFunction !== 'undefined'){
					//console.log('create_frame 1');
					if(typeof newFunction == 'function'){
						eval(newFunction());
					}else{
						stringToFunction(newFunction);
					}
				}
				scaningElem(ele);
			}else if(typeof elem ==='function'){
				//console.log('create_frame 2');
				eval(elem);
				if(typeof newFunction !== 'undefined'){
					if(typeof newFunction == 'function'){
						eval(newFunction());
					}else{
						stringToFunction(newFunction);
					}
				}
				scaningElem(ele);
			}else{
				//console.log(typeof elem);
				if(typeof elem !== 'undefined' && typeof elem === 'object' && elem.getAttribute('newfunction') != null){
					var func = elem.getAttribute('newfunction');
					var arguments = elem.getAttribute('data-param');
					var param = "";
					var coma = ",";
					if(func && func !== ''){
						if(document.getElementById(id)){
							document.getElementById(id).setAttribute('newfunction',func);
							document.getElementById(id).setAttribute('data-param',arguments);
							executeFunctionByName(func, window, arguments);
						}
						var argText = [];
						var namespaces = [];
						if(arguments && arguments !== ''){
							argText = arguments.split(',');
							namespaces = func.split(".");
							for(i=0; i<argText.length; i++){
								if(i==argText.length-1){coma="";}
								param += "'"+argText[i]+"'"+coma;
							}
						}
						var onclicked = func+"("+param+");";
						var refresh_id = document.getElementById('Refresh_'+id);
						refresh_id.setAttribute('onclick',onclicked);
						scaningElem(ele);
						
					}
				}
			}
		}else{
			//console.log('create_frame 4');
			if(typeof newFunction !== 'undefined'){
				if(typeof newFunction == 'function'){
					eval(newFunction());
				}else{
					stringToFunction(newFunction);
				}
			}
			scaningElem(ele);
			//hideProgress();
		}
	}
	
	if(sessionStorage.lockaApp === "LOCK"){
		checkTidakPernahSyncData();
	}
	var node = document.createElement("div");
	var header = '<div id="'+id+'jumbotronpannel" class="jumbotron">';
	if(id == 'panelLogin' && sessionStorage.username == "" && sessionStorage.password == ""){
		header += '';
	}else{
		//header += '<a class="pull-left" onclick="closePanel(\''+id+'\');"><span class="icon-set menu-back-arrow"></span></a>';
		header += '<a class="back pull-left" onclick="closePanel(\''+id+'\');"></a>';
	}
	header += '<span id="'+id+'jumbotron">'+translateScript(title)+'</span>';
	header += '<a id="Refresh_'+id+'" class="pull-right m-t-0  m-r-10" onclick="Refresh_'+id+'();">';
	//header += '<img src="images/synchronize.png" class="submenuIcon m-r-0 m-t-0">';
	header += '</a></div>';
	var content = '<div id="body_'+id+'" class="panel-content" ';
	if(id == 'mData'){
		content += 'onscroll="javascript:fixing_headtable(\'data\',\'body_'+id+'\')"';
	}
	content += '></div>';
	
	node.setAttribute('id',id);
	node.setAttribute('class','panel');
	node.style.opacity = "0";
	node.style.transitionDuration = '0s'; 
	node.style.transform = 'translateX('+window.innerWidth+'px)';
	panels=document.getElementsByClassName("panel");
	var arrIndex = [];
	var zindexval = 3;
	arrIndex.push(zindexval);
	for(i=0; i<panels.length; i++){
		zindexval = panels[i].style.zIndex;
		opacity = panels[i].style.opacity;
		if(zindexval!== "" && opacity == '1'){
			arrIndex.push(parseInt(zindexval));
		}
	}
	currMax = Math.max.apply(null,arrIndex);
	node.style.zIndex=(currMax+1);
	frame = header+content;
	node.innerHTML = frame;
	
	if(!document.getElementById(id)){
		//var owlcontent = document.getElementById('owl-content');
		var owlcontent = document.body;
		//owlcontent.appendChild(node);
		owlcontent.insertBefore(node,owlcontent.firstChild);
		
		var txtFile = new XMLHttpRequest();
		txtFile.open("GET", urlForm, true);
		
		txtFile.onreadystatechange = function(){
		var ele = document.getElementById(id);
		  if (txtFile.readyState === 4) {
			if (txtFile.status === 200) {
				try{
					htmlAfterTrans = translateScript(txtFile.responseText);
					asyncInnerHTML(htmlAfterTrans, function(fragment){
						document.getElementById("body_"+id).innerHTML = "";
						document.getElementById("body_"+id).appendChild(fragment); // myTarget should be an element node.
						frameLoadAction(id,title,elem,newFunction,ele);
						//hideProgress();
					});
					
					//document.getElementById("body_"+id).innerHTML = htmlAfterTrans;
				}catch(e){
					htmlAfterTrans = translateScript(txtFile.responseText);
					asyncInnerHTML(htmlAfterTrans, function(fragment){
						document.getElementById("body_"+id).innerHTML = "";
						document.getElementById("body_"+id).appendChild(fragment); // myTarget should be an element node.
						frameLoadAction(id,title,elem,newFunction,ele);
						//hideProgress();
					});
					//document.getElementById("body_"+id).innerHTML = htmlAfterTrans;
				}
				
			}else{
				if(elem){
					
					var textString = "";
					if(typeof elem ==='string'){
						if(elem !== ""){
							textString = translateScript(elem);
						}
						asyncInnerHTML(textString, function(fragment){
							document.getElementById("body_"+id).innerHTML = "";
							document.getElementById("body_"+id).appendChild(fragment); 
							frameLoadAction(id,title,elem,newFunction,ele);
						});
						
					}else{
						frameLoadAction(id,title,elem,newFunction,ele);
					}
				}else{
					
					frameLoadAction(id,title,elem,newFunction,ele);
				}
				
			}
			//replacementHeight(id);
		  }
		}
		txtFile.send(null);
	}else{
		document.getElementById(id).style.zIndex=(currMax+1);
		if(document.getElementById(id+'jumbotron')){
			document.getElementById(id+'jumbotron').innerHTML = "";
			asyncInnerHTML(title, function(fragment){
				document.getElementById(id+'jumbotron').appendChild(fragment);
				var ele = document.getElementById(id);
				frameLoadAction(id,title,elem,newFunction,ele);
				//hideProgress();
			});
			//document.getElementById(id+'jumbotron').innerHTML = title;
		}else{
			//hideProgress();
		}
		
	}

}
function replacementHeight(id){
	if(document.getElementById(id+'jumbotronpannel')){
		if(document.getElementById('body_'+id)){
			hJumbo = document.getElementById(id+'jumbotronpannel').offsetHeight;
			if(document.getElementById(id+'jumbotronpannel_style')){
				styleDivBefore = document.getElementById(id+'jumbotronpannel_style');
				styleDivBefore.innerHTML = "";
			}else{
				styleDivBefore = document.createElement("div");
				styleDivBefore.id = id+'jumbotronpannel_style';
			}
			styleBefore = document.createElement("style");
			styleBefore.type = 'text/css'; 
			var styles = "#body_"+id+":before{content:' ';width:100%;height:"+hJumbo+"px;display:block;};";
			if (styleBefore.styleSheet){
                styleBefore.styleSheet.cssText = styles; 
            }else{ 
                styleBefore.appendChild(document.createTextNode(styles)); 
			}
			styleDivBefore.appendChild(styleBefore);
			parent = document.getElementById('body_'+id).parentNode;
			//console.log(parent);
			parent.insertBefore(styleDivBefore,document.getElementById('body_'+id));
			document.getElementById('body_'+id).style.marginTop = "-"+hJumbo+"px";
			if(document.getElementById('listsearchonselect')){
				document.getElementById('listsearchonselect').style.marginTop = hJumbo+"px";
			}
		}
	}
}

function stringToFunction(newFunction){
	if(newFunction){
		functParam = newFunction.split("(");
		if(functParam[0]){
			newFunc = functParam[0];
			newparam = "";
			if(functParam[1]){
				newparam = functParam[1].split(")");
				newparam = newparam[0];
			}
			executeFunctionByName(newFunc, window, newparam);
		}
	}
}
function scaningElem(ele){
	createSearchOnSelect(ele);
}
function createSearchOnSelect(both){
	var ele = both.getElementsByClassName('searchOnSelect');
	if(ele.length>0){
		for(i=0; i<ele.length; i++){
			if(ele[i].style.display.trim() !== "none"){
				parent = ele[i].parentNode;
				id = ele[i].id;
				title = ele[i].getAttribute("title");
				btn = document.createElement("button");
				btn.id="search"+id;
				btn.setAttribute("class","col-12 btnfindselect");
				btn.setAttribute("onclick","searchOnSelect('"+id+"','"+title+"');");
				parent.insertBefore(btn,ele[i]);
			}
		}
	}
}
function goTransition(id){
	var panelid = document.getElementById(id);
	if(panelid){
		var hj = 47;
		if(panelid.getElementsByClassName('panel-content')[0]){
			var panel_content = panelid.getElementsByClassName('panel-content')[0];
			//panel_content.style.height=(window.innerHeight-hj)+'px';
			//panel_content.style.height='100%';
		}
		panelid.style.transitionDuration = '0.2s'; 
		panelid.style.transform = 'translateX(0px)';
		panelid.style.opacity = "1";
		// Simpan Right History @author Atwal
		var url = "?panelid="+id;
		window.history.pushState({urlPath:url},"", url);
		sessionStorage.panel = id;
		sessionStorage.oldlocation = "panelid="+id;
		replacementHeight(id);
	}
}
function closePanelMasal(idArr,num){
	if(typeof num == "undefined"){
		num = 0;
	}
	if(Array.isArray(idArr)){
		if(idArr.length > 0){
			for(i=0; i<idArr.length; i++){
				if(document.getElementById(idArr[num])){
					closeTransition(idArr[num]);
					if(num < idArr.length){
						num++;
						closePanelMasal(idArr,num);
					}
				}
			}
		}
	}
}

function closeTransition(id){
	function removePanel(){
		if(document.getElementById(id)){
			remove_panel(id);
			clearInterval(closeTime);
		}
	}
	if(fxhead[sessionStorage.panel]){
		fxhead[sessionStorage.panel] = null;
	}
	var panelid = document.getElementById(id);
	if(panelid){
		panelid.style.transitionDuration = '0.1s'; 
		panelid.style.transform = 'translateX('+window.innerWidth+'px)';
		panelid.style.opacity = "0";
		if(panelid.getAttribute('hiding-element') != null && panelid.getAttribute('hiding-element') == "hide"){
			panelid.style.zIndex = "-999";
		}else{
			var closeTime = setInterval(removePanel,200);
		}
	}
}
function alert_panel(text,title,btn,func,param){
	var id = 'alert'; 
	if(!title){
		title = "Alert";
	}
	if(document.getElementById(id)){
		document.getElementById(id).remove();
	}
	showProgress();
		panel_content 	=(window.innerHeight-97);
		panel_top 		= (window.innerHeight - (panel_content+47))/2;	
		//height:'+panel_content+'px
		var header = '<div class="jumbotron"><span>'+title+'</span></div>';
		var content = '<div class="panel-content" style="height:100%;"><div class="formlogin"></div></div><div class="panel-footer"><button class="btn-alert" onclick="remove_alert(\''+id+'\',this);" newfunction="'+func+'" data-param="'+param+'">'+btn+'</button><div class="clearfix"></div></div>';
		var node = document.createElement("div");     
		node.setAttribute('id',id);
		node.setAttribute('class','panel panelalert');
		node.style.bottom = 0+"px";
		node.style.top = 0+"px";
		node.style.display = "block";
		panels=document.getElementsByClassName("panel");
		node.style.zIndex=1020;
		
		frame = header+content;
		node.innerHTML = frame;
		
		var owlcontent = document.body;
		owlcontent.appendChild(node);
		var ele = document.getElementById(id);
		var panel_content = ele.getElementsByClassName('panel-content')[0];
		panel_content.getElementsByClassName('formlogin')[0].innerHTML = text;

}

function executeFunctionByName(functionName, context , args) {
  var argText = [];
  var namespaces = [];
  if(args && args !== ''){
	argText = args.split(',');
  }
  if(functionName && functionName !== ''){
	namespaces = functionName.split(".");
  }
  if(namespaces.length > 0){
	  var func = namespaces.pop();
	  for(var i = 0; i < namespaces.length; i++) {
		context = context[namespaces[i]];
	  }
	  return context[func].apply(context, argText);
  }else{
	return true;
  }
}

function nextStepLogin(x){
	//Step By Step System
	var num = Math.floor(parseInt(x)) + 1;
	switch (x){
		case 1:
			if(window.location.href.search("index.html") != -1){
				frame_panel('panelLogin','{login}',x);
			}else{
				window.location.href = "index.html";
			}
		break;
		case 2:
			if(window.location.href.search("index.html") != -1){
				frame_panel('panelSyn','{sinkronisasi}',x);
			}else{
				window.location.href = "index.html";
			}
		break;
		case 3:
		var progress = document.getElementsByClassName('progress')[0];
		progress.style.display = 'block';
		closeAllPanel();
		progress.style.display = 'none';
		break;
	}
}
function remove_panel(id){
	document.getElementById(id).remove()
}
function remove_alert(id,e){
	if(e){
		var func = e.getAttribute('newfunction');
		var arguments = e.getAttribute('data-param');
		var param = "";
		var coma = ",";
		document.getElementById(id).remove()
		hideProgress();
		if(func && func !== ''){
			executeFunctionByName(func, window, arguments);
		}
	}
}
//First Login
function frame_panel_login(id,title,num){
	var header = '<div class="jumbotron"><span>'+title+'</span></div>';
	var content = '<div class="panel-content"><div class="formlogin"></div><div id="panel-footer'+num+'" class="panel-footer"></div></div>';
	var node = document.createElement("div");     
	node.setAttribute('id',id);
	node.setAttribute('class','panel panellogin');
	
	node.style.display = "none";
	panels=document.getElementsByClassName("panel");
	var arrIndex = [];
	var zindexval = 200;
	arrIndex.push(zindexval);
	for(i=0; i<panels.length; i++){
		zindexval = panels[i].style.zIndex;
		display = panels[i].style.display;
		if(zindexval!== "" && display == 'block'){
			arrIndex.push(parseInt(zindexval));
		}
	}
	currMax = Math.max.apply(null,arrIndex);
	node.style.zIndex=(currMax+1);
	
	frame = header+content;
	
	node.innerHTML = frame;
	if(!document.getElementById(id)){
		//var owlcontent = document.getElementById('owl-content');
		var owlcontent = document.body;
		owlcontent.appendChild(node);
		//alert("test");
		var txtFile = new XMLHttpRequest();
		txtFile.open("GET", "forms/"+id+".html", true);
		txtFile.onreadystatechange = function() {
		  if (txtFile.readyState === 4) {
			if (txtFile.status === 200) {
			  var ele = document.getElementById(id);
			  var panel_content = ele.getElementsByClassName('panel-content')[0];
			  panel_content.getElementsByClassName('formlogin')[0].innerHTML = txtFile.responseText;
			}
		  }
		}
		txtFile.send(null);
		goSlide(id,'top');
	}else{
		document.getElementById(id).style.zIndex=(currMax+1);
		if(document.getElementById(id+'jumbotron')){
			document.getElementById(id+'jumbotron').innerHTML = title;
		}
		goSlide(id,'top');
	}
}
function goAlert(panelid,direction){
	var panel=document.getElementById(panelId);
	if(panel){
		current=panel.style.zIndex;
		
		if(panel.getElementsByClassName('panel-content')[0]){
			var panel_content = panel.getElementsByClassName('panel-content')[0];
			panel_content.style.height=(window.innerHeight/100)*30+'px';
		}
	}
}
function goSlide(panelid,direction,closeFirst){
	panelId=panelid;
	hideMainMenu();	
	current=document.getElementById(panelId).style.zIndex;
	if(document.getElementById(panelId).style.display=='none'){
		if(closeFirst!='no'){
			//closeAllPanel(); // jika ingin panel tumpang tindih silahkan buang line ini
		}
		if(direction=='top'){
			timer=setInterval(slideTop,5);
		}else if(direction=='left'){
			timer=setInterval(slideLeft,5);
		}else if(direction=='right'){
			timer=setInterval(slideRight,5);
		}else if(direction=='down'){
			timer=setInterval(slideDown,5);
		}
		
		// Simpan Right History @author Atwal
		var url = "index.html?panelid="+panelid+"&direction="+direction+"&closeFirst="+closeFirst;
		window.history.pushState({urlPath:url},"", url);
		sessionStorage.panel = panelid;
		sessionStorage.oldlocation = "panelid="+panelid+"&direction="+direction+"&closeFirst="+closeFirst;
		// End
	}else{
		/*obj=document.getElementById(panelId).style;
		if(current==(panels.length+1)){	
			closeSlide(panelid,direction);
		}else{
			obj.zIndex=(panels.length+1);
		}*/
	}
	
}
//3 Flag Back History Funntion @author Atwal
var onpopstateStat = "";
window.onpopstate=function(evt){
	
	//Fungsi Melarang back history ketika Event Berjalan
	function get_ele(ele,filter){
		var true_fales = new Array();
		true_fales['param'] = false;
		for(i=0; i<ele.length; i++){
			if(ele[i].style.display.toString() == filter){
				true_fales['param'] = true;
				true_fales['obj'] = ele[i];
			}
		}
		return true_fales;
	}
	function get_ele_opacity(ele,filter,num){
		var true_fale = false;
		if(typeof num == "undefined"){
			num = 0;
		}
		var forloop = 1+num;
		if(ele.length > 0 ){
			for(i=num; i<forloop; i++){
				if(ele[i].style.opacity.toString() == filter){
					if(this.calendar){
						this.calendar.hideCalendar();
					}
					true_fale = false;
				}
			}	
		}
		return true_fale;
	}
	
	function get_trueback(){
		var backhistory = true;
		var progress = document.getElementsByClassName('progress');
		var calendar = document.getElementsByClassName('calendar-box');
		var panelLogin = document.getElementById('panelLogin');
		
		if(panelLogin && sessionStorage.username == "" && sessionStorage.password == ""){
			backhistory = false;
		}
		if(get_ele(progress,'block')['param'] === true){
			if(get_ele(progress,'block')['obj']){
				if(get_ele(progress,'block')['obj'].getAttribute("lock") == "gohead"){
					action = get_ele(progress,'block')['obj'].getAttribute("onclick");
					eval(action);
				}
			}
			backhistory = false;
		}else{
			backhistory = true;
		}
		if(get_ele_opacity(calendar,1) === true){
			backhistory = false;
		}
		
		result = backhistory;
		
		return result;
	}
	// END
   if(get_trueback() == false){
	   onpopstateStat = "forward";
	   window.history.forward();
	}else{
		if(onpopstateStat != "forward"){
		   var oldquery = sessionStorage.oldlocation;
		   if(oldquery !== "HOME"){
			   if(oldquery != ""){
				   var varsold = oldquery.split("&");
				   var panelidold 		= "";
				   var directionold		= "";
				   for (var i=0;i<varsold.length;i++){
						var pair = varsold[i].split("=");
						if(pair[0] == 'panelid'){
							panelidold = pair[1];
						}
						if(pair[0] == 'direction'){
							directionold = pair[1];
						}
				   }
					if(document.getElementById(panelidold)){
						var panelOld = document.getElementById(panelidold);
						closeTransition(panelidold);
					}
			   }
			   var query = window.location.search.substring(1);
			   if(query  != ""){
				   var vars = query.split("&");
				   var panelid 		= "";
				   var direction 	= "";
				   var closeFirst 	= "";
				   for (var i=0;i<vars.length;i++){
						var pair = vars[i].split("=");
						if(pair[0] == 'panelid'){
							panelid = pair[1];
						}
						if(pair[0] == 'direction'){
							direction = pair[1];
						}
						if(pair[0] == 'closeFirst'){
							closeFirst = pair[1];
						}
				   }
					if(document.getElementById(panelid)){
						var paneldiv = document.getElementById(panelid);
						sessionStorage.panel = panelid;
						sessionStorage.oldlocation = "panelid="+panelid;
					}
			   }else{
					sessionStorage.oldlocation = "HOME";
					sessionStorage.panel = "home";
			   }
		   }
		   //untuk close Scan Qr jika Back history, location function -> qrscanner.js
			if(typeof QRScanner !== 'undefined'){
				QRScanner.getStatus(function(status){
					if(status.denied == false){
						closeJendelaQr('denied');
					}
				});
			}
			// : END
			return(false);
		}else{
			onpopstateStat = "";
		}
   }
   
}
//3 END;
function slideDown(){
	z+=30;
	curTop=(getScreen()[0]*-1)+z;
	if(curTop>0) curTop=0;
	var panel = document.getElementById(panelId);
	
	var hj = 47;
	if(panel.getElementsByClassName('panel-content')[0]){
		var panel_content = panel.getElementsByClassName('panel-content')[0];
		//panel_content.style.height=(window.innerHeight-hj)+'px';
		panel_content.style.height='100%';
	}
	panel.style.display='block';	
	//panel.style.width=(getScreen()[1])+'px';
	//panel.style.height=(getScreen()[0])+'px';
	panel.style.left='0px';
	panel.style.top=(curTop+window.pageYOffset)+'px';
	if(curTop>=0){
		clearInterval(timer);
		z=0;
	}
}

function slideTop(){
	z-=30;
	curTop=getScreen()[0]+z;
	if(curTop<0) curTop=0;	
	var panel = document.getElementById(panelId);
	var hj = 47;
	if(panel.getElementsByClassName('panel-content')[0]){
		var panel_content = panel.getElementsByClassName('panel-content')[0];
		//panel_content.style.height=(window.innerHeight-hj)+'px';
		panel_content.style.height='100%';
	}
	panel.style.display='block';
	//panel.style.width=(getScreen()[1])+'px';
	//panel.style.height=(getScreen()[0])+'px';	
	panel.style.left='0px';
	panel.style.top=(curTop+window.pageYOffset)+'px';
	if(curTop<=0){
		clearInterval(timer);
		z=0;
	}
}

function slideRight(){
	z+=30;
	curRight=(getScreen()[0]*-1)+z;
	if(curRight>0) curRight=0;
	var panel = document.getElementById(panelId);
	
	var hj = 47;
	if(panel.getElementsByClassName('panel-content').length>0){
		var panel_content = panel.getElementsByClassName('panel-content')[0];
		panel_content.style.height='100%';
		//panel_content.style.height=(window.innerHeight-hj)+'px';
	}
	panel.style.display='block';
	//panel.style.width=(getScreen()[1])+'px';
	panel.style.left=curRight+'px';
	panel.style.top=(window.pageYOffset)+'px';
	if(curRight>=0){
		clearInterval(timer);
		z=0;
	}
}

function slideLeft(){
	z-=30;
	curRight=getScreen()[0]+z;
	if(curRight<0) curRight=0;	
	var panel = document.getElementById(panelId);
	var hj = 47;
	if(panel.getElementsByClassName('panel-content')[0]){
		var panel_content = panel.getElementsByClassName('panel-content')[0];
		panel_content.style.height='100%';
		//panel_content.style.height=(window.innerHeight-hj)+'px';
	}
	panel.style.display='block';
	//panel.style.width=(getScreen()[1])+'px';
	//panel.style.height=(getScreen()[0])+'px';	
	panel.style.left=curRight+'px';
	panel.style.top=(window.pageYOffset)+'px';
	if(curRight<=0){
		clearInterval(timer);
		z=0;
	}
}

function closeSlide(panelid,direction){
	panelId=panelid;	
	if(direction=='top'){
		timer=setInterval(closeSlideTop,10);
	}else if(direction=='left'){
		timer=setInterval(closeSlideLeft,10);
	}else if(direction=='right'){
		timer=setInterval(closeSlideRight,10);
	}else if(direction=='down'){
		timer=setInterval(closeSlideDown,10);
	}
}	

function closeSlideDown(){
	z+=10;
	dHeight=parseInt(document.getElementById(panelId).style.top);
    curTop=(dHeight-z);
    document.getElementById(panelId).style.top=curTop+'px';
	if(getScreen()[0]+curTop<0){
		clearInterval(timer);
		z=0;
		document.getElementById(panelId).style.display='none';
	}
}

function closeSlideTop(){
	z+=10;
	dHeight=parseInt(document.getElementById(panelId).style.top);
    curTop=(dHeight+z);
    document.getElementById(panelId).style.top=curTop+'px';
	if(getScreen()[0]<=curTop){
		clearInterval(timer);
		z=0;
		document.getElementById(panelId).style.display='none';
	}
}

function closeSlideRight(){
	z+=10;
	dWidth=parseInt(document.getElementById(panelId).style.left);
    curLeft=(dWidth-z);
    document.getElementById(panelId).style.left=curLeft+'px';
	if(getScreen()[1]+curLeft<=0){
		clearInterval(timer);
		z=0;
		document.getElementById(panelId).style.display='none';
	}
}

function closeSlideLeft(){
	z+=10;
	dWidth=parseInt(document.getElementById(panelId).style.left);
    curLeft=(dWidth+z);
    document.getElementById(panelId).style.left=curLeft+'px';
	if(getScreen()[1]<=curLeft){
		clearInterval(timer);
		z=0;
		document.getElementById(panelId).style.display='none';
	}
}

function closeAllPanel(){
  var panels=document.getElementsByClassName("panel");
  numPanel = panels.length;
  if(numPanel>0){
	  for(x=0;x<numPanel;x++){
		pannel = panels[x].id;
		closeTransition(pannel);
	  }
  }
  //history.go(-history.length);
  window.history.back(history.length);
  sessionStorage.panel = "home";
}
//4 Close Panel on Top Header @author Atwal
function closePanel(e){
	if(typeof e !== "undefined"){
		progress = document.getElementsByClassName('progress');
		progress[0].setAttribute("lock","gohead");
		window.history.back();
	}else{
		window.history.back();
	}
}

//4 End;
//*5 resize DIV#home for overflow after Orientation Change
function overflow_home(){
	if(document.getElementById('home')){
		var panelhome = document.getElementById('home');
		var hj = 48;
		//panelhome.style.height=(window.innerHeight-hj)+'px';
		// top: 33px;
		// overflow: scroll;
		// position: fixed;
		// bottom: 0px;
	}
}
window.addEventListener('orientationchange', function(){
    overflow_home();
});
// 5 End;
function setZindex(){
  panels=document.getElementsByClassName("panel");
  numPanel = panels.length;
  for(x=0;x<numPanel;x++){
  	panels[x].style.zIndex=x;
  }	
  document.getElementById(panelid).style.zIndex=x+1;
}
function appExit(){
    if(confirm('Keluar, anda yakin..?')){
        navigator.app.exitApp();
        return true;
    }
}

function printTable(col,data,idTarget,title,action){
  //kosongkan display:
  document.getElementById(idTarget).innerHTML='';
  var table=document.createElement("TABLE");
  table.setAttribute("id",idTarget+"data");
  table.setAttribute("class","data");
  table.setAttribute("cellspacing","1px");
  table.setAttribute("border","0");

  //col header
	var header = table.createTHead();
	var rowth=header.insertRow(0);
	rowth.setAttribute("class","rowHeader");

	th = document.createElement('th');
	th.setAttribute("class","tdHeader");
	th.innerHTML = 'No';
	rowth.appendChild(th);
	for(x=0;x<col.length;x++){
		th = document.createElement('th');
		th.setAttribute("class","tdHeader");
		th.innerHTML = translateScript(col[x]);
		rowth.appendChild(th);
	}
	
  //col detail
  var tbody = table.createTBody();
  //console.log(title);
  for(x=0;x<data.length;x++){
      row=tbody.insertRow(x);
      row.setAttribute("class","rowData");
      if(action!== undefined){
        if(action.length>0){
          row.setAttribute("onclick",action[x]);
          row.setAttribute("style","cursor:pointer");
        }
      }
      cell=row.insertCell(0);
      node=document.createTextNode(x+1);
      cell.setAttribute("class","tdData");
      cell.appendChild(node);     
    for(y=0;y<data[x].length;y++){
      var cell=row.insertCell(y+1);
      cell.setAttribute("class","tdData");
      //var node=document.createTextNode(data[x][y]);
      //cell.appendChild(node);
      cell.innerHTML=data[x][y];
	  if(data[x].length < col.length && y == data[x].length-1){
		cell.setAttribute('colspan', col.length-y );
	  }
    }
  }
  
  node=document.createTextNode(title);
  document.getElementById(idTarget).appendChild(node);
  document.getElementById(idTarget).appendChild(table);
}
function disabledEnabled(id,trueFalse){
	switch(trueFalse.toLowerCase()){
		case 'true':
			if(document.getElementById(id)){
				if(document.getElementById(id).disabled === false){
					document.getElementById(id).disabled = true;
				}
			}
			
		break;
		case 'false':
			if(document.getElementById(id)){
				if(document.getElementById(id).disabled === true){
					document.getElementById(id).disabled = false;
				}
			}
		break;
	}
}


/**
Ex: Author: Atwal untuk func : asynPrintTable();
Descriptsi : Untuk Load table dengan sistem Asyncronize mengetahui loading atau tidak dan tidak mengganggu event lain. 
===========================================================================================================================
var col[0]=['Tanggal','Data'];
#Atau 
col[0]={
	'user':{
		'text' :'User'
	},
	'tanggal':{
		'text' :'Tanggal'
	},
	'cetakan':{
		'text' :'{cetakan}'
	},
	'lastupdate':{
		'text' :'Lastupdate'
	}
};
var bothId = document.getElementById('dataPanen');
var option = {
	eleParent:bothId,
	title:'',
	header:col,
	numrow:false
}
asynPrintTable(option,function(originEvent){	
	var data=new Array();
	var act=new Array();
	var edit= new Array();
	var view=new Array();
	var hapus=new Array();
	for(i=0; i<dataTable.length; i++){	
	
		#Edit Contoh double Function:
		==========================================================	
		data[i] =new Array();
		data[i][0]  = dataTable[i].tanggal;
		data[i][1]  = dataTable[i].data;
		
		edit[i]  = [
						{
							'name':'showInnerTab',
							'param':['bkmPengawas','bkmPengawas_tab']},
						{
							'name':'editBkm',
							'param':[rs.rows.item(i).notransaksi]
						}
					];
		view[i]  = [{'name':'lihatBkm','param':[rs.rows.item(i).notransaksi]}];
		hapus[i] = [{'name':'hapusBkm','param':[rs.rows.item(i).notransaksi]}];
		==========================================================
		act[i]	  = {'EDIT':edit[i],'VIEW':view[i],'HAPUS':hapus[i]};
	}
	
	#Build Cara 1
	================================================
	var newdata = {
	  data :data,
	  action : act
  }
	restab = originEvent.exec(newdata); 
	if(restab.success){
		originEvent.build();
	}else{
		notifAlert(restab.result);
	}
	=================================================
	
	#Build Cara 2 (tanpa check succses atau tidak)
	================================================
	var newdata = {
	  data :data,
	  styling : atyle,
	  action : act
  }
	originEvent.exec(newdata); 
	originEvent.build();
	=================================================
}	
==============================================================================	
END
**/
function asynPrintTable(opt,callback){
	//document.getElementById(idTarget).innerHTML = "Loading..";
	var title,eleBoth;
	var colomn = new Array();
	var dataDefault=new Array();
	var actionDefault=new Array();
	var stylingDefault = new Array();
	var footerDefault=new Array();
	var showload=undefined;
	
	var fragmentFunct = new AsynPrintTable();
	fragmentFunct.init();
	(function(){
		callback(fragmentFunct);
	})();
	function AsynPrintTable(){
		this.Utils = {
			isElement: function(o){
				return (
				  o instanceof HTMLElement || //DOM2
				  (o && typeof o === 'object' && o !== null && o.nodeType === 1 && typeof o.nodeName === 'string')
				);
			}
			,extend: function(target, source) {
				target = target || {};
				for (var prop in source) {
					// Go recursively
					if (this.isObject(source[prop])) {
					target[prop] = this.extend(target[prop], source[prop])
					} else {
					target[prop] = source[prop]
					}
				}
				return target;
			}
			,isObject: function(o){
				return Object.prototype.toString.call(o) === '[object Object]';
			}
		}
		this.getEleId = function(ele){
			var utils = this.Utils;
			var elementTraget;
			if(typeof ele !== 'undefined' && ele !== ""){
				if(utils.isElement(ele)){
					elementTraget = ele;
				}else{
					elementTraget = document.getElementById(ele);
				}
				return elementTraget;
			}else{
				//throw new Error('Provided selector is not an element object or String element Id');
				return null
			}
		}
		this.optionsDefault = {
			eleParent : eleBoth,
			title : title,
			header : colomn,
			headerBackground : "",
			headerColor : "",
			data : dataDefault,
			action : actionDefault,
			styling : stylingDefault,
			footer : footerDefault,
			numrow : true,
			success : false,
			failed : true
		}
		this.cacheTable = {
			eleParent : this.optionsDefault.eleParent,
			title : this.optionsDefault.title,
			header : this.optionsDefault.header,
			headerBackground : this.optionsDefault.headerBackground,
			headerColor : this.optionsDefault.headerColor,
			data : this.optionsDefault.data,
			action : this.optionsDefault.action,
			styling : this.optionsDefault.styling,
			footer : this.optionsDefault.footer,
			numrow : this.optionsDefault.numrow,
			success : this.optionsDefault.success,
			failed : this.optionsDefault.failed,
			result : this.optionsDefault.eleParent
		}
		this.progress = function(type){
			switch(type){
				case 'on':
					this.showProgress();
				break;
				case 'off':
					this.hideProgress();
				break;
			}
		};
		this.init = function(){
			this.optionsDefault = this.Utils.extend(this.Utils.extend({}, this.optionsDefault), opt);
			this.clear();
			this.exec({data : dataDefault});
			this.build();
			var elementTraget = this.getEleId(this.optionsDefault.eleParent);
			if(elementTraget !== null){
				this.progress("on");
			}
		};
		this.success = function(elementTraget,frag){
			var result = {
				success : true,
				failed	: false,
				eleParent : elementTraget,
				result	: frag
			}
			this.cacheTable = this.Utils.extend(this.Utils.extend({}, this.optionsDefault), result);
			return result;
		};
		this.failed = function(elementTraget,e){
			var result = {
				success : false,
				failed	: true,
				eleParent	: elementTraget,
				result	: e
			}
			this.cacheTable = this.Utils.extend(this.Utils.extend({}, this.optionsDefault), result);
			return result;
		};
		this.clear = function(){
			this.cacheTable = this.Utils.extend(this.Utils.extend({}, this.cacheTable), this.optionsDefault);
		};
		this.exec = function(newdata){
			this.cacheTable = this.Utils.extend(this.Utils.extend({}, this.cacheTable), newdata);
			resultFrag = this.contruction();
			return resultFrag;
		};
		this.build = function(){
			var frag;
			var cacheTable = this.cacheTable;
			if(cacheTable.success){
				frag = cacheTable.result;
				if(frag){
					var elementTraget = cacheTable.eleParent;
					if(elementTraget !== null){
						elementTraget.innerHTML = "";
						elementTraget.appendChild(frag);
					}
				}
			}
		};
		this.showProgress = function(){
			var options = this.optionsDefault;
			var elementTraget = this.getEleId(options.eleParent);
			var heighMin = 50;
			if(elementTraget !== null){
				h = elementTraget.offsetHeight;
				w = elementTraget.offsetWidth;
				if(h<heighMin){
					h = heighMin;
				}
				var divLoad = document.createElement('div');
				var imageLoad = document.createElement('img');
				rect = elementTraget.getBoundingClientRect();
				divLoad.id = "loadingfor_"+elementTraget.id;
				divLoad.style.height = h+"px";
				divLoad.style.width = w+"px";
				divLoad.style.position = "absolute";
				divLoad.style.background = "#ffffff";
				divLoad.style.opacity = .6;
				imageLoad.style.position = "relative";
				imageLoad.style.top = ((h-15)/2)+"px";
				imageLoad.style.left = ((w-15)/2)+"px";
				imageLoad.style.width = 30+"px";
				imageLoad.style.height = 30+"px";
				imageLoad.src = "images/blue_line_big_loading.gif";
				divLoad.appendChild(imageLoad);
				elementTraget.insertBefore(divLoad,elementTraget.firstChild);
			}
		};
		this.hideProgress = function(){
			var options = this.optionsDefault;
			var elementTraget = this.getEleId(options.eleParent);
			if(elementTraget !== null){
				if(document.getElementById("loadingfor_"+elementTraget.id)){
					document.getElementById("loadingfor_"+elementTraget.id).remove();
				}
			}
		};
		this.contruction = function(){
			var cacheTable = this.cacheTable;
			var col = cacheTable.header;
			var headerBackground = cacheTable.headerBackground;
			var headerColor = cacheTable.headerColor;
			var data = cacheTable.data;
			var title = cacheTable.title;
			var action = cacheTable.action;
			var styling = cacheTable.styling;
			var footer = cacheTable.footer;
			var numrow = cacheTable.numrow;
			var elementTraget = this.getEleId(cacheTable.eleParent);
			var idTarget = "";
			if(elementTraget !== null){
			   idTarget = elementTraget.id;
			}
			  try{
				  var table=document.createElement("TABLE");
				  if(idTarget !== ""){
					table.setAttribute("id",idTarget+"data");
				  }
				  table.setAttribute("class","data");
				  table.setAttribute("cellspacing","1px");
				  table.setAttribute("border","0");
				  table.setAttribute("data-print","true");
				  //col header
					var header = table.createTHead();
					for(x=0;x<col.length;x++){
						rowth=header.insertRow(x);
						rowth.setAttribute("class","rowHeader");
						if(headerBackground != ""){
							rowth.style.background = headerBackground;
						}
						if(headerColor != ""){
							rowth.style.color = headerColor;
						}
						if(numrow){
							th = document.createElement('th');
							th.setAttribute("class","tdHeader");
							th.innerHTML = 'No';
							rowth.appendChild(th);
						}
						for (var xc in col[x]) {
							if (col[x].hasOwnProperty(xc)){
								th = document.createElement('th');
								th.setAttribute("class","tdHeader");
								if(typeof col[x][xc] !== 'object'){
									th.innerHTML = translateScript(col[x][xc]);
								}else{
									for (var k in col[x][xc]) {
										if (col[x][xc].hasOwnProperty("text")){
											th.innerHTML = translateScript(col[x][xc][k]);
										}
										if (col[x][xc].hasOwnProperty("style")){
											th.style = col[x][xc][k];
										}
										if (col[x][xc].hasOwnProperty("colspan")){
											th.setAttribute("colspan",col[x][xc][k]);
										}
										if (col[x][xc].hasOwnProperty("rowspan")){
											th.setAttribute("rowspan",col[x][xc][k]);
										}
									}
								}
								rowth.appendChild(th);
							}	
						}
						
		
					}
				  //col detail
					var firstKey = parseInt(Object.keys(data)[0]); 
					function getAction(func,data,e){
						var dataparam = "";
						var varfunc = new Array();
						if(Array.isArray(data)){
							for (var k in data) {
								if (data.hasOwnProperty(k)){
									varfunc.push(data[k].name+"||"+data[k].param);
								}
							}
							varfuncStr = varfunc.join("#");
							e.setAttribute(func,varfuncStr);
						}
					}
				  var tbody = table.createTBody();
				  var num = 0;
				  if(data.length > 0){
					  for(x=0;x<data.length;x++){
							num = 0;
							k = (firstKey+x);
						if(typeof data[k] !== "undefined"){
							row=tbody.insertRow(x);
							row.setAttribute("class","rowData");
							if(typeof action !== 'undefined'){
								for (var key in action[k]) {
									if (action[k].hasOwnProperty(key)) {
										getAction(key.toLowerCase(),action[k][key],row);
										num++;
									}
								}
								if(idTarget !== ""){
									row.setAttribute("onclick","Action_RowTable(this,'"+idTarget+"data_actbox')");
								}
								row.setAttribute("style","cursor:pointer");
							}
							if(numrow){
								cell=row.insertCell(0);
								node=document.createTextNode(k+1);
								console.log("node",k+1);
								cell.setAttribute("class","tdData");
								cell.appendChild(node);  
							}
							//if(Array.isArray(data[k])){
							for (var y in data[k]) {
								if (data[k].hasOwnProperty(y)){
									var cell=row.insertCell(y);
									  cell.setAttribute("class","tdData");
									  if(typeof data[k][y] !== 'object'){
											cell.innerHTML=data[k][y];
									  }else{
										  for (var z in data[k][y]) {
											if (data[k][y].hasOwnProperty("text")){
													cell.innerHTML=data[k][y][z];
											}
											if (data[k][y].hasOwnProperty("colspan")){
													cell.setAttribute("colspan",data[k][y][z]);
											}
											if (data[k][y].hasOwnProperty("rowspan")){
													cell.setAttribute("rowspan",data[k][y][z]);
											}
											if (data[k][y].hasOwnProperty("style")){
													cell.style=data[k][y][z];
											}
										  }
									  }
								}
							}
							if(typeof styling !== "undefined" && styling.length > 0){
								if(typeof styling[k] !== "undefined"){
									row.style = "cursor:pointer;"+styling[k];
								}
							}else{
								row.style = "cursor:pointer;";
							}
						}
					  }
					  if(typeof footer !== 'undefined'){
						  lastrow = data.length;
						  nextRow = lastrow+1;
						  for(x=0;x<footer.length;x++){
							  row=table.insertRow(nextRow);
							  row.setAttribute("class","rowData");
							  row.style ="background:rgb(255, 245, 107);";
							for(y=0;y<footer[x].length;y++){
							  var cell=row.insertCell(y);
							  cell.setAttribute("class","tdData");
							  if(y == 0){
								  if(numrow){
									cell.setAttribute("colspan",2);
								  }
							  }
							  //var node=document.createTextNode(data[x][y]);
							  //cell.appendChild(node);
							  cell.innerHTML=footer[x][y];
							}
							nextRow++;
						  }
					  }
				  }else{
					  maxColspan = 0;
					  if(col.length > 0){
						  for(x=0;x<col.length;x++){
							  if(maxColspan < col[x].length){
								  maxColspan = col[x].length;
							  }
						  }
					  }
					if(numrow){
						maxColspan = maxColspan+1;
					}
					row=tbody.insertRow(0);
					row.setAttribute("class","rowData");
					cell=row.insertCell(0);
					nodata = translateScript("{nodata}");
					node=document.createTextNode(nodata);
					cell.setAttribute("class","tdData");
					cell.setAttribute("colspan",maxColspan);
					cell.appendChild(node);  
				  }
				  if(elementTraget !== null){
					elementTraget.innerHTML='';
				  }
				  if(idTarget !== ""){
					createActionBox(idTarget+'data_actbox',num);
				  }
				  node=document.createTextNode(title);
				  var frag = document.createDocumentFragment();
				  frag.appendChild(node);
				  frag.appendChild(table);
				  //document.getElementById(idTarget).appendChild(node);
				 // document.getElementById(idTarget).appendChild(table);
				  var result = this.success(elementTraget,frag);
				  return result;
			  }catch(e){
				  var result = this.failed(elementTraget,e);
				  return result;
			  }
		};
	}
}
function printTablemultiAct(col,data,idTarget,title,action,styling,footer){
	/**
	Ex: Setting action Author: Atwal untuk func : printTablemultiAct();
	==============================================================================
		for(i=0; i<dataTable.length; i++){	
			//Edit Contoh double Function:
			edit[i]  = [
							{
								'name':'showInnerTab',
								'param':['bkmPengawas','bkmPengawas_tab']},
							{
								'name':'editBkm',
								'param':[rs.rows.item(i).notransaksi]
							}
						];
			view[i]  = [{'name':'lihatBkm','param':[rs.rows.item(i).notransaksi]}];
			hapus[i] = [{'name':'hapusBkm','param':[rs.rows.item(i).notransaksi]}];
			--------------------------------------------------------------------------
			act[i]	  = {'EDIT':edit[i],'VIEW':view[i],'HAPUS':hapus[i]};
		}
		printTablemultiAct(col,data,'idTarget','title',act);  
	==============================================================================	
	END
	**/
  //kosongkan display:
  //document.getElementById(idTarget).innerHTML='';
  var table=document.createElement("TABLE");
  table.setAttribute("id",idTarget+"data");
  table.setAttribute("class","data");
  table.setAttribute("cellspacing","1px");
  table.setAttribute("border","0");
  table.setAttribute("data-print","true");
  //col header
	var header = table.createTHead();
	var rowth=header.insertRow(0);
	rowth.setAttribute("class","rowHeader");

	th = document.createElement('th');
	th.setAttribute("class","tdHeader");
	th.innerHTML = 'No';
	rowth.appendChild(th);
	for(x=0;x<col.length;x++){
		th = document.createElement('th');
		th.setAttribute("class","tdHeader");
		th.innerHTML = translateScript(col[x]);
		rowth.appendChild(th);
	}
  //col detail
	var firstKey = parseInt(Object.keys(data)[0]); 
	function getAction(func,data,e){
		var dataparam = "";
		var varfunc = new Array();
		if(Array.isArray(data)){
			for (var k in data) {
				if (data.hasOwnProperty(k)){
					//if(i!==0){
					//	varfunc += "#";
					//}
					varfunc.push(data[k].name+"||"+data[k].param);
				}
			}
			varfuncStr = varfunc.join("#");
			e.setAttribute(func,varfuncStr);
		}
	}
  var tbody = table.createTBody();
  var num = 0;
  if(data.length > 0){
	  for(x=0;x<data.length;x++){
			num = 0;
			k = (firstKey+x);
		if(typeof data[k] !== "undefined"){
			row=tbody.insertRow(x);
			row.setAttribute("class","rowData");
			if(typeof action !== 'undefined'){
				for (var key in action[k]) {
					if (action[k].hasOwnProperty(key)) {
						getAction(key.toLowerCase(),action[k][key],row);
						num = num+1;
					}
				}
				row.setAttribute("onclick","Action_RowTable(this,'"+idTarget+"data_actbox')");
				row.setAttribute("style","cursor:pointer");
			}

			cell=row.insertCell(0);
			node=document.createTextNode(k+1);
			cell.setAttribute("class","tdData");
			cell.appendChild(node);  
			if(Array.isArray(data[k])){
				for(y=0;y<data[k].length;y++){
				  var cell=row.insertCell(y+1);
				  cell.setAttribute("class","tdData");
				  cell.innerHTML=data[k][y];
				}
			}
			if(typeof styling !== "undefined"){
				if(typeof styling[k] !== "undefined"){
					row.style = "cursor:pointer;"+styling[k];
				}
			}
		}
	  }
	  if(typeof footer !== 'undefined'){
		  lastrow = data.length;
		  for(x=0;x<footer.length;x++){
			  row=table.insertRow(lastrow+1);
			  row.setAttribute("class","rowData");
			  row.style.background="rgb(255, 245, 107)";
			  //cell=row.insertCell(0);
			  //node=document.createTextNode('');
			  //cell.setAttribute("class","tdData");
			  //cell.appendChild(node);     
			for(y=0;y<footer[x].length;y++){
			  var cell=row.insertCell(y);
			  cell.setAttribute("class","tdData");
			  if(y == 0){
			  cell.setAttribute("colspan",2);
			  }
			  //var node=document.createTextNode(data[x][y]);
			  //cell.appendChild(node);
			  cell.innerHTML=footer[x][y];
			}
		  }
	  }
  }else{
	row=tbody.insertRow(0);
	row.setAttribute("class","rowData");
	cell=row.insertCell(0);
	nodata = translateScript("{nodata}");
	node=document.createTextNode(nodata);
	cell.setAttribute("class","tdData");
	cell.setAttribute("colspan",col.length+1);
	cell.appendChild(node);  
  }
  document.getElementById(idTarget).innerHTML='';
  createActionBox(idTarget+'data_actbox',num);
  node=document.createTextNode(title);
  document.getElementById(idTarget).appendChild(node);
  document.getElementById(idTarget).appendChild(table);
}
function getAction_data(e,name){
	var getfunction = e.getAttribute(name);
	var function_call = "";
	if(getfunction && getfunction != ""){
		
		var f = getfunction.split('#');
		var functext = "";
		var param = "";
		for(i=0; i<f.length; i++){
			functext = f[i].split('||');
			func	= functext[0];
			param 	= functext[1].split(',');
			var par	 = "";
			for(x=0; x<param.length; x++){
				if(x!==0){
					par += ",";
				}
				par += "'"+param[x]+"'";
			}
			function_call += func+"("+par+");";
		}
	}
	return function_call;
}
function createActionBox(e,num){
	if(!document.getElementById(e)){
		heightrow = 55.67 * num;
		heightbox = 18; 
		translateY = (heightbox+heightrow);
		var both = document.createElement('div');
		both.setAttribute('id',e);
		both.setAttribute('class',sessionStorage.panel+'act action_box');
		both.style.height = translateY+'px';
		both.style.zIndex = -998;
		both.style.transitionDuration = '0s';
		both.style.transform= 'translateY('+translateY+'px)';
		document.body.appendChild(both);
		createBackdrop(e);
		createBackdrop('actbox');
		return both;
	}
}
function createBackdrop(e){
	if(!document.getElementById('darkbackdrop')){
		var backdrop = document.createElement('div');
		backdrop.setAttribute('id','darkbackdrop');
		backdrop.setAttribute('class','progress darkbackdrop');
		backdrop.setAttribute('onClick','closeact(\''+ e +'\')');
		backdrop.setAttribute('lock','gohead');
		document.body.appendChild(backdrop);
		return backdrop;
	}
}
function closeact(e){
	var backdrop = document.getElementById('darkbackdrop');
	backdrop.style.opacity = '0';
	backdrop.style.display = 'none';
	backdrop.style.zIndex = -999;
//=======================================
	var action_box = document.getElementById('actbox');
	var heighBox = action_box.offsetHeight;
	action_box.style.zIndex = -998;
	action_box.style.height = heighBox+'px';
	backdrop.style.transitionDuration = '0.8s'; 
	action_box.style.transitionDuration = '0.2s'; 
	action_box.style.transform = 'translateY('+heighBox+'px)';
	
}
function closeactAll(){
	if(document.getElementsByClassName('action_box')){
		 var act = document.getElementsByClassName('action_box');
		 var backdrop = document.getElementsByClassName('darkbackdrop');
		 if(act.length > 0){
			for(i=0; i<act.length; i++){
				act[i].style.transitionDuration = '0.2s'; 
				act[i].style.transform = 'translateY('+act[i].offsetHeight+'px)';
				backdrop[i].style.opacity = '0';
				backdrop[i].style.display = 'none';
				backdrop[i].style.transitionDuration = '0.8s'; 
				backdrop[i].style.zIndex = -999;
			}
		 }
	 }
}

function Action_RowTable(e,actbox){
	//stop Propaganda event
	ev = this.event;
	stopPropaganda(ev);
	
	var html = '<ul class="action_table">';
	var num = 0;
	
	for (var key in actRolePermission) {
		if (actRolePermission.hasOwnProperty(key)) {
			acttxt = actRolePermission[key];
			if(getAction_data(e,key) && getAction_data(e,key) != ""){
				styletype = "";
				if(acttxt.style != ""){
					styletype = ' style="'+acttxt.style+'"' ;
				}
				html += '<li class="'+acttxt.icon+'" onclick="closeact(\'actbox\');'+getAction_data(e,key)+'" '+styletype+'><a> '+acttxt.text+'</a></li>';
				num = num+1;
			}
		}
	}
	if(document.getElementById('actbox') && document.getElementById('darkbackdrop')){
		var action_box = document.getElementById('actbox');
		var backdrop = document.getElementById('darkbackdrop');
		backdrop.style.zIndex = 999;
		
		html += '</ul>';
		heightrow = 55.67 * num;
		heightbox = 18; 
		translateY = (heightbox+heightrow);
		action_box.style.zIndex = 1000;
		action_box.innerHTML = translateScript(html);
		action_box.style.height = translateY+"px";
		document.body.insertBefore(backdrop,action_box);
		backdrop.style.transitionDuration = '0.8s'; 
		backdrop.style.opacity = '1'; 
		backdrop.style.display = 'block';
		action_box.style.transitionDuration = '0.2s'; 
		action_box.style.transform = 'translateY(0px)';
	}else{
		createActionBox('actbox',num);
		Action_RowTable(e,actbox);
	}
	
}
function printTableCummulative(col,data,footer,action){
	//action[i] = [{"att":"onclick","value":[{"function":"showdetail","param":data[i][0]+","+data[i][1]+","+data[i][2]+","+data[i][3]}]}];
	function getAction(att,data,e){
		
		var dataparam = "";
		var varfunc = "";
		if(Array.isArray(data)){
			func = "";
			for(z=0; z<data.length; z++){
				if (data[z].hasOwnProperty("function")) {
					param = "";
					if (data[z].hasOwnProperty("param")) {
						parameter = data[z]["param"].split(",");
						param += "'"+parameter.join("','")+"',this";
						
					}
					func = data[z]["function"]+"("+param+")";
				}
			}
			e.setAttribute(att,func);
		}
	}
  var table=document.createElement("TABLE");
  table.setAttribute("class","data");
  table.setAttribute("cellspacing","1px");
  table.setAttribute("border","0");

  //col header
  var row=table.insertRow(0);
  row.setAttribute("class","rowHeader");
  var cell=row.insertCell(0);
  var node=document.createTextNode('No');
      cell.setAttribute("class","tdHeader");
      cell.appendChild(node);
  for(x=0;x<col.length;x++){
          cell=row.insertCell(x+1);
          cell.setAttribute("class","tdHeader");
          node=document.createTextNode(translateScript(col[x]));
      cell.appendChild(node);
  }
  //col detail
  if(data.length > 0){
	  for(x=0;x<data.length;x++){
		  row=table.insertRow(x+1);
		  row.setAttribute("class","rowData");
		  if(typeof action !== 'undefined'){
				actRow = action[x];
				for(i=0; i<actRow.length; i++){
					getAction(actRow[i]["att"],actRow[i]["value"],row);
				}
		  }
		  cell=row.insertCell(0);
		  node=document.createTextNode(x+1);
		  cell.setAttribute("class","tdData");
		  cell.appendChild(node);     
		for(y=0;y<data[x].length;y++){
		  span = 1;
		  var cell=row.insertCell(y+1);
		  cell.setAttribute("class","tdData");
		  if(y == (data[x].length-1)){
			if(x < (col.length-1)){
				span = col.length-y;
				cell.setAttribute("colspan",span);
			}
		  }
		  //var node=document.createTextNode(data[x][y]);
		  //cell.appendChild(node);
		  cell.innerHTML=data[x][y];
		}
	  }
  }else{
	row=table.insertRow(1);
	row.setAttribute("class","rowData");
	cell=row.insertCell(0);
	nodata = translateScript("{nodata}");
	node=document.createTextNode(nodata);
	cell.setAttribute("class","tdData");
	cell.setAttribute("colspan",col.length+1);
	cell.appendChild(node);
  }
  if(typeof footer !== 'undefined'){
	  lastrow = data.length;
	  for(x=0;x<footer.length;x++){
		  row=table.insertRow(lastrow+1);
		  row.setAttribute("class","rowData");
		  row.style.background="rgb(255, 245, 107)";
		  cell=row.insertCell(0);
		  node=document.createTextNode('');
		  cell.setAttribute("class","tdData");
		  cell.appendChild(node);     
		for(y=0;y<footer[x].length;y++){
		  var cell=row.insertCell(y+1);
		  cell.setAttribute("class","tdData");
		  //var node=document.createTextNode(data[x][y]);
		  //cell.appendChild(node);
		  cell.innerHTML=footer[x][y];
		}
	  }
  }
return table;
}

function validateDate(d){
 var re= /^[0-9]{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])/;
 if(d.match(re)){
  return true;
 }else{
  return false;
 }
}
/*
function showInnerForm(id){
	df=document.getElementById(id);
	//close all innerForm first
	da=document.getElementsByClassName('innerForm');
	for(x=0;x<da.length;x++){
		if(da[x]!=df){
			da[x].style.display='none';
		}
	}
	if(df.style.display.toString()=='none'|| df.style.display.toString()==''){
		df.style.display='inline-block';
	}else{
		df.style.display='none';
	}
  window.scrollTo(0,0);
}
*/
function searchingOn(ul,e){
	showProgress();
	var warp = document.getElementById(ul); 
	var listdata = warp.getElementsByTagName('li');
	var pattern = e.value.toLowerCase();
	datatext = "";
	for(i=0; i<listdata.length; i++){
		datatext = listdata[i].innerText.toLowerCase();
		index = datatext.indexOf(pattern);
		 if (index != -1){
			listdata[i].style.display = "block";
		  }else{
			listdata[i].style.display = "none";
		  }
		 if(i == listdata.length-1){
			 hideProgress();
		 }
	}
}
function choose_search(elem,event){
	stopPropaganda(event);
	
	closePanel('searchonselect');
	var indexEle = elem.getAttribute('index-choose');
	var idEle = elem.getAttribute('id-choose');
	var e = document.getElementById(idEle);
	var att = e.getAttribute('callback');
	var param = e.getAttribute('param');
	e.options[indexEle].selected = true;
	value = e.options[indexEle].value;
	var allparam = "";
	if(param !== null && param !== ""){
		p = param.split(',');
		for(i=0; i<p.length; i++){
			allparam += ",'"+p[i]+"'";
		}
	}
	if(att !== null && att !== ""){
		funct = att+"('"+value+"'"+allparam+","+idEle+")";
		eval(funct);
	}
}

function otherFieldNopol(e,val){
	function fusionText(ele){
		part1 = document.getElementById('nopolpart_3').value;
		part2 = document.getElementById('nopolpart_2').value;
		part3 = document.getElementById('nopolpart_1').value;
		var result = "";
		part2Txt = "";
		if(part2.trim() != ""){
			part2Txt = " "+part2;
		}
		part3Txt = "";
		if(part3.trim() != ""){
			part3Txt = " "+part3;
		}
		result = part1+part2Txt+part3Txt;
		ele.value = result.toUpperCase();
	}
	input1 = document.createElement('input');
	input2 = document.createElement('input');
	input3 = document.createElement('input');
	input1.id = 'nopolpart_1';
	input2.id = 'nopolpart_2';
	input3.id = 'nopolpart_3';
	input1.setAttribute('type','text');
	input2.setAttribute('type','number');
	input3.setAttribute('type','text');
	input1.setAttribute('class','col-2 m-b-0 p-l-0 p-r-0');
	input2.setAttribute('class','col-6 m-b-0 m-r-5 p-l-0 p-r-0');
	input3.setAttribute('class','col-3 m-b-0 m-r-5 p-l-0 p-r-0');
	input2.setAttribute('min','0');
	input2.setAttribute('min','0');
	input2.setAttribute('max','9999');
	input1.style="text-transform:uppercase;text-align:center";
	input2.style="text-align:center;";
	input3.style="text-transform:uppercase;text-align:center";
	
	input1.setAttribute('maxlength','3');
	
	
	input = document.createElement('input');
	input.id = e.id;
	search = document.getElementById('search'+e.id);
	input.setAttribute('class','otherfield');
	input.setAttribute('type','hidden');
	input.setAttribute('placeholder','Other');
	e.setAttribute('old-id',e.id);
	e.id = "otherfield_"+e.id;
	if(typeof val !== 'undefined' && val !== "" && val !== "other"){
		input.value = val;
		isi = val.split(" ");
		if(typeof isi[0] !== "undefined"){
			input3.value=isi[0];
		}
		if(typeof isi[1] !== "undefined"){
			input2.value=isi[1];
		}
		if(typeof isi[2] !== "undefined"){
			input1.value=isi[2];
		}
	}
	input1.onkeyup = function(x){
		newtag = this.value.replace(" ", "");
		newtag = newtag.replace(",", "");
		newtag = newtag.replace(".", "");
		if (newtag.length >= 3 ) {
			newtag = newtag.substr(0,3);
		}
		this.value = newtag;
		fusionText(input);
	}
	input2.onkeyup = function(x){
		newtag = this.value.replace(".", "");
		newtagnewtag = newtag;
		var val = newtagnewtag.toString();
		var length = parseInt(val.length);
		var jml = 4;
		if(length > jml){
			newtagnewtag = newtagnewtag.slice(0,jml);
			newtagnewtag = Number(newtagnewtag);
		}else{
			newtagnewtag = newtagnewtag;
		}
		console.log(newtagnewtag);
		this.value = newtagnewtag;
		if (length >= jml ) {
			input1.focus(); 
		}
		fusionText(input);
	}
	input3.onkeyup = function(x){
		newtag = this.value.replace(" ", "");
		newtag = newtag.replace(",", "");
		newtag = newtag.replace(".", "");
		this.value = newtag;
		if (newtag.length == 2) {
			input2.focus(); 
		}
		fusionText(input);
	}
	e.parentNode.insertBefore(input, e.nextSibling);
	e.parentNode.insertBefore(input1, e.nextSibling);
	e.parentNode.insertBefore(input2, e.nextSibling);
	e.parentNode.insertBefore(input3, e.nextSibling);
	e.style.display = 'none';
	search.style.display = 'none';
	
	var holdTouchInput = false;
	var timeHold;
	input1.addEventListener('touchstart', function(event) {
	  timeHold = setTimeout(function() {
		  holdTouchInput = true;
	  }, 500);
	});
	input2.addEventListener('touchstart', function(event) {
	  timeHold = setTimeout(function() {
		  holdTouchInput = true;
	  }, 500);
	});
	input3.addEventListener('touchstart', function(event) {
	  timeHold = setTimeout(function() {
		  holdTouchInput = true;
	  }, 500);
	});
	
	input1.addEventListener('touchend', function(event) {
		clearTimeout(timeHold);
		if(holdTouchInput == true){
			holdTouchInput = false;
			message = "Apakah ingin kembali ke bentuk pilihan ?";
			title	  = "{perhatian}";
			buttonLabels = ['{ok}','{batal}'];
			notifConfirm(message,title,buttonLabels,backPilihan);
		}
	});
	input2.addEventListener('touchend', function(event) {
		clearTimeout(timeHold);
		if(holdTouchInput == true){
			holdTouchInput = false;
			message = "Apakah ingin kembali ke bentuk pilihan ?";
			title	  = "{perhatian}";
			buttonLabels = ['{ok}','{batal}'];
			notifConfirm(message,title,buttonLabels,backPilihan);
		}
	});
	input3.addEventListener('touchend', function(event) {
		clearTimeout(timeHold);
		if(holdTouchInput == true){
			holdTouchInput = false;
			message = "Apakah ingin kembali ke bentuk pilihan ?";
			title	  = "{perhatian}";
			buttonLabels = ['{ok}','{batal}'];
			notifConfirm(message,title,buttonLabels,backPilihan);
		}
	});
	function backPilihan(btn){
		if(btn == 1){
			idNow = e.getAttribute('old-id');
			eleRemoved = document.getElementById(idNow);
			eleRemoved.remove();
			
			input1 = document.getElementById('nopolpart_1');
			input2 = document.getElementById('nopolpart_2');
			input3 = document.getElementById('nopolpart_3');
			input1.remove();
			input2.remove();
			input3.remove();
			e.style.display = 'block';
			search = document.getElementById('search'+idNow);
			search.style.display = 'block';
			e.id = idNow;
			e.value = "";
		}
	}
}

function otherField(e,val){
	input = document.createElement('input');
	input.id = e.id;
	search = document.getElementById('search'+e.id);
	input.setAttribute('class','otherfield');
	input.setAttribute('type','text');
	input.setAttribute('placeholder','Other');
	
	e.setAttribute('old-id',e.id);
	e.id = "otherfield_"+e.id;
	if(typeof val !== 'undefined' && val !== "" && val !== "other"){
		input.value = val;
	}
	e.parentNode.insertBefore(input, e.nextSibling);
	e.style.display = 'none';
	search.style.display = 'none';
	var holdTouchInput = false;
	var timeHold;
	input.addEventListener('touchstart', function(event) {
	  timeHold = setTimeout(function() {
		  holdTouchInput = true;
	  }, 500);
	});
	
	input.addEventListener('touchend', function(event) {
		clearTimeout(timeHold);
		if(holdTouchInput == true){
			holdTouchInput = false;
			message = "Apakah ingin kembali ke bentuk pilihan ?";
			title	  = "{perhatian}";
			buttonLabels = ['{ok}','{batal}'];
			notifConfirm(message,title,buttonLabels,backPilihan);
		}
	});
	
	function backPilihan(btn){
		if(btn == 1){
			idNow = e.getAttribute('old-id');
			eleRemoved = document.getElementById(idNow);
			eleRemoved.remove();
			e.style.display = 'block';
			search = document.getElementById('search'+idNow);
			search.style.display = 'block';
			e.id = idNow;
			e.value = "";
		}
	}
}
function searchOnSelect(idele,judul){
	var select = document.getElementById(idele);
	if(select.disabled !== true){
		showProgress();
		frame_panel('searchonselect','Select '+judul,'','penyesuaianSearchonselect('+idele+','+judul+')');
		
	}
}
function asyncInnerHTML(HTML, callback,closeProgress) {
	showProgress();
    var temp = document.createElement('div'),
        frag = document.createDocumentFragment();
    temp.innerHTML = HTML;
    (function(){
        if(temp.firstChild){
            frag.appendChild(temp.firstChild);
            setTimeout(arguments.callee, 0);
        } else {
			if(typeof closeProgress === 'undefined' || (typeof closeProgress !== 'undefined' && closeProgress == true)){
				hideProgress();
			}
            callback(frag);
        }
    })();
}
function penyesuaianSearchonselect(idele,judul){
	var searchonselect = document.getElementById("searchonselect");
	searchonselect.setAttribute("hiding-element","hide");
	var currentSelect = false;
	if(searchonselect.getAttribute("select-element") !== null && searchonselect.getAttribute("select-element") == idele){
		currentSelect = true;
	}
	searchonselect.setAttribute("select-element",idele);
	var select = document.getElementById(idele);
	var allOption = select.getElementsByTagName('option');
	var both_search = document.getElementById('body_searchonselect');
	if(document.getElementById("listsearchonselect")){
		var ul = document.getElementById("listsearchonselect");
		both_search.scrollTop = 0;
		var search_Allel = both_search.getElementsByClassName('searchon');
		if(search_Allel.length > 0){
			var search_el = search_Allel[0];
			search_el.setAttribute('placeholder','Search '+judul);
		}
		if(currentSelect == false){
			ul.innerHTML = "";
			search_el.value = "";
			createList(allOption,ul);
		}else{
			if(allOption.length>0){
				if(allOption[0].getAttribute("current-value") !== idele){
					allOption[0].setAttribute("current-value",idele);
					ul.innerHTML = "";
					search_el.value = "";
					createList(allOption,ul);
				}
			}
		}
	}else{
		var ul = document.createElement('ul');
		ul.id = "listsearchonselect";
		ul.classList.add("searchonselect");
		ul.style ="margin-bottom:36.5px;margin-top:36.5px;";
		var search_el = document.createElement('input');
		search_el.setAttribute('type','search');
		search_el.setAttribute('class','searchon col-12');
		search_el.setAttribute('placeholder','Search '+judul);
		search_el.onsearch = function(){
			searchingOn('listsearchonselect',this);
		}
		both_search.appendChild(ul);
		both_search.appendChild(search_el);
		createList(allOption,ul);
	}
	
	function createList(allOption,ul,search){
		if(typeof search !== 'undefined'){
			//search = "default";
		}
		var isi = "";
		var arrIsi = new Array();
		if(allOption.length > 0){
			allOption[0].setAttribute("current-value",idele);
			var limit = 50;
			var num = 1;
			for(i=0; i<allOption.length; i++){
				var opttext = allOption[i].text.split('||');
				var optVal = allOption[i].value;
				opttextval = "<b>"+opttext[0]+"</b><br/><font size='0.5em'>";
				for(ii=1; ii<opttext.length; ii++){
					opttextval += opttext[ii];
				}
				opttextval += "</font>";
				/*li = document.createElement("li");
				li.setAttribute("value",optVal);
				li.setAttribute("index-choose",i);
				li.setAttribute("id-choose",idele);
				li.setAttribute("onclick","choose_search(this,event);");
				li.innerHTML = opttextval;
				ul.appendChild(li);*/
				isi += '<li value="'+optVal+'" index-choose="'+i+'" id-choose="'+idele+'" onclick="choose_search(this,event);">'+opttextval+'</li>';
				if(num == limit){
					arrIsi.push(isi);
					isi = "";
					num = 1;
				}else if((i+1) == allOption.length){
					arrIsi.push(isi);
					isi = "";
					num = 1;
				}else{
					num++;
				}
			}
			if(arrIsi.length > 0){
				loadListBerkala(ul,arrIsi,0);
			}
		}
	}
	
	function loadListBerkala(ul,arr,num){
		var closeProg = false;
		if((num+1) == arr.length){
			closeProg = true;
		}
		asyncInnerHTML(arr[num], function(fragment){
			num++;
			if(document.getElementById("searchonselect")){
				ul.appendChild(fragment); // myTarget should be an element node.
				if(closeProg == true){
					both_search.scrollTop = 0;
				}else{
					both_search.scrollTop = both_search.scrollHeight - both_search.clientHeight;
					loadListBerkala(ul,arr,num);
				}
			}
		},closeProg);
	}
}
function showInnerTab(id,me){
	if(typeof  me !== 'undefined'){
		var btnTab = document.getElementById(me);
		var btnTabclass = btnTab.className;
	}
	
	function replace_class_(listclas,disp){
		var list = listclas.split(" ");
		allClass = "";
		if(list.length > 0){
			for(x=0;x<list.length;x++){
				if(list[x] == 'tab-close' || list[x] == 'tab-open'){
					if(disp.toUpperCase() == 'BLOCK'){
						allClass += 'tab-open ';
					}else if(disp.toUpperCase() == 'NONE'){
						allClass += 'tab-close ';
					}	
				}else{
					allClass += list[x]+' ';
				}
			}
		}
		return allClass;
	}
	
	function close_allicon(idpanel){
		var tabopen = idpanel.getElementsByClassName('tab-open');
		for(x=0;x<tabopen.length;x++){
			var tabopenClose = tabopen[x].className;	
			tabopen[x].className = replace_class_(tabopenClose,'none');
		}
		
	}
	
	df=document.getElementById(id);
	//close all innerTab first
	da=document.getElementsByClassName('innerTab');
	
	for(x=0;x<da.length;x++){
		if(da[x]!=df){
			da[x].style.display = 'none';
		}
	}
		
	if(df.style.display.toString()=='none'|| df.style.display.toString()==''){
		
		var query = window.location.search.substring(1);	
		var vars = query.split("&");
		var id_panel = vars[0].split("=");
		var idpanel = document.getElementById(id_panel[1]);
		
		close_allicon(idpanel);
		
		var content = idpanel.getElementsByClassName('panel-content')[0];
		df.style.display ='block';
		if(typeof me !== 'undefined'){
			btnTab.className = replace_class_(btnTabclass,'block');
		}
		
		content.scrollTop = 0;
	}
  window.scrollTo(0,0);
}
function showInnerForm(id,me){
	if(typeof  me !== 'undefined'){
		var icon = me.getElementsByTagName('i');
		var iconclass = icon[0].className;
	}
	function replace_class_(listclas,disp){
		var list = listclas.split(" ");
		allClass = "";
		if(list.length > 0){
			for(x=0;x<list.length;x++){
				if(list[x] == 'nav-close' || list[x] == 'nav-open'){
					if(disp.toUpperCase() == 'BLOCK'){
						allClass += 'nav-open ';
					}else if(disp.toUpperCase() == 'NONE'){
						allClass += 'nav-close ';
					}	
				}else{
					allClass += list[x]+' ';
				}
			}
		}
		return allClass;
	}
	function close_allicon(){
		var toggle = document.getElementsByClassName('nav-open');
		for(x=0;x<toggle.length;x++){
			var iconclasslose = toggle[x].className;	
			toggle[x].className = replace_class_(iconclasslose,'none');
		}
		
	}
	df=document.getElementById(id);
	//close all innerForm first
	da=document.getElementsByClassName('innerForm');
	
	for(x=0;x<da.length;x++){
		if(da[x]!=df){
			animate_height(da[x],'none');
		}
	}
	if(df.style.display.toString()=='none'|| df.style.display.toString()==''){
		close_allicon();
		animate_height(df,'block');
		if(typeof me !== 'undefined'){
			icon[0].className = replace_class_(iconclass,'block');
		}
		var query = window.location.search.substring(1);	
		var vars = query.split("&");
		var id_panel = vars[0].split("=");
		var content = document.getElementById(id_panel[1]).getElementsByClassName('panel-content')[0];
		content.scrollTop = 0;
	}else{
		//close_allicon();
		//animate_height(df,'none');
		//if(typeof me !== 'undefined'){
		//	icon[0].className = replace_class_(iconclass,'none');
		//}
	}
  window.scrollTo(0,0);
}
function animate_height(ele,displayStr){
	function showup(){
		ele.style.display='block';
		clearInterval(openTime);
	}
	function hidedown(){
		ele.style.display='none';
		clearInterval(closeTime);
	}
	if(displayStr.toUpperCase() == 'BLOCK'){
		ele.style.opacity = "1";
		ele.style.transitionDuration = '0.2s'; 
		ele.style.transform = 'scaleY(1)';
		ele.style.display='block';
		//var openTime = setInterval(showup,200);
	}if(displayStr.toUpperCase() == 'NONE'){
		ele.style.opacity = "0";
		ele.style.transitionDuration = '0.2s'; 
		ele.style.transform = 'scaleY(0)';
		ele.style.display='none';
		//var closeTime = setInterval(hidedown,200);
	}
}
/*
function animate_height_trash(ele,displayStr){
	//ele2=document.getElementById('bkmPengawas');
	if(displayStr.toUpperCase() == 'BLOCK'){
		ele.style.opacity=0;
		ele.style.display='block';
		ele.style.overflowY = 'hidden';
		var hd = 0;
		hd = ele.offsetHeight;
		if(!ele.getAttribute('first-height')){
			ele.setAttribute('first-height',hd);
		}
		ele.style.height='0px';
		ele.style.opacity=1;
		var animate_heightVar =window.setInterval(open_div,1);
	
	}if(displayStr.toUpperCase() == 'NONE'){
		ele.style.overflowY = 'hidden';
		var animate_height2Var =window.setInterval(close_div,1);
	}
	function open_div(){
		var curLeft_obj = ele.offsetHeight;
		var xheigh = ele.getAttribute('first-height');
		var x = 1;

		if(curLeft_obj < xheigh){
			curLeft_obj 	= curLeft_obj + x;
			ele.style.height=curLeft_obj+'px';
		}else{
			ele.style.height	= xheigh+'px';
			ele.style.opacity 	= null;
			ele.style.overflowY = null;
			ele.style.height 	= null;
			clearInterval(animate_heightVar);
			x = null;
			
		}
	}
	function close_div(){
		var curH_obj = ele.offsetHeight;
		var x = 20;
		curH_obj = curH_obj-x;
		if(curH_obj > 4){
			ele.style.height=curH_obj+'px';
		}else{
			ele.style.height= 0+'px';
			ele.style.display='none';
			ele.style.overflowY = null;
			ele.style.height = ele.getAttribute('first-height')+'px';
			clearInterval(animate_height2Var);
			x = null;
		}
	}
}
*/
function playSound(src) {
    this.sound = document.createElement("audio");
    this.sound.src = src;
    this.sound.setAttribute("preload", "auto");
    this.sound.setAttribute("controls", "none");
    this.sound.setAttribute("type", "audio/mpeg");
    this.sound.style.display = "none";
    document.body.appendChild(this.sound);
    this.sound.play();
   // this.sound.pause();
}
function numFormat(num) {    
    return +(Math.round(num + "e+2")  + "e-2");
}
function numberFormat(number,digit) {
      number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
      //Seperates the components of the number
      var components = (parseFloat(number).toFixed(digit)).split(".");
      //Comma-fies the first part
      components [0] = components [0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      //Combines the two sections
      return components.join(".");
}

function tanggalSekarang() {
    var d = new Date();
    var y = d.getFullYear();
    var m = d.getMonth()+1;
    var d = d.getDate();
	result = y+"-"+m.lpad(2,"0")+"-"+d.lpad(2,"0");
	return result;
}
function timeSekarang() {
    var d = new Date();
	var H = d.getHours();
	var i = d.getMinutes();
	var s = d.getSeconds();
 
	result = H.lpad(2,"0")+":"+i.lpad(2,"0")+":"+s.lpad(2,"0");
	return result;
}
/*
i : 2015-03-03
O : 03-03-2015
*/
function tanggalnormal(tgl){
	tgl=tgl.split("-");
	tgl=tgl[2]+'-'+tgl[1]+'-'+tgl[0];
	return tgl;
}

/*
i : 2015-03-03
O : 03-03-15
*/
function tanggalformat1(tgl){
	tgl=tgl.split("-");
	tgl=tgl[2]+'-'+tgl[1]+'-'+tgl[0].substr(2,2);
	return tgl;
}

// function numberFormat(n, digit) {
//   if(digit==''){
//     digit=0;
//   }
//     return parseFloat(n).toFixed(digit).replace(/./g, function(c, i, a) {
//         return i > 0 && c !== "." && (a.length - i) % 3 === 0 ? "," + c : c;
//     });
// }
function getPeriode(idmasupan,idhasil){
  //idmasupan mewakili object id untuk kodeorg yang akan di kirim ke server
  //idhasil adalah untuk object menerima kiriman data dari server
  afdelingId =getValue(idmasupan);
  param='method=addtional&tipelap=ambilperiode&username='+sessionStorage.username+'&password='+sessionStorage.password+
                  '&kodeorg='+afdelingId+'&uuid='+sessionStorage.imei;
    post_response_text(sessionStorage.server+'/owlMobile.php', param, respog);
    function respog(){
        hideProgress();
        if(con.readyState==4)
        {
          if (con.status == 200) {
              if (!isSaveResponse(con.responseText)) {
                  notifAlert('ERROR TRANSACTION,\n' + con.responseText,'{error}');
              }
              else{
                        var hslnya=JSON.parse(con.responseText);
                        ds=document.getElementById(idhasil);
                        ds.length=0;
                        ds.options[ds.length]=new Option("","",false,false);
                        for(var i=0; i<hslnya.length; i++) {
                            ds.options[ds.length]= new Option(hslnya[i].periode,hslnya[i].periode,false,false);
                        }
              }
          }
          else {
              error_catch(con.status);
          }
        } 
    }
}
function getNoakun(idmasupan,idhasil,whrkond){
  afdelingId =getValue(idmasupan);
  param='method=addtional&tipelap=ambilnoakun&username='+sessionStorage.username+'&password='+sessionStorage.password+
                  '&kodeorg='+afdelingIdu+'&whrKond='+whrkond+'&uuid='+sessionStorage.imei;
    post_response_text(sessionStorage.server+'/owlMobile.php', param, respog);
    function respog(){
        hideProgress();
        if(con.readyState==4)
        {
          if (con.status == 200) {
              if (!isSaveResponse(con.responseText)) {
                  notifAlert('ERROR TRANSACTION,\n' + con.responseText,'{error}');
              }
              else{
                        var hslnya=JSON.parse(con.responseText);
                        ds=document.getElementById(idhasil);
                        ds.length=0;
                        ds.options[ds.length]=new Option("","",false,false);
                        for(var i=0; i<hslnya.length; i++) {
                            ds.options[ds.length]= new Option(hslnya[i].periode,hslnya[i].periode,false,false);
                        }
              }
          }
          else {
              error_catch(con.status);
          }
        } 
    }
}
/*******************************************************************************
	#Author - Atwal
 *******************************************************************************/
var fxhead = [];
function fixing_headtable(e,b){
	var bothid = document.getElementById(b);
	var tableid = bothid.getElementsByClassName(e);
	
	if(tableid.length > 0 ){
		if(!fxhead[sessionStorage.panel]){
			fxhead[sessionStorage.panel] = new KepalaTableNgegantung(e,b);
		}
	}
}
/*******************************************************************************
	#END : function headerfixed
 *******************************************************************************/
// Translate Script
function loadJSON(JSONscript,callback) {  
    var xobj = new XMLHttpRequest();
        xobj.overrideMimeType("application/json");
    xobj.open('GET', JSONscript, true); // Replace 'my_data' with the path to your file
    xobj.onreadystatechange = function () {
          if (xobj.readyState == 4 && xobj.status == "200") {
			callback(xobj.responseText);
          }
    };
    xobj.send(null);  
}

function translateScriptExec(xhtml){
	var resultHTML = xhtml;
	if(resultHTML !== ""){
		var xhtml = resultHTML.replace(/{(.*?)}/g,function (match, capture) {
			capture = capture.toLowerCase();
			if(typeof eval('language.'+[capture]) !== 'undefined'){
				return eval('language.'+[capture]);
			}else{
				return match;
			}
		}); 
	}
	return xhtml;
}
function translateScript(html){
	var resultHTML = html;
	if(language.hasOwnProperty('langversion') == false){
		loadJSON('lang/'+sessionStorage.lang+'.json',function(response) {
			try{
				language = JSON.parse(response);
			}catch(e){
				console.log("Load language : "+e);
			}
		});
	}
	resultHTML = translateScriptExec(html);
	return resultHTML;
}
//Scaning Script
function scaningScriptJava(myscript){
	console.log('Scaning Script..');
	if(document.getElementById(myscript)){
		theScript = document.getElementById(myscript);
		nodeScriptReplace(theScript);
	}
}
function nodeScriptReplace(node) {
	if ( nodeScriptIs(node) === true ) {
			node.parentNode.replaceChild( nodeScriptClone(node) , node );
	}else {
		var i        = 0;
		var children = node.childNodes;
		while ( i < children.length ) {
			nodeScriptReplace( children[i++] );
		}
	}
	return node;
}
function nodeScriptIs(node) {
        return node.tagName === 'SCRIPT';
}
function nodeScriptClone(node){
	var script  = document.createElement("script");
	script.text = node.innerHTML;
	for( var i = node.attributes.length-1; i >= 0; i-- ) {
		script.setAttribute( node.attributes[i].name, node.attributes[i].value );
	}
	return script;
}
/*******************************************************************************
	#END : function Translate
 *******************************************************************************/
function signatureRead(signature_img,callback){
	var pembungkus = document.getElementById("signature-pad"),
    clearButton = pembungkus.querySelector("[data-action=clear]"),
    savePNGButton = pembungkus.querySelector("[data-action=save-png]"),
    canvas = pembungkus.querySelector("canvas"),
    signaturePad;

	// Adjust canvas coordinate space taking into account pixel ratio,
	// to make it look crisp on mobile devices.
	// This also causes canvas to be cleared.
	function resizeCanvas(){
		// When zoomed out to less than 100%, for some very strange reason,
		// some browsers report devicePixelRatio as less than 1
		// and only part of the canvas is cleared then.
		var ratio =  Math.max(window.devicePixelRatio || 1, 1);
		canvas.width = canvas.offsetWidth * ratio;
		canvas.height = canvas.offsetHeight * ratio;
		canvas.getContext("2d").scale(ratio, ratio);
	}

	window.onresize = resizeCanvas;
	resizeCanvas();
	signaturePad = new SignaturePad(canvas);
	clearButton.addEventListener("click", function (event) {
		signaturePad.clear();
	});
	savePNGButton.addEventListener("click", function (event) {
		if (signaturePad.isEmpty()) {
			notifAlert("Please provide signature first.",'{perhatian}');
		} else {
			signature_img.src = signaturePad.toDataURL();
			signature_img.style.display = 'block';
			signature_img.style.height = "100px";
			signature_img.style.background = '#FFF';
			closePanel();
			if(typeof callback !== 'undefined'){
				eval(callback(signaturePad.toDataURL()));
			}
		}
	});
}
function resizeImage(img,maxSize){
	var resizedImage;
    // Read in file
    var file = img;

    // Ensure it's an image
	console.log(file);
    if(file.type.match(/image.*/)) {
        console.log('An image has been loaded');
        // Load the image
        var reader = new FileReader();
        reader.onload = function (readerEvent) {
            var image = new Image();
            image.onload = function (imageEvent) {
                // Resize the image
                var canvas = document.createElement('canvas'),
                    max_size = maxSize,
                    width = image.width,
                    height = image.height;
                if (width > height) {
                    if (width > max_size) {
                        height *= max_size / width;
                        width = max_size;
                    }
                } else {
                    if (height > max_size) {
                        width *= max_size / height;
                        height = max_size;
                    }
                }
                canvas.width = width;
                canvas.height = height;
                canvas.getContext('2d').drawImage(image, 0, 0, width, height);
                resizedImage = canvas.toDataURL('image/jpeg');
            }
            image.src = readerEvent.target.result;
        }
        reader.readAsDataURL(file);
    }
}

function clearLocalStorage(){
    return localStorage= null;
}
// Use to convert from lat long dist to meters
function measure(lat1, lon1, lat2, lon2){ 
	// generally used geo measurement function
    var R = 6378.137; // Radius of earth in KM
    var dLat = (lat2 - lat1) * Math.PI / 180;
    var dLon = (lon2 - lon1) * Math.PI / 180;
    var a = Math.sin(dLat/2) * Math.sin(dLat/2) +
    Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
    Math.sin(dLon/2) * Math.sin(dLon/2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    var d = R * c;
	toMeter = Math.round(d * 1000);
    return toMeter; // meters
}

function resizeImg(imageObject, maxWidth, maxHeight){

	// Max size for thumbnail
	if(typeof(maxWidth) === 'undefined')  maxWidth = 300;
	if(typeof(maxHeight) === 'undefined')  maxHeight = 300;

	  // Create and initialize two canvas
	  var canvas = document.createElement("canvas");
	  var ctx = canvas.getContext("2d");
	  var canvasCopy = document.createElement("canvas");
	  var copyContext = canvasCopy.getContext("2d");
  
	  // Determine new ratio based on max size
	  var ratio = 1;
	  if(imageObject.width > maxWidth)
		ratio = maxWidth / imageObject.width;
	  else if(imageObject.height > maxHeight)
		ratio = maxHeight / imageObject.height;

	  // Draw original image in second canvas
	  canvasCopy.width = imageObject.width;
	  canvasCopy.height = imageObject.height;
	  copyContext.drawImage(imageObject, 0, 0);
	  
	  
	  // Copy and resize second canvas to first canvas
	  canvas.width = imageObject.width * ratio;
	  canvas.height = imageObject.height * ratio;
	  ctx.drawImage(canvasCopy, 0, 0, canvasCopy.width, canvasCopy.height, 0, 0, canvas.width, canvas.height);
	return  canvas;
  
}

// text to image 
function createImageText(text,size){
	function trimCanvas(c) {
		var ctx = c.getContext('2d'),
			copy = document.createElement('canvas').getContext('2d'),
			pixels = ctx.getImageData(0, 0, c.width, c.height),
			l = pixels.data.length,
			i,
			bound = {
				top: null,
				left: null,
				right: null,
				bottom: null
			},
			x, y;
		
		// Iterate over every pixel to find the highest
		// and where it ends on every axis ()
		for (i = 0; i < l; i += 4) {
			if (pixels.data[i + 3] !== 0) {
				x = (i / 4) % c.width;
				y = ~~((i / 4) / c.width);

				if (bound.top === null) {
					bound.top = y;
				}

				if (bound.left === null) {
					bound.left = x;
				} else if (x < bound.left) {
					bound.left = x;
				}

				if (bound.right === null) {
					bound.right = x;
				} else if (bound.right < x) {
					bound.right = x;
				}

				if (bound.bottom === null) {
					bound.bottom = y;
				} else if (bound.bottom < y) {
					bound.bottom = y;
				}
			}
		}
		
		// Calculate the height and width of the content
		var trimHeight = bound.bottom - bound.top,
			trimWidth = bound.right - bound.left,
			trimmed = ctx.getImageData(bound.left-5, bound.top-5, trimWidth+10, trimHeight+10);

		copy.canvas.width = trimWidth+10;
		copy.canvas.height = trimHeight+10;
		copy.putImageData(trimmed, 0, 0);

		// Return trimmed canvas
		return copy.canvas;
	}
	
	
	var canvas = document.createElement('canvas');
	var tCtx = canvas.getContext('2d'), //Hidden canvas
	result = "";
	tCtx.font = size+"px Arial";
	var txt = text;
	tCtx.fillText(txt, 0, size);
	result = trimCanvas(tCtx.canvas).toDataURL();
	return result;
}

// counter

function getDottedSpacePanen(textleft,textright,ext,str){
	function loopstr(str,jml){
		var char_ = str; 
		var i;
		for(i=0; i<jml; i++){
			char_ += str;
		}
		return char_;
	}
	maxlength = 28;
	txtext = "";
	if(ext.length>4){
		txtext = "";
	}else if(ext.length<4){
		console.log(ext.length);
		plusSpace = 4-ext.length;
		txtext = "";
		for(i=0; i<plusSpace; i++){
			txtext += " ";
		}
		txtext += ext;
	}else{
		txtext = ext;
	}
	if(ext == ""){
	var result = textleft+" "+textright;
	}else{
	var result = textleft+textright+" "+txtext;
	}
	if(result.length <= maxlength ){
		sisa = maxlength-result.length;
		dotted = loopstr(str,sisa);
		result = textleft+" "+dotted+" "+textright+" "+txtext;
	}
	return result;
}
function getSpacePanen(textleft,textright,max){
	function loopstr(str,jml){
		var char_ = str; 
		var i;
		for(i=1; i<jml; i++){
			char_ += str;
		}
		return char_;
	}
	maxlength = max;
	result = textleft+textright
	if(result.length < maxlength ){
		sisa = maxlength-result.length;
		spaces = loopstr(" ",sisa);
		result = textleft+spaces+textright;
	}
	return result;
}
function colomnPrint(jml,txt,sparate){
	var maxlength = 30;
	var txtresult = "";
	var space = "";
	if(typeof sparate !== "undefined" && sparate == "sparate"){
		space = "  ";
	}
	txtdata = txt.split("#");
	txtFirst = "";
	txtSecond = "";
	if(typeof txtdata[0] !== "undefined"){
		txtFirst = txtdata[0];
	}
	if(typeof txtdata[1] !== "undefined"){
		txtSecond = txtdata[1];
	}
	switch(jml){
		case 1:
			txtresult += getSpacePanen(txtFirst,txtSecond,maxlength);
		break;
		case 2:
			maxlength = maxlength-2;
			collength = maxlength/2;
			txtdata = txt.split("#");
			txtresult = getSpacePanen(txtFirst,txtSecond,collength)+space;
		break;
		case 3:
			maxlength = maxlength-4;
			collength = maxlength/3;
			txtdata = txt.split("#");
			txtresult = getSpacePanen(txtFirst,txtSecond,collength)+space;
		break;
	}
	return txtresult;
}
// Set to developer
var goDevtime= 0;
function closeCount_godev(){
	clearInterval(runingTimegoDev);
	goDevtime = 0 ;
}
function godeveloper(){
	if(sessionStorage.developer !== "true"){
		stopPropaganda(this.event);
		if(sessionStorage.developer !== "true"){
			goDevtime = goDevtime+1;
			sessionStorage.accuracy = goDevtime;
			if(goDevtime == 1){
				runingTimegoDev = setInterval(function(){closeCount_godev()},3000);
			}
			if(goDevtime == 6){
				sessionStorage.developer = "true";
				notifAlert("You are Developer");	
				getUserMenu();
			}
		}else{	
			notifAlert("You have become a developer");
		}
	}
}
function noTransaksi(){
	var userid = sessionStorage.karyawanid;
	nouserid = userid.substring(userid.length - 10, userid.length);
	date=new Date();
	Y = date.getFullYear();
	m = date.getMonth()+1;
	d = date.getDate();
	H = date.getHours();
	i = date.getMinutes();
	s = date.getSeconds();
	
	return Y.toString()+m.lpad(2,"0").toString()+d.lpad(2,"0").toString()+H.lpad(2,"0").toString()+i.lpad(2,"0").toString()+s.lpad(2,"0").toString()+nouserid;
}

function getTanggalx(){
	date=new Date();
	Y = date.getFullYear();
	m = date.getMonth()+1;
	d = date.getDate();
	H = date.getHours();
	i = date.getMinutes();
	s = date.getSeconds();
	
	var Tanggalx=Y.toString()+"-"+m.lpad(2,"0").toString()+"-"+d.lpad(2,"0").toString();
  return Tanggalx;
}
 // datepart: 'y', 'm', 'w', 'd', 'h', 'n', 's'
Date.dateDiff = function(datepart, fromdate, todate) {	
  datepart = datepart.toLowerCase();	
  var diff = todate - fromdate;	
  var divideBy = { w:604800000, 
                   d:86400000, 
                   h:3600000, 
                   n:60000, 
                   s:1000 };	
  
  return Math.floor(diff/divideBy[datepart]);
}
function maxLengthTypeNumber(e,num){
	var val = e.value.toString();
	var length = parseInt(val.length);
	var jml = parseInt(num);
	if(length > jml){
		value = val.slice(0,jml);
		e.value = Number(value);
		console.log(Number(value));
	}else{
		val = val;
	}
}
function maxLengthTypeNumberJJg(e,num){
	if(isNaN(e.value) == false){
		var val = e.value.toString();
		var length = parseInt(val.length);
		var jml = parseInt(num);
		value = val.slice(0,jml);
		e.value = parseInt(value);
	}else{
		e.value = 0;
	}
}
function versionName(verNumber){
	console.log(verNumber);
	var vername = "";
	if(typeof verNumber !== "undefined" && verNumber != ""){
		Major	= verNumber.substr(0,1);
		Minor   = verNumber.substr(2,1);
		Build	= verNumber.substr(4,1);
		vername = Major+"."+Minor+"."+Build;
		return vername;
	}
	
}
function waitingForprint(type){
	progress = document.getElementsByClassName("progress");
	switch(type){
		case'wait':
			if(progress.length>0){
				progress[0].classList.add("print_loading");
				progress[0].style.display = "block";
			}
		break;
		case'done':
			if(progress.length>0){
				progress[0].classList.remove("print_loading");
				progress[0].style.display = null;
			}
		break;
	}
}

//slidertouch 
//author Atwal Arifin
function slidertouch(){
	var holderForWidth = document.getElementsByClassName('holder');
	
	 widthHolder = holderForWidth[0].getAttribute("jumlah-data");
	 var looper = 0;
	 var looperW = 100;
	 if(widthHolder){
		looper = parseInt(widthHolder);
		looperW = (parseInt(widthHolder)*100);
		looperW = (parseInt(widthHolder)*100);
		holderForWidth[0].style.width = looperW+"%";
	 }else{
		 notifAlert("jumlah-data Undefined !!","ERROR");
	 }
	 var slideWrapper = document.getElementsByClassName('slide-wrapper');
	Array.from(slideWrapper).forEach(function(eleslideWrapper) {
		eleslideWrapper.style.width = (100/looper)+"%";
	});
	if (navigator.msMaxTouchPoints) {
		
	  document.getElementById("slider").classList.add("ms-touch");
	  document.getElementById("slider").onscroll = function(){
		  slideimage = document.getElementsByClassName('slide-both');
		  Array.from(slideimage).forEach(function(element) {
			  element.style.transform = 'translate3d(-' + (100-$(this).scrollLeft/6) + 'px,0,0)';
		  });
	  };

	} else {

	  var slider = {

		el: {
		  slider: document.getElementById("slider"),
		  holder: document.getElementsByClassName('holder'),
		  slideBoth: document.getElementsByClassName('slide-both')
		},

		slideWidth: document.getElementById("slider").offsetWidth,
		touchstartx: undefined,
		touchmovex: undefined,
		movex: undefined,
		index: 0,
		longTouch: undefined,
		
		init: function() {
		  this.bindUIEvents();
		},

		bindUIEvents: function() {
		  Array.from(this.el.holder).forEach(function(holder) {
			 holder.addEventListener('touchstart', function(event) {
				slider.start(event);
			}
			, false);
		  });
		   Array.from(this.el.holder).forEach(function(holder) {
			 holder.addEventListener('touchmove', function(event) {
				slider.move(event);
			}
			, false);
		   });
		   Array.from(this.el.holder).forEach(function(holder) {
			 holder.addEventListener('touchend', function(event) {
				slider.end(event);
			}
			, false);
		   });

		},

		start: function(event) {
		  // Test for flick.
		  this.longTouch = false;
		 setTimeout(function(){
			 console.log("false");
			window.slider.longTouch = true;
		  }, 250);

		  // Get the original touch position.
		  this.touchstartx =  event.touches[0].pageX;
		  // The movement gets all janky if there's a transition on the elements.
		  animateClass = document.getElementsByClassName('animate');
		  Array.from(animateClass).forEach(function(aniclass) {
			  aniclass.classList.remove("animate");
		  });
		},

		move: function(event) {
		  // Continuously return touch position.
		  this.touchmovex =  event.touches[0].pageX;
		  // Calculate distance to translate holder.
		  //console.log(this.index*this.slideWidth,(this.touchstartx - this.touchmovex));
		  this.movex = this.index*this.slideWidth + (this.touchstartx - this.touchmovex);
		  // Defines the speed the images should move at.
		  widthHolder = parseInt(document.getElementsByClassName('holder')[0].offsetWidth);
		 // widthSlide = parseInt(document.getElementsByClassName('slider')[0].offsetWidth);
		  //divide = looper*2;
		  //var panx = 100-this.movex/divide;
		 // console.log(this.index,this.slideWidth,this.touchstartx,this.touchmovex);
		 
		  if (this.movex < widthHolder) { // Makes the holder stop moving when there is no more content.
				this.el.holder[0].style.transform = 'translate3d(-' + this.movex + 'px,0,0)';
		  }
		  //if (panx < 100) { // Corrects an edge-case problem where the background image moves without the container moving.
			//Array.from(this.el.slideBoth).forEach(function(eleslideBoth) {
			//	eleslideBoth.style.transform = 'translate3d(-' + panx + 'px,0,0)';
			//});
		  //}
		},

		end: function(event) {
		  // Calculate the distance swiped.
		  var absMove = Math.abs(this.index*this.slideWidth - this.movex);
		  // Calculate the index. All other calculations are based on the index.
		  devider = parseInt(looper-1);
		  //console.log(absMove,this.movex,this.index,this.slideWidth,devider);
		  
		  if (absMove > this.slideWidth/devider || this.longTouch === false) {
			if (this.movex > (this.index*this.slideWidth+(this.slideWidth/4)) && this.index < devider) {
			  this.index++;
			  this.movex = this.index*this.slideWidth;
			} else if (this.movex < (this.index*this.slideWidth-(this.slideWidth/4)) && this.index > 0) {
			  this.index--;
			  this.movex = this.index*this.slideWidth;
			}
		  }
		   //console.log(this.index,this.slideWidth);
		  // Move and animate the elements.
			this.el.holder[0].classList.add("animate");
			this.el.holder[0].style.transform = 'translate3d(-' + this.index*this.slideWidth + 'px,0,0)';
			
		  //Array.from(this.el.slideBoth).forEach(function(eleslideBoth) {
			//	eleslideBoth.classList.add("animate");
			//	eleslideBoth.style.transform = 'translate3d(-' + 100-this.index*50 + 'px,0,0)';
			//});
		}

	  };

	  slider.init();
	}
}
function blinkItElement(ele){
	function bgChanger(end) {
		if(end == "END"){
			ele.style.backgroundColor = null;
		}else{
			ele.style.backgroundColor = "#" + end;
		}
	}
	setTimeout(function() { bgChanger("FFFFFF")}, 0);
	setTimeout(function() { bgChanger("END")}, 100);
	setTimeout(function() {bgChanger("FFFFFF")}, 200);
	setTimeout(function() {bgChanger("END")}, 300);
	setTimeout(function() {bgChanger("FFFFFF")}, 400);
	setTimeout(function() {bgChanger("END")}, 600);
}

//=============================================================================================
// Aktivasi Javascript module
//Scaning Script

function create_script(scriptname,formjs,callback){
	//console.log("Read Js");
	showProgress();
	if(typeof formjs !== "undefined"){
		scriptname = formjs;
	}
	if(document.getElementById("myscript_"+scriptname)){
		callback();
	}else{
		var path = "js/formjs/"+scriptname+".js";
		var Myscript = document.createElement('script');
		Myscript.setAttribute('id','myscript_'+scriptname);
		Myscript.src = path;
		Myscript.setAttribute('type', 'text/javascript');
		Myscript.setAttribute('async', true);
		Myscript.onerror = function(){
			callback();
			document.getElementById("myscript_"+scriptname).remove();
			Myscript.onerror = null;
		  };
		Myscript.onload = Myscript.onreadystatechange = function() {
		if (!this.readyState || this.readyState === 'loaded' || this.readyState === 'complete') {
			callback();
			Myscript.onload = Myscript.onreadystatechange = null;
		}
	  };
	  document.body.appendChild(Myscript);
	}
  
	
}
//Scaning Script
function scaningScriptJava(scriptEle){
	console.log('Scaning Script..');
	if(scriptEle){
		theScript = scriptEle;
		nodeScriptReplace(theScript);
	}
}
function nodeScriptReplace(node) {
	if ( nodeScriptIs(node) === true ) {
			node.parentNode.replaceChild( nodeScriptClone(node) , node );
	}else {
		var i        = 0;
		var children = node.childNodes;
		while ( i < children.length ) {
			nodeScriptReplace( children[i++] );
		}
	}
	return node;
}
function nodeScriptIs(node) {
        return node.tagName === 'SCRIPT';
}
function nodeScriptClone(node){
	var script  = document.createElement("script");
	script.text = node.innerHTML;
	for( var i = node.attributes.length-1; i >= 0; i-- ) {
		script.setAttribute( node.attributes[i].name, node.attributes[i].value );
	}
	return script;
}
function pseudoStyle(element,topval,bottomval,hChild){
	var _sheetId = "pseudoStyles";
	var _head = document.head || document.getElementsByTagName('head')[0];
	var _sheet = document.getElementById(_sheetId) || document.createElement('style');
	_sheet.id = _sheetId;
	parentId = element.id;
	var className = "pseudoStyle" + parentId;
	
	if (!element.classList.contains(className)) {
		element.classList.add(className);
	}
	if(topval != "" && bottomval != ""){
		_sheet.innerHTML = " ."+className+":before{top:"+topval+";height:"+(hChild*2)+"px;}";
		_sheet.innerHTML += " ."+className+":after{top:"+bottomval+";height:"+(hChild*4)+"px;}";
	}else{
		topval = -(hChild*2);
		bottomval = hChild;
		_sheet.innerHTML = " ."+className+":before{top:"+topval+";height:"+(hChild*2)+"px;}";
		_sheet.innerHTML += " ."+className+":after{top:"+bottomval+";height:"+(hChild*4)+"px;}";
	}
	if(!document.getElementById(_sheetId)){
		_head.appendChild(_sheet);
	}
}
var tableToExcel = (function () {
	var uri = 'data:application/vnd.ms-excel;base64,'
	, tmplWorkbookXML = '<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?><Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
      + '<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office"><Author>Axel Richter</Author><Created>{created}</Created></DocumentProperties>'
      + '<Styles>'
      + '<Style ss:ID="Currency"><NumberFormat ss:Format="Currency"></NumberFormat></Style>'
      + '<Style ss:ID="Date"><NumberFormat ss:Format="Medium Date"></NumberFormat></Style>'
      + '</Styles>' 
      + '{worksheets}</Workbook>'
	, tmplWorksheetXML = '<Worksheet ss:Name="{nameWS}"><Table>{rows}</Table></Worksheet>'
	, tmplCellXML = '<Cell{attributeStyleID}{attributeFormula}><Data ss:Type="{nameType}">{data}</Data></Cell>'
	, base64 = function (s) { return window.btoa(unescape(encodeURIComponent(s))) }
	, format = function (s, c) { return s.replace(/{(\w+)}/g, function (m, p) { return c[p]; }) }
	return function (filename,appname,iddiv) {
		//data-type : ["DateTime","Number","Boolean","Error"];
		//data-style : ["Date","Currency"];
		//data-value : isi yang akan di keluarkan;
		if(typeof iddiv !== "undefined" && iddiv != ""){
			iddiv = document.getElementById(iddiv);
			tables = iddiv.getElementsByTagName("table");
			console.log("by:DIV Table");
		}else{
			tables = document.getElementsByTagName("table");
		}
		var ctx = "";
		var workbookXML = "";
		var worksheetsXML = "";
		var rowsXML = "";

		for (var i = 0; i < tables.length; i++) {
			check_print = tables[i].getAttribute("data-print");
			name = tables[i].getAttribute("name");
			if(check_print == "true"){
				if (!tables[i].nodeType) tables[i] = document.getElementById(tables[i]);
				for (var j = 0; j < tables[i].rows.length; j++) {
				  rowsXML += '<Row>'
				  for (var k = 0; k < tables[i].rows[j].cells.length; k++) {
					var dataType = tables[i].rows[j].cells[k].getAttribute("data-type");
					var dataStyle = tables[i].rows[j].cells[k].getAttribute("data-style");
					var dataValue = tables[i].rows[j].cells[k].getAttribute("data-value");
					dataValue = (dataValue)?dataValue:tables[i].rows[j].cells[k].innerHTML;
					var dataFormula = tables[i].rows[j].cells[k].getAttribute("data-formula");
					dataFormula = (dataFormula)?dataFormula:(appname=='Calc' && dataType=='DateTime')?dataValue:null;
					ctx = {  attributeStyleID: (dataStyle=='Currency' || dataStyle=='Date')?' ss:StyleID="'+dataStyle+'"':''
						   , nameType: (dataType=='Number' || dataType=='DateTime' || dataType=='Boolean' || dataType=='Error')?dataType:'String'
						   , data: (dataFormula)?'':dataValue
						   , attributeFormula: (dataFormula)?' ss:Formula="'+dataFormula+'"':''
						  };
					rowsXML += format(tmplCellXML, ctx);
				  }
				  rowsXML += '</Row>'
				}
				ctx = {rows: rowsXML, nameWS: name || 'Sheet' + i};
				worksheetsXML += format(tmplWorksheetXML, ctx);
				rowsXML = "";
			}
		}
		ctx = {created: (new Date()).getTime(), worksheets: worksheetsXML};
		workbookXML = format(tmplWorkbookXML, ctx);
		
		var link = document.createElement("A");
		  link.href = uri + base64(workbookXML);
		  link.download = filename || 'Workbook.xls';
		  link.click();
	}
})();
function downloadPrintTable(functionApp,filename,appname,iddiv){
	switch (functionApp.toLowerCase()){
		case 'excel':
			tableToExcel(filename,appname,iddiv);
		break;
	}
}
function searchmap(type){
	switch(type){
		case 'open':
			if(document.getElementById('header')){
				var header = document.getElementById('header');
				var backsearchmap = document.getElementById('backsearchmap');
				var search_map = document.getElementById('search_map');
				var cachesearchboth = document.getElementById('cache-search-both');
				var tssLabel = document.getElementsByClassName('tss-label');
				header.style.height="100%";
				header.style.backgroundColor ="rgb(245,245,245)";
				cachesearchboth.style.opacity = 1;
				cachesearchboth.style.height =null;
				backsearchmap.style.display = "block";
				if(tssLabel.length>0){
					tssLabel[0].style.display = "none";
				}
				var button = cachesearchboth.getElementsByTagName('li');
				if(button.length > 0){
					for(i=0; i<button.length; i++){
						button[i].onclick = function(event){
							searchmap('close');
						}
					}
				}
				backsearchmap.onclick = function(event){
					searchmap('close');
				}
			}
		break;
		case 'close':
			if(document.getElementById('header')){
				var header = document.getElementById('header');
				var backsearchmap = document.getElementById('backsearchmap');
				var cachesearchboth = document.getElementById('cache-search-both');
				var tssLabel = document.getElementsByClassName('tss-label');
				header.style.height = null;
				header.style.backgroundColor = null;
				cachesearchboth.style.opacity = null;
				cachesearchboth.style.height = "0px";
				backsearchmap.style.display = null;
				if(tssLabel.length>0){
					tssLabel[0].style.display = null;
				}
			}
		break;
	}
	
}

function openNaveFoter(e){
	var parentEle = e.parentNode;
	var footnavmap = parentEle.getElementsByClassName('active');
	if(footnavmap.length>0){
		for(i=0; i<footnavmap.length; i++){
			if(footnavmap[i].getAttribute("content-id") !== e.getAttribute("content-id")){
				footnavmap[i].classList.remove('active');
			}else{
				footnavmap[i].classList.add('active');
			}
		}
	}	
	var _conentId = e.getAttribute('content-id');
	e.classList.add('active');
	openContent(_conentId);
	function openContent(id){
		if(document.getElementById(id)){
			contentid = document.getElementById(id);
			var parentContent = contentid.parentNode;
			var fdiv= parentContent.getElementsByClassName('footer-nav-content');
			if(fdiv.length>0){
				for(i=0; i<fdiv.length; i++){
					if(fdiv[i].id !== id){
						fdiv[i].classList.remove('active');
					}else{
						fdiv[i].classList.add('active');
					}
				}
			}	
			
		}
	}
}
function mapResetView(){
	if(document.getElementById('header')){
		header = document.getElementById('header');
		header.style.height = "0px";
	}
	if(document.getElementById('footermap')){
		footermap = document.getElementById('footermap');
		if(header.style.height !== null){
			footermap.setAttribute('prev-height',footermap.style.height);
			footermap.style.height = null;
		}
	}
}
function footerNaveMapSwipe(){
	
	var footermap = document.getElementById('footermap');
	var openclosenavboth = document.getElementById('openclosenavboth');
	var openfloatnavboth = document.getElementById('openfloatnavboth');
	var clicked = 0;
	var prevDiff = 0;
	var upDown = '';
	var maxHeigh = (window.innerHeight-57);
	var cacheHeigh = (window.innerHeight-57);
	var standardH = footermap.clientHeight;
	var h = 0;
	function up(num){
		var result = false;
		if(Math.sign(num) == 1){
			result = true;
		}
		return result;
	}
	function down(num){
		var result = false;
		if(Math.sign(num) == -1){
			result = true;
		}
		return result;
	}
	var supportsTouch = false;
	if ('ontouchstart' in window) {
		//iOS & android
		supportsTouch = true;

	} else if(window.navigator.msPointerEnabled) {

		// Windows
		// To test for touch capable hardware 
		if(navigator.msMaxTouchPoints) {
			supportsTouch = true;
		}

	}
	if(supportsTouch){
		footermap.ontouchstart = function(evt) {
			if (evt.preventDefault) {
				  evt.preventDefault();
			}
			upDown = 'clicked';
			
			prevDiff = evt.touches[0].clientY;
			h = this.clientHeight;
			cacheHeigh = h;
			footermap.ontouchmove = function(evt) {
				y = evt.touches[0].clientY;
				diff = (h+(prevDiff-y));
				if((diff>60 && up((prevDiff-y)) == true) || down((prevDiff-y)) == true){
					if(diff <= (standardH+10)){
						this.style.height = (standardH)+"px";
						cacheHeigh = standardH;
					}else if(diff > (maxHeigh+40)){
						this.style.height = (maxHeigh)+"px";
						cacheHeigh = maxHeigh;
					}else{
						this.style.height = diff+"px";
						cacheHeigh = diff;
					}
				}
				if(up((prevDiff-y)) == true){
					upDown = 'up';
				}else if(down((prevDiff-y)) == true){
					upDown = 'down';
				}
			}
			
		}
		
		footermap.ontouchleave = function(evt) {
			if(upDown == 'up' || upDown == 'down'){
				if(this.clientHeight < (standardH+50)){
					this.style.height = standardH+"px";
				}else if((h < 300 && this.clientHeight > 300) || (h > 300 && this.clientHeight < 300)){
					this.style.height = 300+"px";
				}else if(h > 300 && this.clientHeight > maxHeigh){
					this.style.height = maxHeigh+"px";
				}
			}
			upDown = '';
			footermap.ontouchmove = null;
		}
		footermap.ontouchend = function(evt) {
			if(upDown == 'up' || upDown == 'down'){
				if(this.clientHeight < (standardH+50)){
					this.style.height = standardH+"px";
				}else if((h < 300 && this.clientHeight > 300) || (h > 300 && this.clientHeight < 300)){
					this.style.height = 300+"px";
				}else if(h > 300 && this.clientHeight > maxHeigh){
					this.style.height = maxHeigh+"px";
				}
			}else if(upDown == 'clicked'){
				if(evt.target.classList.contains("foot-nav-map")){
					openNaveFoter(evt.target);
				}else if(evt.target.parentNode.classList.contains("foot-nav-map")){
					openNaveFoter(evt.target.parentNode);
				}
				if(evt.target.id == 'openfloatnavboth'){
					if(this.style.maxWidth != 100+"%"){
						this.style.maxWidth = 100+"%";
						openfloatnavboth.classList.remove("fa-arrow-right");
						openfloatnavboth.classList.add("fa-arrow-left");
					}else{
						this.style.maxWidth = null;
						openfloatnavboth.classList.remove("fa-arrow-left");
						openfloatnavboth.classList.add("fa-arrow-right");
					}
				}else{
					
					if(this.clientHeight > standardH){
						if(evt.target.id == openclosenavboth.id){
							this.style.height = standardH+"px";
							openclosenavboth.classList.remove("fa-arrow-down");
							openclosenavboth.classList.add("fa-arrow-up");
						}
					}else if(this.clientHeight <= standardH){
						openclosenavboth.classList.remove("fa-arrow-up");
						openclosenavboth.classList.add("fa-arrow-down");
						this.style.height = 300+"px";
					}
				}
			}
			upDown = '';
			footermap.ontouchmove = null;

		}
	}else{
		footermap.onmousedown = function(evt) {
			if (evt.preventDefault) {
				  evt.preventDefault();
			}
			
			prevDiff = evt.clientY;
			stopPropaganda(evt);
			h = this.clientHeight;
			upDown = 'clicked';
			cacheHeigh = h;
			footermap.onmousemove = function(evt) {
				y = evt.clientY;
				diff = (h+(prevDiff-y));
				if((diff>20 && up((prevDiff-y)) == true) || down((prevDiff-y)) == true){
					if(diff <= (standardH+10)){
						this.style.height = (standardH)+"px";
						cacheHeigh = standardH;
					}else if(diff > (maxHeigh+40)){
						this.style.height = (maxHeigh)+"px";
						cacheHeigh = maxHeigh;
					}else{
						this.style.height = diff+"px";
						cacheHeigh = diff;
					}
				}
				if(up((prevDiff-y)) == true){
					upDown = 'up';
					openclosenavboth.classList.remove("fa-arrow-up");
					openclosenavboth.classList.add("fa-arrow-down");
				}else if(down((prevDiff-y)) == true){
					upDown = 'down';
					openclosenavboth.classList.remove("fa-arrow-down");
					openclosenavboth.classList.add("fa-arrow-up");
				}
			}
		}
		
		footermap.onmouseleave = function(evt) {
			if(upDown == 'up' || upDown == 'down'){
				if(this.clientHeight < (standardH+50)){
					this.style.height = standardH+"px";
				}else if((h < 300 && this.clientHeight > 300) || (h > 300 && this.clientHeight < 300)){
					this.style.height = 300+"px";
				}else if(h > 300 && this.clientHeight > maxHeigh){
					this.style.height = maxHeigh+"px";
				}
			}
			upDown = '';
			footermap.onmousemove = null;
		}
		footermap.onmouseup = function(evt) {
			if(upDown == 'up' || upDown == 'down'){
				if(this.clientHeight < (standardH+50)){
					this.style.height = standardH+"px";
				}else if((h < 300 && this.clientHeight > 300) || (h > 300 && this.clientHeight < 300)){
					this.style.height = 300+"px";
				}else if(h > 300 && this.clientHeight > maxHeigh){
					this.style.height = maxHeigh+"px";
				}
			}else if(upDown == 'clicked'){
				if(evt.target.classList.contains("foot-nav-map")){
					openNaveFoter(evt.target);
				}else if(evt.target.parentNode.classList.contains("foot-nav-map")){
					openNaveFoter(evt.target.parentNode);
				}
				if(evt.target.id == 'openfloatnavboth'){
					if(this.style.maxWidth != 100+"%"){
						this.style.maxWidth = 100+"%";
						openfloatnavboth.classList.remove("fa-arrow-right");
						openfloatnavboth.classList.add("fa-arrow-left");
					}else{
						this.style.maxWidth = null;
						openfloatnavboth.classList.remove("fa-arrow-left");
						openfloatnavboth.classList.add("fa-arrow-right");
					}
				}else{
					
					if(this.clientHeight > standardH){
						if(evt.target.id == openclosenavboth.id){
							this.style.height = standardH+"px";
							openclosenavboth.classList.remove("fa-arrow-down");
							openclosenavboth.classList.add("fa-arrow-up");
						}
					}else if(this.clientHeight <= standardH){
						openclosenavboth.classList.remove("fa-arrow-up");
						openclosenavboth.classList.add("fa-arrow-down");
						this.style.height = 300+"px";
					}
				}
			}
			upDown = '';
			footermap.onmousemove = null;
			console.log("up null")
		}
	}
	
}
 if(language.hasOwnProperty('langversion') == false){
	loadJSON('lang/'+sessionStorage.lang+'.json',function(response) {
		try{
			language = JSON.parse(response);
		}catch(e){
			console.log("Load language : "+e);
		}
	});
 }
 function isInt(n){
    return Number(n) === n && n % 1 === 0;
}

function isFloat(n){
    return Number(n) === n && n % 1 !== 0;
}

function encryptNumber(numb){
	var result = numb;
	var strChar = "";
	if(typeof numb !== "undefined"){
		numb = Number(numb);
		if(isFloat(numb)){
			strNumb = numb.toString().split(".");
			strChar = hashkit.encode(strNumb[0]);
			if(strNumb.length > 1){
				strChar2 = hashkit.encode(strNumb[1]);
				strChar += "."+strChar2;
			}
		}else{
			strChar = hashkit.encode(numb);
		}
		result = strChar;
	}

	return result;
}
function decryptNumber(str,zeroPad){
	var result = str;
	if(typeof str !== "undefined"){
		strData = str.split(".");
		if(strData.length > 1){
			var strNumb = hashkit.decode(strData[0]);
				strNumb2 = hashkit.decode(strData[1]);
				strNumb += "."+strNumb2;
		}else{
			var strNumb = hashkit.decode(str);
		}
		if(typeof zeroPad === "undefined" || !Number.isInteger(zeroPad) || zeroPad == 0 || zeroPad == null){
			result = strNumb;
		}else{
			result = strNumb.lpad(zeroPad,"0");
		}
			
	}
	return result;
}
function notransEncrtypt(notrans){
	//20191014122743000000009
	var num = noTransaksi("number");
	jmlNumber = num.length;
	var notran1 = parseInt(notrans.substr(0, jmlNumber));
	var notran2 = notrans.substr(jmlNumber, notrans.length);
	notran1 = encryptNumber(notran1);
	if(notran2.trim() != ""){
		notran2 = parseInt(notran2);
		notran1+=  "#"+encryptNumber(notran2);
	}
	return notran1;
}
function notransDecrtypt(notrans){
	//20191014122743000000009
	var nouserid = noTransaksi("nouserid");
	var num = notrans.split("#");
	jmluid = nouserid.length;
	jmlNumber = num.length;
	if(jmlNumber > 0 ){
		var notran1 = num[0];
			notran1 = decryptNumber(notran1);
			if(jmlNumber > 1 ){
				var notran2 = num[1];
				notran2 = decryptNumber(notran2,jmluid);
				notran1+= notran2;
			}
	
		return notran1;
	}
}
function dateEncrtypt(dateSystem){
	//.lpad(2,"0")
	var result = dateSystem;
	if(typeof dateSystem !== "undefined" && dateSystem != ""){
		var d = new Date(dateSystem);
		Yr = d.getFullYear();
		mn = (d.getMonth()+1).lpad(2,"0");
		day = d.getDate().lpad(2,"0");
		dateNmb = Yr+mn+day;
		validate = parseInt(dateNmb);
		if(Number.isInteger(validate)){
			var strNumb = hashkit.encode(validate);
			result = strNumb;
		}
	}
	return result;
}
function dateDecrtypt(dateEncrypt){
	var result = dateEncrypt;
	if(typeof dateEncrypt !== "undefined" && dateEncrypt != ""){
		validate = dateEncrypt.split("-");
		if(validate.length <= 1 ){
			dateNumb = hashkit.decode(dateEncrypt).toString();
			var Yr = dateNumb.slice(0, -4);
			var mn = dateNumb.substr(dateNumb.length-4, 2);
			var day = dateNumb.substr(dateNumb.length-2, 2);
			dateNmb = Yr+"-"+mn+"-"+day;
			result  = dateNmb;
		}
	}
	return result;
}

/***  
Author : Atwal Arifin  
tahun  : 2019 
Check Browser name 
 ***/
 function browser(find){
	var sBrowser,sCodeBrowser, sUsrAg = navigator.userAgent;
	if(sUsrAg.indexOf("Chrome") > -1) {
		sBrowser = "Google Chrome";
		sCodeBrowser = "GC";
	} else if (sUsrAg.indexOf("Safari") > -1) {
		sBrowser = "Apple Safari";
		sCodeBrowser = "AS";
	} else if (sUsrAg.indexOf("Opera") > -1) {
		sBrowser = "Opera";
		sCodeBrowser = "OP";
	} else if (sUsrAg.indexOf("Firefox") > -1) {
		sBrowser = "Mozilla Firefox";
		sCodeBrowser = "MF";
	} else if (sUsrAg.indexOf("MSIE") > -1) {
		sBrowser = "Microsoft Internet Explorer";
		sCodeBrowser = "IE";
	} else {
		sBrowser = "unknown";
		sCodeBrowser = "-";
	}
	result = "";
	switch(find){
		case 'code':
			result = sCodeBrowser;
		break;
		case 'name':
			result = sBrowser;
		break;
	}
	return result;
}
