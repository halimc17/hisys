/***  
Author : Atwal Arifin  
tahun  : 2019 
Untuk menu utama
 ***/
var priv;
if(typeof sessionStorage.menuFormat == 'undefined' || sessionStorage.menuFormat == 'undefined'){
	sessionStorage.menuFormat= "v2";
}
if(typeof sessionStorage.lock == 'undefined'){
	sessionStorage.lock= false;
}
function getStyle(el,styleProp){
    var x =el;

    if(window.getComputedStyle){
        var y = document.defaultView.getComputedStyle(x,null).getPropertyValue(styleProp); 
    }else if (x.currentStyle){
		var y = x.currentStyle[styleProp];
    }                     
    return y;
}
 function removeAllEleMenu(eleArr){
	for(i=0; i<eleArr.length; i++){
		eleArr[i].style.width = "0px";
		eleArr[i].style.left = "-100px";
		eleArr[i].style.opacity = 0;
	}
 }
 function toggleFormatMenuUtama(ver=sessionStorage.menuFormat){
	bd = document.getElementById('bodymaster');
	m = document.getElementById('menuutama');
	devieder = document.getElementById("deviderMenu");
	if(!m.classList.contains(ver)){
		m.classList.add(ver);
	}
	if(ver == 'v2'){
		m.setAttribute("lock","false");
		if(m.classList.contains("v1")){
			m.classList.remove("v1");
		}
		if(devieder.getAttribute("x")){
			bd.parentNode.style.left = devieder.getAttribute("x")+"px";
			m.style.width = devieder.getAttribute("x")+"px";
		}
	}else{
		if(m.classList.contains("v2")){
			m.classList.remove("v2");
		}
		m.setAttribute("lock","false");
		bd.parentNode.style.left = null;
		m.style.width = null;
	}
	bd.setAttribute("version",ver);
	sessionStorage.menuFormat = ver;
 }
 
 function toUnderElement(){
	if(document.getElementById('menuwrapper')){
		var menuwrapper = document.getElementById('menuwrapper');
		document.body.appendChild(menuwrapper);
		navFormatMenu = menuwrapper.getElementsByClassName('format-menu');
		if(navFormatMenu.length > 0){
			btn = navFormatMenu[0].getElementsByTagName("button");
			if(btn.length > 0){
				for(i=0;i<btn.length; i++){
					
					if(isMobile.any()){
						btn[i].ontouchend = function(){
							toggleFormatMenuUtama(this.getAttribute("version"));
						}
					}else{
						btn[i].onclick = function(){
							toggleFormatMenuUtama(this.getAttribute("version"));
						}
					}
					
				}
			}
		}
		
	}
 }
 function openmenuutama(e){
	 var menuwrapper = document.getElementById('menuwrapper');
	 var menuutama = document.getElementById('menuutama');
	 bd = document.getElementById('bodymaster');
	 devieder = document.getElementById("deviderMenu");
	 var showMenu;
	 // console.log(sessionStorage.lock);
	 if(menuutama.style.opacity == "1"){
		showMenu = false;
	 }else if(menuutama.style.opacity == "0"){
		 showMenu = true;
	 }else if(sessionStorage.lock == "true"){
		 showMenu = true;
	 }else if(sessionStorage.lock == "false"){
		 showMenu = false;
	 }
	if(showMenu == false){
		//menuutama.style.right = null;
		menuutama.style.opacity = 0;
		//menuutama.style.width = 1+"px";
		menuutama.style.padding = "0px";
		menuutama.style.zIndex = -9999;
		menuwrapper.style.zIndex = -9999;
		sessionStorage.lock = false;
		/* setTimeout(function(){
			var menuchild_new = document.getElementsByClassName("menuchild_new");
			if(menuchild_new.length > 0){
				removeAllEleMenu(menuchild_new);
			}
		},400); */
		inactiveMenuV2();
		if(typeof e !== 'undefined'){
			logomenuutama = e;
		}else{
			logomenuutama = document.getElementById('logomenuutama');
		}
		logomenuutama.classList.remove('active');
	 }else{
		 //open
		//menuutama.style.right = "0px";
		menuutama.style.opacity = 1;
		menuutama.style.padding = null;
		menuutama.style.zIndex = 1;
		menuwrapper.style.zIndex = 1;
		sessionStorage.lock = true;
		if(typeof e !== 'undefined'){
			logomenuutama = e;
		}else{
			logomenuutama = document.getElementById('logomenuutama');
		}
		logomenuutama.classList.add('active');
		if(devieder.getAttribute("x")){
			activeMenuV2(devieder.getAttribute("x"));
		}else{
			activeMenuV2();
		}
	 }
	
 }
 function loadChildMenu(idmenu){
	if(document.getElementById('childmenuutama_'+idmenu)){
		childmenu = document.getElementById('childmenuutama_'+idmenu);
		childmenu.style.width = "100%";
		childmenu.style.left = "0px";
		childmenu.style.opacity = "1";
	}else{
		let menuutama = document.getElementById('menuutama');
		$.get(menuutama,site_url($.options.filename+'/load_childmenu?parent='+idmenu),function(responseText){
			openChildMenu(idmenu,responseText.response);
		});
		
	}
 }
 

 function rapihkanChildMenu(idChild){
	if(document.getElementById("childmenuutama_"+idChild)){
		var bothchildmenu = document.getElementById("bothchildmenu_"+idChild);
		h = bothchildmenu.offsetHeight;
		columnmenu = document.getElementById("columnmenu_0_"+idChild);
		listmenu = columnmenu.getElementsByClassName("listmenu");
		var index = 0;
		var height = 0;
		var height2 = 0;
		var newColomn = document.createElement("div");
		newColomn.classList.add("col-listmenu"); 
		newColomn.id = "columnmenu_1_"+idChild; 
		bothchildmenu.appendChild(newColomn);
		var newColomn = document.createElement("div");
		newColomn.classList.add("col-listmenu"); 
		newColomn.id = "columnmenu_2_"+idChild; 
		bothchildmenu.appendChild(newColomn);
		var col = new Array();
		var col2 = new Array();
		var colNum = 1;
		for(x=0; x<listmenu.length; x++){
			position = listmenu[x].clientHeight;
			height = (height+position);
			if(height >= h){
				height2 = (height-h);
				if(height2 >= h){
					col2.push(listmenu[x].id);
				}else{
					col.push(listmenu[x].id);
				}
			}
		 }
		
		 chp = bothchildmenu.getElementsByClassName("menulistparentico");
		 if(chp.length > 0){
			 for(x=0; x<chp.length; x++){
				 
				 if(isMobile.any()){
					  chp[x].ontouchstart = function(){
						this.ontouchmove = function(){
							this.ontouchend = null;
						}
						this.ontouchcancel = function(){
							this.ontouchend = null;
						}
						this.ontouchend = function(){
							loadChildMenu(this.getAttribute('ch'));
						}
						
					}
				 }else{
					 chp[x].onclick = function(){
						 loadChildMenu(this.getAttribute("ch"));
					 }
				 }
			 }
		 }
		 ch = bothchildmenu.getElementsByClassName("menulistico");
		 if(ch.length > 0){
			 
			 for(x=0; x<ch.length; x++){
				 if(isMobile.any()){
					  ch[x].ontouchstart = function(){
							this.ontouchmove = function(){
								this.ontouchend = null;
							}
							this.ontouchcancel = function(){
								this.ontouchend = null;
							}
							this.ontouchend = function(){
								 event.stopPropagation();
								  var ev = event;
								 var attr = this.getAttribute("ch").split(",");
								 if(attr.length > 0){
									if(sessionStorage.menuFormat=="v1" || isMobile.any()){
										openmenuutama();
									}
									checkPriviledge(attr[2],function(priv){
										do_load(attr[1],attr[2],ev,attr[0],priv);
									});
									activeParent(attr[2],event);
								 }
							}
						}
				 }else{
					 ch[x].onclick = function(event){
						 event.stopPropagation();
						 var ev = event;
						 var attr = this.getAttribute("ch").split(",");
						 if(attr.length > 0){
							if(sessionStorage.menuFormat=="v1" || isMobile.any()){
								openmenuutama();
							}
							checkPriviledge(attr[2],function(priv){
								do_load(attr[1],attr[2],ev,attr[0],priv);
							});
							 activeParent(attr[2],event);
						 }
					 }
				 }
			 }
		 }
		 createColumn(col,col2,idChild);
	 }
 }
 function activeParent(idmenu,evt){
	 var event = evt || window.event;
	 event.stopPropagation();
	removeAllActived(document.getElementById("menuutama"),"active",idmenu);
	
	function addAllActived(idmenu){
			
		if(document.getElementById("menu_"+idmenu)){
			document.getElementById("menu_"+idmenu).parentNode.classList.add("active");
			document.getElementById("menu_"+idmenu).getAttribute("parentid");
			if(document.getElementById("menu_"+idmenu).getAttribute("parentid") != "0"){
				addAllActived(document.getElementById("menu_"+idmenu).getAttribute("parentid"));
			}
		}
	}
	function removeAllActived(ele,for_,idmenu){
		
		allActive = document.getElementsByClassName(for_);
		if(allActive.length > 0){
			allActive[0].classList.remove(for_);
			removeAllActived(ele,for_,idmenu);
		}else{
			addAllActived(idmenu);
		}
	}
	
 }
 function createColumn(col,col2,idChild){
	columnmenu = document.getElementById("columnmenu_0_"+idChild);
	var	listmenu = columnmenu.getElementsByClassName("listmenu");
	 if(col.length >0){
		column = document.getElementById("columnmenu_1_"+idChild);
		for(var i=0; i<col.length; i++){
			column.appendChild(listmenu[col[i]]);
		}
	 }
	 if(col2.length >0){
		column = document.getElementById("columnmenu_2_"+idChild);
		for(var i=0; i<col2.length; i++){
			column.appendChild(listmenu[col2[i]]);
		}
	 }
 }
 function activeMenuV2(x){
	var e = document.getElementById("deviderMenu");
	var body = document.getElementById("bodymaster");
	var mn = document.getElementById("menuutama");
	var newcontainer = document.getElementsByClassName("newcontainer");
	if(!x){
		var rectDev = e.getBoundingClientRect();
		x = rectDev.right;
	}
	e.setAttribute("x",x);
	body.parentNode.style.left = x+"px";
	if(sessionStorage.menuFormat=="v2"){
		mn.style.width = x+"px";
	}
	
 }
  function inactiveMenuV2(){
	var body = document.getElementById("bodymaster");
	body.parentNode.style.left = null;
 }
 function swiperColFrame(){
	var e = document.getElementById("deviderMenu");
	var body = document.getElementById("bodymaster");
	var mn = document.getElementById("menuutama");
	var newcontainer = document.getElementsByClassName("newcontainer");
	var x;
	function handleMouseDown(evt){
		evt.preventDefault();
		ele = evt.target;
		if(isMobile.any()){
			window.addEventListener('touchmove',handleMouseMove);
		}else{
			window.addEventListener('mousemove',handleMouseMove);
		}
	}
	function handleMouseMove(evt){
		wsc = window.innerWidth;
		if(evt.clientX >= 200 && evt.clientX <= 350){
			body.parentNode.style.left = evt.clientX+"px";
			mn.style.width = evt.clientX+"px";
			x = evt.clientX;
		}
		//if(evt.movementX)
	}
	function handleMouseUp(evt){
		evt.preventDefault();
		if(x && x > 0){
			e.setAttribute("x",x);
		}
		window.removeEventListener("mousemove",handleMouseMove);
	}
	
	if(isMobile.any()){
		e.addEventListener('touchstart',handleMouseDown);
		//window.addEventListener('touchend',handleMouseUp);
		window.addEventListener('touchleave',handleMouseUp);
		window.addEventListener('touchcancel',handleMouseUp);
	}else{
		e.addEventListener('mousedown',handleMouseDown);
		window.addEventListener('mouseup',handleMouseUp);
		window.addEventListener('mouseleave',handleMouseUp);
	}
	lock = 0;
	if(mn.hasAttribute("lock")){
		 lock = mn.getAttribute("lock");
	 }
	 if(mn.style.opacity == "1" && lock != "1"){
		activeMenuV2();
	 }
	
	//e.onmousedown = function(evt) {
		 
			//return that.handleMouseDown(evt);
		/*,
		mousemove: function(evt) {
		  return that.handleMouseMove(evt);
		},
		mouseup: function(evt) {
		  return that.handleMouseUp(evt);
		}, 
		mouseleave: function(evt) {
		  return that.handleMouseUp(evt);
		}
		,
		touchstart: function(evt) {
		var result = that.handleMouseDown(evt, prevEvt);
		  prevEvt = evt
		  return result;
		}

		// Mouse up group
		
		, touchend: function(evt) {
		  return that.handleMouseUp(evt);
		}

		// Mouse move group
		
		, touchmove: function(evt) {
		  return that.handleMouseMove(evt);
		}

		// Mouse leave group
		
		, touchleave: function(evt) {
		  return that.handleMouseUp(evt);
		} 
		, touchcancel: function(evt) {
		  return that.handleMouseUp(evt);
		}*/
	//};
 }
 function openChildMenu(idmenu,listChildMenu){
	var listNumber = 1;
	var div = document.createElement("div");
	div.onscroll = function(event){
		//alert();
	}
	div.id = "childmenuutama_"+idmenu;
	div.classList.add('menuchild_new');
	div.style.display = "block";
	menuchild_new = document.getElementsByClassName("menuchild_new");
	var jml = menuchild_new.length;
	partNumb = (listNumber+jml);
	var spaceTitle = 160;
	var spaceBoth = 0;
	div.style.right = "0px";
	var menu = document.getElementById('menu_'+idmenu);
	menuparent = menu.parentNode;
	text = menu.innerHTML;
	img = menuparent.getElementsByTagName("img");
	icon = document.createElement("img");
	span = document.createElement("span");
	iconSrc = "";
	if(img.length > 0){
		iconSrc = img[0].src;
	}
	icon.src = iconSrc;
	var divMenu = document.createElement("div");
	divMenu.classList.add('child_column');
	divMenu.style.cursor = "pointer";
	
	if(isMobile.any()){
		divMenu.ontouchstart = function(event){
			
			this.ontouchmove = function(){
				this.ontouchend = null;
			}
			this.ontouchcancel = function(){
				this.ontouchend = null;
			}
			this.ontouchend = function(){
				this.parentNode.style.width = "0px";
				this.parentNode.style.left = "-100px";
				this.parentNode.style.opacity = 0;
			}
		}
	}else{
		divMenu.onclick = function(){
			this.parentNode.style.width = "0px";
			this.parentNode.style.left = "-100px";
			this.parentNode.style.opacity = 0;
		}
	}
	span.innerHTML = text+"<img src='"+base_url()+"assets/images/leftarrow.png' style='width:20px;'>";
	divMenu.appendChild(icon);
	divMenu.appendChild(span);
	div.appendChild(divMenu);
	var menuutama = document.getElementById('menuutama');
	div.style.opacity = 1;
	div.style.left = "0px";
	div.style.width = "auto";
	div.style.padding = null;
	div.style.zIndex = 1+jml;
	var both = document.createElement("div");
	both.id = "bothchildmenu_"+idmenu;
	both.classList.add("bothchildmenu");
	both.style.left = spaceTitle+"px";
	both.innerHTML = listChildMenu;
	div.appendChild(both);
	menuutama.appendChild(div);
	rapihkanChildMenu(idmenu);
}
 function openmenu(){
	 var menuopen = document.getElementsByClassName("closemenu");
	 for(i=0; i<menuopen.length; i++){
		ele = menuopen[i];
		ele.classList.remove('closemenu');
		ele.classList.add('openmenu');
	 }
 }
 function ScrollMenu(idmenu){
	
	this.noscroll = function()
	  {
		_dscroll();
	  };
	if (document.getElementById(idmenu)){ 
		_cScroll(idmenu);
	}
	function _cScroll(idmenu){
		var menuWrap = document.getElementById(idmenu); 
		var parent = menuWrap.parentNode;
		parent.addEventListener("mouseover", function(event){
			//this.addEventListener('wheel',function(event){
			//	geserKeatas(event);
			//});
			if (this.addEventListener) {
				
				if(isMobile.any()){
					this.addEventListener("touchstart", function(event){
						getLocationScroll(event);
					}, false);
					this.addEventListener("touchmove", function(event){
						swapKeatas(event);
					}, false);
					this.addEventListener("touchend", function(event){
						getEndScroll(event);
					}, false);
				}else{
					userAgent = browser("code");
					if(userAgent == "MF"){
						// Firefox
						this.addEventListener("DOMMouseScroll", function(event){
							geserKeatas(event);
						}, false);
					}else{
						// IE9, Chrome, Safari, Opera
						this.addEventListener("mousewheel", function(event){
							geserKeatas(event);
						}, false);
					}
				}
			}else this.attachEvent("onmousewheel", function(event){
				geserKeatas(event);
			});
		});
		menuWrap.addEventListener("mouseout", function(event){
			//console.log(event);
		});
	}
	function getEndScroll(e){
		if(document.getElementById("menuwrapper")){
			menuwrapper = document.getElementById("menuwrapper");
			wH = menuwrapper.offsetHeight-50;
			mH = document.getElementById(idmenu).offsetHeight;
			if(mH >= wH){
				menuWrap = document.getElementById(idmenu); 
				getTop = parseInt(getStyle(menuWrap,'top'));
				getBottom = parseInt(getStyle(menuWrap,'bottom'));
				if(getTop > 50){
					menuWrap.style.top = "49px";
					menuWrap.style.bottom = null;
				}else if(getTop < 49 && getBottom > 0){
					menuWrap.style.top = null;
					menuWrap.style.bottom = 0+"px";
				}
			}
		}
	}
	function getLocationScroll(e){
		if(document.getElementById("menuwrapper")){
			menuwrapper = document.getElementById("menuwrapper");
			wH = menuwrapper.offsetHeight-50;
			mH = document.getElementById(idmenu).offsetHeight;
			if(mH >= wH){
				menuWrap = document.getElementById(idmenu); 
				deltaY = e.changedTouches[0];
				dasarAngka = deltaY.clientY;
				getTop = parseInt(menuWrap.offsetTop );
				menuWrap.setAttribute("Y",dasarAngka);
				menuWrap.setAttribute("position",getTop);
			}
		}
	}
	function swapKeatas(e){
		if(document.getElementById("menuwrapper")){
			menuwrapper = document.getElementById("menuwrapper");
			wH = menuwrapper.offsetHeight-50;
			mH = document.getElementById(idmenu).offsetHeight;
			if(mH >= wH){
				
				var e = e || window.event;
				menuWrap = document.getElementById(idmenu); 
				oldPosotion = parseInt(menuWrap.getAttribute("Y")); 
				elePosotion = parseInt(menuWrap.getAttribute("position")); 
				getTop = parseInt(getStyle(menuWrap,'top'));
				deltaY = e.changedTouches[0];
				dasarAngka = deltaY.clientY;
				angkaPerubahan = dasarAngka-oldPosotion;
				menuWrap.style.top = (elePosotion+angkaPerubahan)+"px";
				menuWrap.style.bottom = null;
				
				menuWrap.style.position = 'absolute';
			}
		}
	}
	function geserKeatas(e){
		if(document.getElementById("menuwrapper")){
			menuwrapper = document.getElementById("menuwrapper");
			wH = menuwrapper.offsetHeight-50;
			mH = document.getElementById(idmenu).offsetHeight;
			if(mH >= wH){
				
				var e = e || window.event;
				menuWrap = document.getElementById(idmenu); 
				getTop = parseInt(getStyle(menuWrap,'top'));
				getBottom = parseInt(getStyle(menuWrap,'bottom'));
				if(getTop > 50){
					menuWrap.style.bottom = null;
					menuWrap.style.top = 50+'px';
				}else if(getBottom > 0){
					menuWrap.style.bottom = 0+'px';
					menuWrap.style.top = null;
				}
				deltaY = e.deltaY || e.detail;
				scroll = 120;
				if(deltaY > 0 && getBottom < 0){
					getTop = getTop-Math.abs(scroll);
					menuWrap.style.bottom = null;
					menuWrap.style.top = getTop+'px';
				}else if(deltaY < 0 && getTop < 50){
					getTop = getTop+Math.abs(scroll);
					menuWrap.style.bottom = null;
					menuWrap.style.top = getTop+'px';
				} 
				
				menuWrap.style.position = 'absolute';
			}
		}
	}
	function _dscroll(idmenu){
		//alert('die');
	}
}


function site_url(url){
	var url_ = "";
	var result = site_url_php;
	if(typeof url !== 'undefined'){
		url_ = url;
	}
	result += url_;
	return result;
}
function base_url(url){
	var url_ = "";
	var result = base_url_php;
	if(typeof url !== 'undefined'){
		url_ = url;
	}
	result += url_;
	return result;
}
function site_url_js(url){
	var url_ = "";
	if(typeof url !== 'undefined'){
		url_ = url;
	}
	var protocol = window.location.protocol;//http:
	var hostname = window.location.hostname;//localhost
	var port = window.location.port;//localhost
	var pathName = window.location.pathname;//bakrie/master.php
	var hostandloc = window.location.href;//http://localhost/bakrie/master.php

	leftloc = pathName.split('/');
	if(port != ""){
		result = protocol+"//"+hostname+":"+port+"/";
	}else{
		result = protocol+"//"+hostname+"/";
	}
	len = leftloc.length;
	if(len > 7 ){
		len = 7;
	}
	for(i=0;i<len; i++){
		if (leftloc[i].toLowerCase().indexOf('.php') == -1) {
			if(leftloc[i].trim() !== ""){
				result += leftloc[i]+"/";
			}
		}
	}
	result += url_;
	return result;
}
function setUrlFake(url,segment,codeQ,prev){
	$.setUrlFake(url,segment,codeQ,prev);
}

window.onpopstate=function(evt)
{
	
	if(evt.state == null){
		window.location = '?page=master_front';
	}else{
		pageCi = evt.state.page;
		prev = evt.state.prev;
		tujuan = evt.state.href;
		activeParent(evt.state.template);
		getPageOwlProject(tujuan,pageCi,evt.state.template,prev);
		
	}
	
}

function getPageOwlProject(url,segment,codeQ,prev){
	zz=verify();
	if(zz){       
		var url = new URL(url);
		$.sendAjax(false,url.href,callpage);
		// con.open("GET",url.href,true);
		// con.onreadystatechange= eval(callpage);
		// con.send(null);
	}else{
	   window.location='logout';
	}
	function callpage(result,ev){
		$.setUrlFake(ev.currentTarget.responseURL,segment,codeQ,prev);
		asyncInnerHTML(codeQ,ev.currentTarget.responseText, function(target){
			$.scaningScriptJava(target);
			if(target && $){
				$.panelScroll[target.id] = new GeminiScrollbar({
					element: target,
					autoshow :true,
					onResize :function(){
						this.update();
					}
				}).create();
			}
		});
	};
}

function asyncInnerHTML(codeQ,HTML, callback,closeProgress) {
	var containerId = "containerbody";
	if(document.getElementById(containerId)){
		var containerbody = document.getElementById(containerId);
		containerbody.innerHTML = "";
	}else{
		var bodymaster = document.getElementById("bodymaster");
		var containerbody = document.createElement("div");
		containerbody.id = containerId;
		containerbody.classList.add("masterpanel");
		bodymaster.appendChild(containerbody);
	}
    var temp = document.createElement('div'),
        frag = document.createDocumentFragment();
    temp.innerHTML = HTML;
    (function(){
        if(temp.firstChild){
            frag.appendChild(temp.firstChild);
            setTimeout(arguments.callee, 0);
        } else {
			containerbody.appendChild(frag); // myTarget should be an element node.
            callback(containerbody);
        }
    })();
}
function do_load(dest,menuid,ev,code="",priv){
		if($){
			$.resetProject();
		}else{
			$ = new OwlProject();
		}
 if(dest=='BI'){
		window.open("main_bi.html","OWLBI","status=0,toolbar=0,resizable=1,status=0,location=no,menubar=0,directories=0");       
	}else{ 
	 dest=dest.replace(".php","");
	 dest=dest.replace(".html","");
	 dest=dest.replace(".phtml","");
	 dest=dest.replace(".php3","");
	 tujuan = $.site_url("modules/"+dest);
	getPageOwlProject(tujuan,dest,menuid,priv);
 }
}
function do_loadOwlProject(dest,menuid,ev,code){
	checkPriviledge(menuid,function(priv){
		do_load(dest,menuid,ev,code,priv);
	});
	//do_load(dest,menuid,ev,code);
}

function open_notif(dest,supp){
	dest=dest.replace(".php","");
	dest=dest.replace(".html","");
	dest=dest.replace(".phtml","");
	dest=dest.replace(".php3","");
	window.location=dest+'.php?xxx='+supp;
}

function jump(val,e){
	
	action='';
	key=getKey(e);
	//alert(key);
	if(key<48 || key>57 || !key){try{step=document.getElementById('jumpList');for(x=0;x<step.length;x++){if(step.options[x].getAttribute("value")==val){action=step.options[x].getAttribute("action");}}}catch(err){}if(action!=''){do_load(action);}else{}}
}

function tolltipJump(ev){
	   width=350;
	   height=400;
	   title='Jump Menu :'
       if (document.getElementById('dynamic1')) {
		c = document.createElement('div');   
		c.style.width = width+'px';
	   }
	   else {
	   	c = document.createElement('div');
	   	c.setAttribute('id', 'dynamic1');
	   	c.setAttribute('class', 'drag');
	   	c.style.position = 'absolute';
	   	c.style.display = 'none';
		c.style.width = width+'px';
	   	c.style.paddingTop = '3px';
	   	c.style.zIndex = 1000;
	   	document.body.appendChild(c);
	   }
        cont="<b style='color:#FFFFFF;'>"+title+"</b><img src=assets/images/closebig.gif align=right onclick=closeDialog() title='Close detail' class=closebtn onmouseover=\"this.src='assets/images/closebigon.gif';\" onmouseout=\"this.src='assets/images/closebig.gif';\"><br><br>";
	    cont+="<div style='background-color:#FFFFFF;border:#777777 solid 2px;height:"+height+"px;overflow:auto'>";
	    cont+='<table class=sortable style="width:100%" cellpadding=0 cellspacing=1 border=0>';
	    cont+='<thead><tr class=rowheader><td>Code</td><td>Name</td></tr></thead><tbody>'
		step=document.getElementById('jumpList');
		for(x=0;x<step.length;x++){
			cont+='<tr class=rowcontent style="cursor:pointer;" onclick=jump('+step.options[x].getAttribute("id")+',event)><td align=center>'+step.options[x].getAttribute("id")+'</td><td>'+step.options[x].text+"</td></tr>";
		}
		cont+='</tbody></table>';
	    cont+="</div>";
		document.getElementById('dynamic1').innerHTML=cont;
			pos = new Array();
            pos = getMouseP(ev);
            document.getElementById('dynamic1').style.top = pos[1] + 'px';
            document.getElementById('dynamic1').style.left =(pos[0]-width)+'px';
			document.getElementById('dynamic1').style.display='';
}

function openUserMenu(){
	menuutama = document.getElementById("menuutama");
	listMenuParent = menuutama.getElementsByClassName("menubox");
	if(listMenuParent.length > 0){
		for(i=0; i<listMenuParent.length; i++){
			if(isMobile.any()){
				listMenuParent[i].ontouchstart = function(){
					this.ontouchmove = function(){
						this.ontouchend = null;
					}
					this.ontouchcancel = function(){
						this.ontouchend = null;
					}
					this.ontouchend = function(){
						loadChildMenu(this.getAttribute('ch'));
					}
				}
			}else{
				listMenuParent[i].onclick = function(){
					loadChildMenu(this.getAttribute('ch'));
				}
			}
		}
	}
	logoMenuId = document.getElementById("logomenuutama");
	
	if(isMobile.any()){
		logoMenuId.ontouchend = function(){
			openmenuutama(this);
		}
	}else{
		logoMenuId.onclick = function(){
			openmenuutama(this);
		}
	}
	btnMenu = document.getElementsByClassName("btn-menu-bar");
	function removeAllActived(ele,for_){
		allActive = document.getElementsByClassName("list-menu-active");
		if(allActive.length > 0){
			allActive[0].classList.remove("list-menu-active");
			removeAllActived(ele,for_);
		}else{
			if(typeof ele !== 'undefined' && typeof for_ !== 'undefined'){
				ele.classList.add("list-menu-active");
				document.getElementById(for_).classList.add("list-menu-active");
				// if($.notification.status){
				// 	$.notification.create("TEST Judul",$.notification.options);
				// }
				window.addEventListener("click",function(evt){
					evt.stopPropagation();
					removeAllActived();
				});
			}
		}
	}
	for(i=0; i<btnMenu.length; i++){
		btnMenu[i].addEventListener("click",function(evt){
			evt.stopPropagation();
			if(document.getElementById(evt.target.getAttribute("for"))){
				removeAllActived(evt.target,evt.target.getAttribute("for"));
			}
			
		});
	}
	
}

function checkPriviledge(menuId,callback){
   let bd = document.getElementById('bodymaster');
   $.get(bd,site_url($.options.filename+'/checkPriviledge?id='+menuId),function(responseText){
		try{
			priv = responseText.response;
			priv = priv[0];
			eval(callback(priv));
		}catch(e){
			//location.reload();
		}
	});
    
}
