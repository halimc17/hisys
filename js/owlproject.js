var optionsDefaults = {
	filename : 'owlprojectindex.php',
	master : '#bodymaster',
	slave : 'logout.php',
	getpage : 'getpage',
	type : 'form',
	javascript : {
		language : "javascript1.2",
		src : []
	},
	buatbaru : {
		name : 'buatbaru',
		title : 'New',
		slave : 'form',
		image : 'images/navigasi/addbig.png',
		text : 'New',
		event : {
			click : 'btnNew'
		},
		show : true,
		isEnable : true
	},
	listdata : {
		name : 'listdata',
		title : 'List Data',
		image : 'images/navigasi/folderfresh.png',
		text : 'List Data',
		event : {
			click : 'refreshProject'
		},
		show : true,
		isEnable : true
	},
	excel : {
			name : 'excel',
			title : 'excel',
			image : 'images/navigasi/excel.png',
			event : {
				click : 'printTable'
			},
			show : true,
			isEnable : true
	}
	,pdf : {
			name : 'pdf',
			title : 'pdf',
			slave : 'pdf', // Umar
			image : 'images/navigasi/pdf.png',
			event : {
				click : 'btnPdf'
			},
			show : true,
			isEnable : true
	}
	,csv : {
			name : 'csv',
			title : 'Csv',
			image : 'images/navigasi/csv.png',
			event : {
				click : 'printTable'
			},
			show : true,
			isEnable : true
	}
	,fixHeader : {
			name : 'fixheader',
			title : 'Fix Header',
			image : 'images/navigasi/fix-header.png',
			event : {
				click : 'openFixHeader'
			},
			show : true,
			isEnable : true
	}
	,filter : {
			name : 'filter',
			title : 'Filter',
			slave : 'filter',
			image : 'images/navigasi/filter.png',
			event : {
				click : 'openFilter'
			},
			text : 'Filter',
			width : 30,
			bottom : 0,
			right : 0,
			zIndex : 1,
			show : true,
			autoshow : false,
			isEnable : true
	}
	,search : {
		name : 'search',
		show : true,
		isEnable : true
	}
	,actions : {}
	,breadcrumb : {
		name : 'breadcrumb',
		title : ''
	}
}
var actDefaults = {
		'cetak':'fa fa-file-pdf-o',
		'survei':'fa fa-folder',
		'verifikasi':'fa fa-check',
		'approval':'fa fa-check',
		'new':'fa fa-file-o',
		'edit':'fa fa-pencil',
		'delete':'fa fa-times',
		'view':'fa fa-search',
		'detail':'fa fa-list-alt',
		'generate':'fa fa-superpowers',
		'clone':'fa fa-files-o',
		'process':'fa fa-cogs',
		'active':'fa fa-check-circle-o',
		'nonactive':'fa fa-times-circle-o',
		'fixed-column':'fa fa-table',
		'privilege':'fa fa-key'
}
var OwlProject = function(options){
	this.init(options);
}
OwlProject.prototype.Utils = function(){
	var that = this;
	return {
		extend: function(target, source){
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
		,error_catch: function(o){
			var alert = "";
			switch (x){
				case 203:
					alert = 'Dibutuhkan Authority';
				break;
				case 400:
					alert = 'Error Server';
				break;
				case 403:
					alert = 'Anda dilarang masuk';
				break;
				case 404:
					alert = 'File tidak ditemukan';
				break;
				case 405:
					alert = 'Method tidak diijinkan';
				break;
				case 407:
					alert = 'Proxy Error';
				break;
				case 408:
					alert = 'Permintaan terlalu lama';
				break;
				case 409:
					alert = 'Query Conflict';
				break;
				case 414:
					alert = 'ULI terlalu panjang';
				break;
				case 412:
					alert = 'Variable terlalu banyak';
				break;
				case 415:
					alert = 'Unsupported Media Type';
				break;
				case 500:
					alert = 'Server busy, try submit later';
				break;
				case 502:
					alert = 'Bad gateway';
				break;
				case 505:
					alert = 'Browser anda terlalu tua';	    
				break;
			}
			return alert;
			
		}
		,site_url: function(url){
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
		,getURL: function(pageCi){
			var tujuan;
			filemaster = optionsDefaults.filename;
			if(typeof pageCi.template !== 'undefined'){
				if(pageCi.template =='owlproject'){
					sendTo = this.site_url(filemaster+'?selfproject='+pageCi.page);
				 }else{
					 sendTo = this.site_url(pageCi.page+'.php');
				 }
				tujuan =  new URL(sendTo)
			}
			 return tujuan;
		}
		,isObject: function(o){
			return Object.prototype.toString.call(o) === '[object Object]';
		}
		,isElement: function(o){
			return (
			  o instanceof HTMLElement || //DOM2
			  (o && typeof o === 'object' && o !== null && o.nodeType === 1 && typeof o.nodeName === 'string')
			);
		}
		,isNodeList: function(o){
			return (
			  o instanceof NodeList ||  o instanceof HTMLCollection || //DOM2
			  (o && typeof o === 'object' && o !== null && o.nodeType === 1 && typeof o.nodeName === 'string')
			);
		}
		,PageY : function(a,tobody){
			var b=0;a= this.getElement(a);
				while(a){
					if(this.Def(a.offsetTop)){
						if(a == tobody ){
							break;
						}
						b+=a.offsetTop;
					}
					a=this.Def(a.offsetParent)?a.offsetParent:null
				}
				return b
		}
		,getParentByTagName : function(a,TAGNAME){
			var b;a= this.getElement(a);
				while(a){
					if(this.Def(a)){
						if(a.tagName == TAGNAME){
							b = a;
							break;
						}
					}
					a=this.Def(a.parentNode)?a.parentNode:null
				}
				return b
		}
		,Def : function(){
			for(var b=0,a=arguments.length;b<a;++b){
				if(typeof(arguments[b])==="undefined"){
					return false
				}
			}
			return true
		}
		,getBodyPanel: function(a) {
			var b=null;
			var a=this.getElement(a);
			while(a){
				if(a.classList.contains('panel') == true ){
					b = a;
					break;
				}
			}
			return b
		}
		,getElementById: function(a) {
			if(typeof(a)=="string"){
				if(document.getElementById){
					a=document.getElementById(a);
				}else{
					if(document.all){
						a=document.all[a];
					}else{
						a=null;
					}
				}
			}
			return a;
		}

		,getElement: function(elementOrSelector) {
			var element;
			if (!this.isElement(elementOrSelector)) {
			  if (typeof elementOrSelector === 'string' || elementOrSelector instanceof String) {
				element = document.querySelector(elementOrSelector)
				if (!element) {
				  throw new Error('Provided selector did not find any elements. Selector: ' + elementOrSelector)
				  return null
				}
			  } else {
				throw new Error('Provided selector is not an HTML object nor String')
				return null
			  }
			} else {
			  element = elementOrSelector
			}
			return element
		}
		,getXMLHttpRequest: function(){
			var xhRequest = null;
			try { 
				xhRequest = new XMLHttpRequest(); } 
			catch(e){
				try { 
					xhRequest =  new ActiveXObject("Msxml2.XMLHTTP");
				}catch (e) {
					try { 
						xhRequest =   new ActiveXObject("Microsoft.XMLHTTP");
					}catch (e) {
						alert("XMLHttpRequest Tidak didukung oleh browser");
					}
				}
			}

		   return xhRequest;
		}
		,addAttribute: function(element,Arrattribute){
			for (var i in Arrattribute){
				element.setAttribute(i,Arrattribute[i]);
				if(i == 'name'){
					if(Arrattribute[i] == 'fixheader'){
						if (!that.hasOwnProperty(Arrattribute[i])){
							that[Arrattribute[i]] = {}
						}
						that[Arrattribute[i]]['target'] = element;
						
					}
				}
			}
			return element;
		}
		,addEvent: function(element,Arrevent){
			var func;
			var arg;
			for (var i in Arrevent){
				func = Arrevent[i];
				arg = undefined;
				
				if(Arrevent[i].hasOwnProperty('function')){
					func = Arrevent[i]['function'];
				}
				if(Arrevent[i].hasOwnProperty('arg')){
					if(Arrevent[i]['arg'].length > 0){
						arg = Arrevent[i]['arg'];
					}
				}
				element.addEventListener(i, function(event){
					if(typeof that[func] === "function"){
						if(typeof arg !== "undefined" && arg.length > 0 ){
							that[func].apply(that,arg);
						}else{
							that[func](event);
						}
					}else if(typeof that[func] === "undefined"){
						alert("["+func+"] function is undefined!");
					}else{
						alert("["+func+"] is not a function!");
					}
				});
			}
			return element;
		}
		,setUrlFake: function(url,segment,codeQ,prev){
			var Url = new URL(url);
			dsearch = Url.search;
			url = this.site_url("master.php?page="+segment);
			if(window.history.state == null){
				window.history.pushState({page:segment,search:dsearch,template:codeQ,prev:prev},"", url);
			}else{
				if(window.history.state.page !== segment){
					window.history.pushState({page:segment,search:dsearch,template:codeQ,prev:prev},"",url);
				}
			}
		}
		,reSetUrlFake: function(url,segment,codeQ,prev){
			var Url = new URL(url);
			dsearch = Url.search;
			url = this.site_url("master.php?page="+segment);
			window.history.replaceState({page:segment,search:dsearch,template:codeQ,prev:prev},"",url);
		}
		,appendChild: function(element,Arrchildnode){
			for (var i in Arrchildnode){
				newEle = this.createElement(Arrchildnode[i]);
				element.appendChild(newEle);
			}
			return element;
		}
		,createElement: function(template){
			var element;
				for (var k in template){
					if (template.hasOwnProperty(k)) {
						element = document.createElement(k);
						data = template[k];
						if (data.hasOwnProperty('att')){
							attribute = data['att'];
							element = this.addAttribute(element,attribute);
							
						}
						if (data.hasOwnProperty('e')){
							evnt = data['e'];
							element = this.addEvent(element,evnt);	
						}
						if (data.hasOwnProperty('i')){
							inner = data['i'];
							element.innerHTML = inner;
						}
						if (data.hasOwnProperty('c')){
							childnode = data['c'];
							element = this.appendChild(element,childnode);
						}
					}
				}
				
			return element;
		}
		,createButton : function(data,type,parentObj){
			var newObjct = parentObj || {};
			var eleDevider = {'div':{'att': {'class':'devidernavheader'}}};
			
			var attribute,child = {};
				switch(type){
					case 'bignav':
						if(data.length > 0){
							if(newObjct.hasOwnProperty('status')){
								if(newObjct.status){
									newObjct.obj.push(eleDevider);
								}
							}else{
								newObjct.obj = new Array();
								newObjct.status = false;
							}
							
							for(var n=0; n<data.length; n++){
								attribute = {};
								obj = {};
								if(data[n].hasOwnProperty('show') &&  data[n].show){
									attribute.att = {'class':'bignavheader','title':data[n].title,'name':data[n].name};
									//that.console(attribute);
									if(data[n].hasOwnProperty('isEnable') && data[n].isEnable){
										if (data[n].hasOwnProperty('event')){
											attribute.e = {};
											for (var x in data[n].event){
												attribute.e[x] = data[n].event[x];
											}
										}
									}
									obj.div = {};
									obj.div = attribute;
									obj.div.c = new Array();
									
									child = {};
									child.img = {};
									child.img.att = {'class':'naviconBig','src':data[n].image};
									obj.div.c.push(child);
									
									child = {};
									child.br = {};
									obj.div.c.push(child);
									
									child = {};
									child.font = {};
									if(data[n].hasOwnProperty('text')){
										child.font['i'] = data[n].text;
									}
									obj.div.c.push(child);
									newObjct.obj.push(obj);
									newObjct.status = true;
								}
							}
						}
					break;
					case'smallnav':
						if(data.length > 0){
							if(newObjct.hasOwnProperty('status')){
								if(newObjct.status){
									newObjct.obj.push(eleDevider);
								}
							}else{
								newObjct.obj = new Array();
								newObjct.status = false;
							}
							var objGroup = new Array();
							var pembagi = Math.ceil(data.length/2);  
							
							for(var n=0; n<data.length; n++){
								attribute = {};
								obj = {};
								if(data[n].hasOwnProperty('show') &&  data[n].show){
									attribute.att = {'class':'smallnavheader active','title':data[n].title,'name':data[n].name};
									
									if(data[n].hasOwnProperty('isEnable') && data[n].isEnable){
										if (data[n].hasOwnProperty('event')){
											attribute.e = {};
											for (var x in data[n].event){
												attribute.e[x] = data[n].event[x];
											}
										}
									}
									
									obj.div = {};
									obj.div = attribute;
									obj.div.c = new Array();
									
									child = {};
									child.img = {};
									child.img.att = {'src':data[n].image};
									obj.div.c.push(child);
									
									if(data[n].hasOwnProperty('text')){
										child = {};
										child.font = {};
										child.font['i'] = data[n].text;
										obj.div.c.push(child);
									}
									objGroup.push(obj);
									if(pembagi == (n+1)){
										child = {};
										child.br = {};
										objGroup.push(child);
									}
								}
							}
							var inLine = {'div':{'att': {'class':'inline-header'},'c':objGroup}};
							newObjct.obj.push(inLine);
						}
					break;
				}

			return newObjct;
		}
		,getMaster : function(option){
			if(typeof option != 'undefined' && option.hasOwnProperty('master')){
				return this.getElement(option.master);
			}else{
				return document.getElementById('bodymaster');
			}
		}
		,createFramework : function(option){
			if(typeof option != 'undefined' && option.hasOwnProperty('master')){
				var parentElement = this.getMaster(option);
				//navigasi
				var btnNav = [];
				var dataBtn = [option.buatbaru,option.listdata];
				btnNavBig = this.createButton(dataBtn,'bignav');
				dataBtnSmall = [option.excel,option.pdf,option.csv,option.fixHeader,option.filter];
				AllBtnNav = this.createButton(dataBtnSmall,'smallnav',btnNavBig);
				btnNav = AllBtnNav.obj;
				/* that.console(btnNav); */

				var div_c = new Array();
				var div_p = new Array();
				var chldBrdCrm = new Array();
				var chldPrgrs = new Array();
				var brdCrm = option.breadcrumb;
				if(brdCrm.title == ""){
					brdCrm.title = "&nbsp;";
				}
				chldBrdCrm.push({'span':{'i': brdCrm.title}});
				Search = option.search;
				var seachEle = [];
				if(Search.show){
					seachEle.push({'input':{'att': {'name':'mainsearch','class':'myinputtext','type':'search','placeholder':'Search..'}}});
				}
				chldBrdCrm.push({'div':{'att': {'class':'pull-right u-margin-r-10'},'c':seachEle}});
				
				div_c[0] = {'div':{'att': {'class':'navcontrol-hide'},'e': {'click':'togglehide'}}};
				div_c[1] = {'div':{'att': {'class':'headerform noselect'},'c':btnNav}};
				div_c[2] = {'div':{'att': {'class':'breadcrumb'},'c':chldBrdCrm}};

				// var div_04 = {'div':{'att': {'id':'pagination'}}};
				var div_03 = {'div':{'att': {'id':'progressBar','class':'progressBar nostripes','style':'display:none'}}};
				var div_02 = {'div':{'att': {'id':'navheaderfunction','class':'navheader'},'c':div_c}};
				var div_01 = {'div':{'att': {'id':'containerbody','class':'masterpanel'}}};
				if(this.getElementById('containerbody')){
					var panel 		= this.getElementById('containerbody');
					panel.innerHTML = "";
				}else{
					var panel 		= this.createElement(div_01);
				}
				var header 		= this.createElement(div_02);
				var prgressBar 	= this.createElement(div_03);
				// var pgntn 		= this.createElement(div_04);

				header.appendChild(prgressBar);
				// header.appendChild(pgntn, header.firstChild);
				panel.insertBefore(header, panel.firstChild);
				parentElement.appendChild(panel);
				if(option.javascript.src.length > 0){
					for(i=0; i<option.javascript.src.length; i++){
						if(option.javascript.src[i] != ""){
							parentElement.appendChild(this.createElement({'script':{'att': {'language':option.javascript.language,'src':option.javascript.src[i]}}}));
						}
					}
				}
				return {
					'master':parentElement,
					'header':header,
					'panel':panel
				};
			}else{
				var parentElement = this.getMaster();
				//parentElement.innerHTML = "";
				var div_01 = {'div':{'att': {'id':'containerbody','class':'masterpanel'}}};
				if(this.getElementById('containerbody')){
					var panel 		= this.getElementById('containerbody');
					panel.innerHTML = "";
				}else{
					var panel 		= this.createElement(div_01);
				}
				return {
					'master':parentElement,
					'header':'',
					'panel':panel
				};
			}
		}
	}
	
}
/*** Start Definisi ***/
OwlProject.prototype.init = function(options) {
	var that = this;
	var Utils = this.Utils();
	this.fxheadModul={};

	this.getElementById = Utils.getElementById;
	this.nav = undefined;

	this.buatbaru = {
		target:undefined,
		close:function(){}
	};

	this.filter = {
		target:undefined,
		close:function(){}
	};


	this.pdfFilter = {
		target:undefined,
		close:function(){}
	};

	this.utils = this.Utils();;
	
	this.refresh = undefined;
	
	this.onlineStatus = undefined;

	this.panel = undefined;
	
	this.panelScroll = {};
	
	this.header = undefined;

	this.search = undefined;
	
	this.frameWork = undefined;
	
	this.search = undefined;
	
	this.dataAction = new Array();
	
	this.site_url = Utils.site_url;
	this.setUrlFake = Utils.setUrlFake;
	
	this.master = Utils.getMaster();
	
	this.options = {};
	this.menuAct = new Array();
	
	this.nav = undefined;
	
	this.resetProject(options);//reset if ready
	this.setupHandlers();
}
/*** Regulasi ***/



/*** scanning ***/
OwlProject.prototype.openFixHeader = function(){
	//this.console(this);
	var parentEle = this.Utils().getElementById('containerbody'); 
	if(typeof elementParent !== 'undefined' && this.Utils().isElement(elementParent)){
		parentEle = elementParent;
	}
	idParent = parentEle.id;
	if(this.fxheadModul.hasOwnProperty('tablefix'+idParent)){
		this.fxheadModul['tablefix'+idParent].paint();
	}
	//var dataTable = parentEle.getElementsByClassName('data-table');
	
}

OwlProject.prototype.createPaintTable = function(elementParent){
	var that = this;
	var Utils = this.Utils();
	var dataTable = elementParent.getElementsByClassName('data-table');
/* 	var table 	  = dataTable[0]; */
	function cleanHilight(_colgroup){
		if(_colgroup){
			if(_colgroup.getElementsByClassName("hover").length > 0){
				for (var hvCol = 0; hvCol< _colgroup.getElementsByClassName("hover").length; ++hvCol) {
					_colgroup.getElementsByClassName("hover")[hvCol].classList.remove("hover");
				}
			}
		}
	}
	function hilight(el,_colgroup){
			if(_colgroup){
				idx = parseInt(el.getAttribute("idx"));
				if(_colgroup.getElementsByClassName("hover").length > 0){
					for (var hvCol = 0; hvCol< _colgroup.getElementsByClassName("hover").length; ++hvCol) {
						if(_colgroup.getElementsByClassName("hover")[hvCol].cellIndex != idx){
							_colgroup.getElementsByClassName("hover")[hvCol].classList.remove("hover");
						}
					}
				}
				_colgroup.childNodes[idx].classList.add("hover");
			}
		
	}
	function paint(parentEle){
		for (var i_0 = 0; i_0< dataTable.length; ++i_0) {
			if(Utils.isElement(dataTable[i_0])){
				dataTable[i_0].onmouseout = function(event){
					_colgroup = Utils.getElementById(this.id+"_colgroup");
					cleanHilight(_colgroup);
				}
				dataTable[i_0].onmouseover = function(event){
					if(typeof event.target.cellIndex == 'number' && event.target.tagName == "TD" && typeof Utils.getParentByTagName(event.target,'TBODY') != 'undefined'){
						_colgroup = Utils.getElementById(this.id+"_colgroup");
						hilight(event.target,_colgroup);
					}
				}
				if(dataTable[i_0].tHead){
					dataTable[i_0].tHead.style.top = Utils.PageY(dataTable[i_0],dataTable[i_0].parentNode)+"px";
					dataTable[i_0].tHead.classList.add("table-sticky");
					dataTable[i_0].tHead.style.position = "sticky";
					dataTable[i_0].tHead.style.zIndex = 2;
					dataTable[i_0].tHead.style.boxShadow = "1px 1px 3px #000000c7";
					
					//table.tHead.setAttribute("data-action","true");
				}
				if(dataTable[i_0].tFoot){
					dataTable[i_0].tFoot.style.position = "sticky";
					dataTable[i_0].tFoot.classList.add("table-sticky");
					dataTable[i_0].tFoot.style.bottom = 0;
					dataTable[i_0].tFoot.style.zIndex = 2;
					dataTable[i_0].tFoot.style.boxShadow = "1px 1px 3px #000000c7";
				}
				//rows = dataTable[i_0].rows;
				var mH = [];
				for (var y = 0; y < dataTable[i_0].rows.length; ++y) {
					 var row = dataTable[i_0].rows[y];
					 actions = new Array();
					for(var x=0;x<dataTable[i_0].rows[y].cells.length;x++){
						idx = 0;
						var cell = dataTable[i_0].rows[y].cells[x], xx = x, tx, ty;
						for(; mH[y] && mH[y][xx]; ++xx);
						for(tx = xx; tx < xx + cell.colSpan; ++tx){
							for(ty = y; ty < y + cell.rowSpan; ++ty){
								if( !mH[ty] ) mH[ty] = [];                    // fill missing rows
								mH[ty][tx] = true;
							}
						}
						//xx: the horizontal offset of the cell
						//y: the vertical offset of the cell
						cell.setAttribute("idx",xx);
						if(cell.tagName == "TH" && cell.classList.contains("tools") == false){
							//fixLeft
							// cell.onclick = function(){
								// leftFix(this);
							// }
							act = {
								target : cell
								,event : [{
									title : 'Fixed Column '+cell.innerText
									,name : 'fixed-column'
									,execute : leftFix
									,arguments : [cell]
								}]
								
							}
							actions.push(act);
						}
					}
					if(row.parentNode.tagName == "THEAD"){
						options = {
							parentId : dataTable[i_0].id
							,title : 'Tools'
							,ele : actions
						}
						that.menuTools(row,options);
					}
					
				}
				if(mH.length > 0){
					colgroup = document.createElement('colgroup');
					colgroup.id = dataTable[i_0].id+"_colgroup";
					for (var y = 0; y < mH[0].length; ++y) {
						col = document.createElement('col');
						colgroup.appendChild(col);
					}
					dataTable[i_0].insertBefore(colgroup,dataTable[i_0].tHead);
				}
			}
		}
	}
	function clean(idx){
		for (var i_0 = 0; i_0< dataTable.length; ++i_0) {
			if(Utils.isElement(dataTable[i_0])){
				var table = dataTable[i_0];
				if(document.getElementById(table.id+"_style")){
					document.getElementById(table.id+"_style").innerHTML = "";
				}
				
			}
		}
	}
	function leftFix_(){
		console.log(arguments);
	}
	function leftFix(el){
		//this.event.stopPropagation();
		table = Utils.getParentByTagName(el,'TABLE');
		idx = el.getAttribute("idx");
		padLeft = el.style.paddingLeft;
		padRight =el.style.paddingRight;
		border =1;
		if(Utils.isElement(table)){
			CSS = "";
			tan = "background: linear-gradient(180deg, rgba(195,214,240,1) 0%, rgba(228,236,248,1) 2%, rgba(173,199,235,1) 15%, rgba(195,218,249,1) 52%, rgba(199,222,251,1) 82%,rgba(228,236,248,1) 98%,rgba(0,0,0,1) 100%);color: black;text-shadow: 1px 1px #ffffff;";
			tan2 = "color: black;text-shadow: 1px 1px #ffffff;";
			let LeftOffset = 0;
			for(i=0; i<=idx; i++){
				if(i == idx){
					//tan = 'box-shadow: 2px 0px 2px rgb(0 0 0 / 20%);';
				}
				w = document.getElementById(table.id+"_colgroup").childNodes[i].offsetWidth;
				CSS += '#'+table.id+' thead th[idx="'+i+'"]{position: sticky;left:'+LeftOffset+'px;z-index:2;'+tan+'}';
				CSS += '#'+table.id+' tbody td[idx="'+i+'"]{background-color: rgba(255,255,255,1);color:black;'+tan2+'}';
				CSS += '#'+table.id+' td[idx="'+i+'"]{position: sticky;left:'+LeftOffset+'px;}';
				CSS += '#'+table.id+' tr:hover td[idx="'+i+'"]{background-color: #fdfce8;}';
				LeftOffset = (LeftOffset+w);
			}
			let update = true;
			css = document.createTextNode(CSS);
			if(document.getElementById(table.id+"_style")){
				style = document.getElementById(table.id+"_style");
				getIdx = style.getAttribute("idx");
				if(getIdx == idx){
					update = false;
					style.setAttribute("idx","");
				}else{
					style.setAttribute("idx",idx);
				}
				style.innerHTML = "";
			}else{
				style = document.createElement('style');
				style.id = table.id+"_style";
				style.type = 'text/css';
				style.setAttribute("idx",idx);
				table.parentNode.appendChild(style);
			}
			if (update){
				if (style.styleSheet){
				  style.styleSheet.cssText = CSS;
				} else {
				  style.appendChild(css);
				}
			}
		}
	}
	paint(elementParent);
	return {
		paint : function(){paint(elementParent);},
		clean : function(){clean();}
	}
	
}

OwlProject.prototype.ScaningTableHeaderfix = function(panel){
	var that = this;
	var elementParent = panel;
	
	if(this.Utils().isElement(elementParent)){
		var dataTable = elementParent.getElementsByClassName('data-table');
		var ParentIdName = [];
		if(dataTable.length > 0){
			//that.console("Scan Header Fix..");
			for (var i = 0; i < dataTable.length; ++i) {
				dataTable[i].id = "tablefix-"+i+"-"+elementParent.id;
				dataTable[i].classList.add('tablefix');
				dataTable[i].setAttribute("col-position","true");
			}
			if(ParentIdName.indexOf(elementParent.id) == -1){
				ParentIdName.push(elementParent.id);
			}
			for (var i = 0; i < ParentIdName.length; ++i) {
				this.fxheadModul['tablefix'+ParentIdName[i]] = this.createPaintTable(elementParent);
			}
			
		}
	}
	
	window.addEventListener("resize", function() {
			that.repaintHeadfix();	
		}
	);
	/* function createPain(elementParent){
		var thats = that;
		var dataTable = elementParent.getElementsByClassName('data-table');
		var table 	  = dataTable[0];
		function paint(table,parentEle){
			if(thats.Utils().isElement(table)){
				th = table.tHead.querySelectorAll("th");
				for (var i = 0; i < th.length; ++i) {
					th[i].style.top = PageY(table,parentEle)+"px";
					th[i].onclick = function(){
						idx = this.cellIndex;
						leftFix(table,idx);
					}
				}
			}
		}
		function leftFix(table,idx){
			
			
		}
		function leftFix(table,idx){
			if(thats.Utils().isElement(table)){
				var leftRc = new Array();
				var reduse = 0;
				reactTable =table.getBoundingClientRect();
				var rt = reactTable.left;
				for (var i = 0; i < table.rows.length; ++i) {
					cells = table.rows[i].cells;
					reduse = 0;
					for (var xi = 0; xi < cells.length; ++xi) {
						if(cells[xi].cellIndex <= idx){
							cells[xi].classList.add("fixed");
							if(leftRc.hasOwnProperty("idx_"+cells[xi].cellIndex)){
								cells[xi].style.left = leftRc["idx_"+cells[xi].cellIndex]+"px";
							}else{
								if(cells[xi].cellIndex == 0){
									cells[xi].style.left = "0px";
									leftRc["idx_"+cells[xi].cellIndex] = 0;
								}else{
									cells[xi].style.left = (reduse)+"px";
									leftRc["idx_"+cells[xi].cellIndex] = (reduse);
								}
								threct = cells[xi].offsetWidth;
								reduse = reduse+threct;
							}
							
						}else if(cells[xi].cellIndex > idx && cells[xi].classList.contains('fixed')){
							cells[xi].classList.remove("fixed");
							cells[xi].style.left = null;
						}else{
							break;
						}
					}
				}
			}
		}
		paint(table,elementParent);
		return {
			paint : function(){paint(table,elementParent);},
			clean : function(){}
		}
	} */
	/* var elementParent = panel;
	if(this.Utils().isElement(elementParent)){
		var dataTable = elementParent.getElementsByClassName('data-table');
		if(dataTable.length > 0){
			that.console("Scan Header Fix..");
			var ParentIdName = [];
			for (var i = 0; i < dataTable.length; ++i) {
				if(!dataTable[i].classList.contains('tablefix'+elementParent.id)){
					dataTable[i].classList.add('tablefix'+elementParent.id);
				}
				if(ParentIdName.indexOf(elementParent.id) == -1){
					ParentIdName.push(elementParent.id);
				}
				this.clearingHeadfix('tablefix'+elementParent.id);
			}
			for (var i = 0; i < ParentIdName.length; ++i) {
				this.fxheadModul['tablefix'+ParentIdName[i]] = new KepalaTableNgegantung('tablefix'+ParentIdName[i], ParentIdName[i],0);
			}
		}
	} */
}

OwlProject.prototype.makeDesignInput = function(eLForm){
	var that = this;
	let Attr = ['search'];
	let tagName = ['input','select'];
	let srcAtt = eLForm.querySelectorAll('[search=true]');
	for (let a in srcAtt){
		if(that.Utils().isElement(srcAtt[a])){
			console.log(srcAtt[a]);
			div = document.createElement('div');
			span = document.createElement('span');
			div.classList.add("input-group");
			span.classList.add("input-group-addon");
			span.innerHTML = '<i class="fa fa-search" aria-hidden="true"></i>';
			parntElement = srcAtt[a].parentNode;
			parntElement.insertBefore(div,srcAtt[a]);
			div.appendChild(srcAtt[a]);
			div.appendChild(span);
			span.onclick = function(){
				z.elSearch(srcAtt[a].id,event);
			};
		}
	}
	
}
OwlProject.prototype.scanFormTag = function(panel){
	var that = this;
	if(that.Utils().isElement(panel)){
		var allForm = panel.getElementsByTagName("form");
		that.console("Scan Form");
		for (let k in allForm){
			if(that.Utils().isElement(allForm[k])){
				that.makeDesignInput(allForm[k]);
				allForm[k].onsubmit = function(evt){ 
				console.log('submit');
					evt.preventDefault();
					var ajaxOnSuccess = function(){
						that.console("Success!!");
					}
					var missCallback = function(){
						that.console("Form tidak memiliki attribute callback");
					}
					var target = evt.target || evt.srcElement; 
					if(typeof target.attributes.action == 'undefined'){
						target.attributes.action = that.options.slave;
					}
					// try{
						if(typeof target.attributes.method == 'undefined'){
							that.console("Form tidak memiliki attribute Method");
						}else if(typeof target.attributes.callback == 'undefined' && that.options.type.toLowerCase() != "report"){
							that.console("Form tidak memiliki attribute callback");
						}else{
							if(typeof target.attributes.callback !== 'undefined'){
								try {
									callbackEvt = eval(target.attributes.callback.value);
								} catch (e) {
									callbackEvt	= missCallback;
								}
							}else{
								//console.log(that.panel);
								loader = that.loader(that.panel);
								loader.on();
								callbackEvt = that.refreshProject;
							}
							
							that.sendAjax(this,evt.action,callbackEvt);
							
						}
						return false;
					// }catch(e){
						// that.console("Error Event Submit",evt);
					// }
				}
			}
		}
					
	}
}
OwlProject.prototype.asyncInnerHTML = function(HTML, callback){
	var temp = document.createElement('div'),
		frag = document.createDocumentFragment();
		temp.innerHTML = HTML;
		(function(){
			if(temp.firstChild){
				frag.appendChild(temp.firstChild);
				setTimeout(arguments.callee, 0);
			} else {
				callback(frag);
			}
		})();
}
OwlProject.prototype.scanLoadField = function(p){
	var that = this;
	var dataElem = {};
	function refreshLelem(elem,slave){
		var loader = that.loader(elem);
		loader.on();
		that.getpage(elem,elem.getAttribute("slave"),function(result){
			var el = elem;
			that.asyncInnerHTML(result.response, function(fragment){
				el.appendChild(fragment); // myTarget should be an element node.
				//loader.off();
			});
		});
	}
	if(that.Utils().isElement(p)){
		var allField = p.getElementsByClassName("load-field");
		if(allField.length>0){
			that.console("Scan Load Field..");
			for(i_01x=0; i_01x<allField.length; i_01x++){
				if(allField[i_01x].getAttribute("slave")){
					 refreshLelem(allField[i_01x],allField[i_01x].getAttribute("slave"));
					if(allField[i_01x].getAttribute("name")){
						dataElem[allField[i_01x].getAttribute("name")] = {
							target:allField[i_01x]
							,refresh:function(){
								refreshLelem(allField[i_01x],allField[i_01x].getAttribute("slave"));
							}
						}
						
					}
				}
			}
			that.ScaningTableHeaderfix(p);
			return dataElem;
		}
	}
}
OwlProject.prototype.handleMouseDown = function(evt){
	evt.preventDefault();
}
OwlProject.prototype.handleMouseUp = function(evt){}
OwlProject.prototype.handleMouseMove = function(evt){}

OwlProject.prototype.handleTouchDown = function(evt){
	evt.preventDefault();
}
OwlProject.prototype.handleTouchMove = function(evt){}
OwlProject.prototype.handleTouchUp = function(evt){}

OwlProject.prototype.setupHandlers = function(){
	var that = this, prevEvt = null; // use for touchstart event to detect double tap

	this.eventListeners = {
	  // Mouse down group
		mousedown: function(evt) {
		  return that.handleMouseDown(evt);
		}
	  , touchstart: function(evt) {
		  var result = that.handleTouchDown(evt);
		  prevEvt = evt
		  return result;
		}
		// Mouse up group
	  , mouseup: function(evt) {
		  return that.handleMouseUp(evt);
		}
	  , touchend: function(evt) {
		  return that.handleTouchUp(evt);
		}

		// Mouse move group
	  , mousemove: function(evt) {
		  return that.handleMouseMove(evt);
		}
	  , touchmove: function(evt) {
		  return that.handleTouchMove(evt);
		}

		// Mouse leave group
	  , mouseleave: function(evt) {
		  return that.handleMouseUp(evt);
		}
	  , touchleave: function(evt) {
		  return that.handleTouchUp(evt);
		}
	  , touchcancel: function(evt) {
		  return that.handleTouchUp(evt);
		}
	}
	
}

OwlProject.prototype.scaningScriptJava = function(e,dostnHaveSrc){
	var that = this;
	 var elm = e;
	 var no = 0;
	
	if(typeof dostnHaveSrc == 'undefined'){ 
		let dataScript =  elm.querySelectorAll("script[src]");
		if(dataScript.length > 0){
			Array.from(dataScript).forEach( oldScript => {
				const newScript = document.createElement("script");
				Array.from(oldScript.attributes).forEach( attr => newScript.setAttribute(attr.name, attr.value) );
				newScript.appendChild(document.createTextNode(oldScript.innerHTML));
				oldScript.parentNode.replaceChild(newScript, oldScript);
				newScript.onerror  = function(ev) {
						console.log("Error : Script Source at "+this.src);
						no++;
						if(no == dataScript.length){
							that.scaningScriptJava(e,'runScript');
						}
				}
				newScript.onload = newScript.onreadystatechange = function(ev) {
					if(!this.readyState || this.readyState == "loaded" || this.readyState == "complete") {
						console.log("Scaning Javascript");
						no++;
						if(no == dataScript.length){
							that.scaningScriptJava(e,'runScript');
						}
					}
					
				}
			});
		}else{
			that.scaningScriptJava(e,'runScript');
			
		}
		//console.log(this.eventListeners,window);
	}else{
		console.log("Scaning Javascript");
		let dataScript =  elm.querySelectorAll("script:not([src])");
		Array.from(dataScript).forEach( oldScript => {
			const newScript = document.createElement("script");
			Array.from(oldScript.attributes).forEach( attr => newScript.setAttribute(attr.name, attr.value) );
			newScript.appendChild(document.createTextNode(oldScript.innerHTML));
			oldScript.parentNode.replaceChild(newScript, oldScript);
		}); 
	}
}
OwlProject.prototype.cleanMenuAct = function(){
	var that = this;
	for (const p in that.menuAct) {
		if(typeof that.menuAct[p] !== 'undefined'){
			for (const prop in that.menuAct[p]) {
				if(typeof that.menuAct[p][prop] !== 'undefined'){
					that.menuAct[p][prop].clean();
				}
			}
		}
	}
}
OwlProject.prototype.functionEventlistener = function(argFunct){
	
	// function executeFunctionByName(functionName, context /*, args */) {
		// var args = Array.prototype.slice.call(arguments, 2);
		// var namespaces = functionName.split(".");
		// var func = namespaces.pop();
		// for (var i = 0; i < namespaces.length; i++) {
			// context = context[namespaces[i]];
		// }
		// return context[func].apply(context, args);
	// }
	// this.dataAction.target = ev.owlArguments[1];
	nameFuncts = argFunct.split(";");
	let regex = /\([^)]*\)/i;
	for(x=0; x<nameFuncts.length; x++){
		if(nameFuncts[x] != ''){
			eval(nameFuncts[x]);
			//console.log(nameFuncts[x].match(regex)[0]);
		}
	}
	// eval(arg);
	// delete ev.owlArguments;
}
OwlProject.prototype.menuTools = function(){
	let optionsDefault = {
		parentId : 'owlprojectId'
		,title : 'Tools'
		,ele : []
	}
	let optionsEle = {
		target : null
		,event : []
	}
	let optionsEvent = {
		title : null
		,name : null
		,active : true
		,execute : null
		,arguments : []
	}
	var that = this;
	var Utils = this.Utils();
	//menuToolsHeaderTable
	if(Utils.isElement(arguments[0])){
		el = Utils.getElement(arguments[0]);
		if(Utils.isObject(arguments[1])){
			opt = Utils.extend(Utils.extend({}, optionsDefault), arguments[1]);
			for(i=0;i<opt.ele.length;i++){
				if(Utils.isElement(opt.ele[i].target)){
					if(typeof this.menuAct[opt.parentId] == 'undefined'){
						this.menuAct[opt.parentId] = new Array();
					}
					opt.ele[i] = Utils.extend(Utils.extend({}, optionsEle), opt.ele[i]);
					elem = opt.ele[i].target;
					if(opt.ele[i].event.length > 0){
						for(ii=0;ii<opt.ele[i].event.length;ii++){
							opt.ele[i].event[ii] = Utils.extend(Utils.extend({}, optionsEvent), opt.ele[i].event[ii]);
							if(opt.ele[i].event[ii].name != null){
								elem.setAttribute(opt.ele[i].event[ii].name,opt.ele[i].event[ii].active);
								elem.setAttribute('title-'+opt.ele[i].event[ii].name,opt.ele[i].event[ii].title);
							}
						}
					}
					this.menuAct[opt.parentId]['tools-'+opt.parentId+'-'+i] = new this.menuActionList(elem,that,opt.title,opt.ele[i].event);
				}
			}
		}
	}
}
OwlProject.prototype.menuActionList = function(ct,that,titleMenu="List Actions",addEvent=[]){
	
	var Utils = that.Utils();
	this.clean = function(){_dtor()};
		let listaction = ct.querySelectorAll("[list-action]");
		if(listaction.length > 0){
			for ( var i = 0; i < listaction.length; i++){
				listaction[i].oncontextmenu = function(e) {
					e.preventDefault();
					_dtor();
					 return _ctor(this,e,titleMenu);
				}
			}
		}else{
			ct.oncontextmenu = function(e) {
				e.preventDefault();
				_dtor();
				 return _ctor(this,e,titleMenu);
			}
		}
		 
		 function _dtor(){
			 if(that.getElementById('actionlistwrap')){
				var LastAct = that.getElementById('actionlistwrap');
				LastAct.remove();
			 }
			 if(ct){
				 listactive = ct.querySelectorAll('[list-action="active"]');
				 for (var i = 0, len = listactive.length; i < len; i++ ) {
					if(typeof listactive[i].oncontextmenu === 'undefined'){
						listactive[i].oncontextmenu = null;
					}
					listactive[i].attributes['list-action'].value = '';
				}
			 }
		 }

		function _ctor(ele,event,titleMenu){
			if(ele.attributes['list-action']){
				ele.attributes['list-action'].value = 'active';
			}else{
				ele.setAttribute('list-action','active');
			}
			if(ele.getAttribute('title') && ele.getAttribute('title') !== ""){
				titleMenu = ele.getAttribute('title');
			}
			liArr = new Array();
			liArr.push({'li':{'att': {'class':'title'},'i':titleMenu}});
			ul = Utils.createElement({'ul':{'c':liArr}});
			let readyEl = false;
			for(ii=0; ii<ele.attributes.length; ii++){
				if(Object.keys(that.actionBtn).indexOf(ele.attributes[ii].nodeName.toLowerCase()) !== -1){
					child = new Array();
					if(typeof that.actionBtn[ele.attributes[ii].nodeName.toLowerCase()] !== 'undefined'){
						child.push({'i':{'att': {'class':that.actionBtn[ele.attributes[ii].nodeName.toLowerCase()],'aria-hidden':'true'}}});
					}
					textAct = ele.attributes[ii].nodeName.replace("-"," ");
					if(ele.hasAttribute("title-"+ele.attributes[ii].nodeName)){
						textAct = ele.getAttribute("title-"+ele.attributes[ii].nodeName);
					}
					child.push({'a':{'i':textAct}});
					
					if(addEvent.length > 0){
						newOjc = {};newOjc[ele.attributes[ii].nodeName] = ele.attributes[ii].value;
						lin = {'li':{'att':newOjc,'c':child}};
					}else{
						lin = {'li':{'e':{'click':{'function':'functionEventlistener','arg':[ele.attributes[ii].value,ele]}},'c':child}};
					}
					li = Utils.createElement(lin);
					for (ix = 0, len = addEvent.length; ix < len; ix++ ){
						if(addEvent[ix].name == ele.attributes[ii].nodeName && ele.attributes[ii].value == "true" && typeof addEvent[ix].execute == "function"){
							var newFunct = addEvent[ix].execute;
							var newArguments = addEvent[ix].arguments;
							li.onclick = function(event){
								newFunct.apply(newFunct,newArguments);
							}
						}
					}
					ul.appendChild(li);
					readyEl = true;
				}
			}
			if(readyEl){
				div = Utils.createElement({'div':{'att': {'id':'actionlistwrap','class':'actionlistwrap'}}});
				div.appendChild(ul);
				document.body.insertBefore(div,document.body.childNodes[0]);
			
				let x = 0;
				let y = 0;
				if (typeof event !== 'undefined') {
					x = event.clientX;// Get the horizontal coordinate
					y = event.clientY;
				}else{
					Console.log('Error: Contextmenu');
				}
				let w = window.innerWidth;
				let h = window.innerHeight;
				
				if(isMobile.any()){
					y = div.clientHeight;
					div.style.transform = "translateY(-"+y+"px)";
				}else{
					if(w-x < 210){
						x = x-200;
					}
					if(h-y < 210){
						y = y-div.clientHeight;
					}
					div.style.transform = "translate("+x+"px,"+y+"px)";
				}
				window.onwheel  = function(){
					that.cleanMenuAct();
				};
				
				window.onclick  = function(){
					that.cleanMenuAct();
				};
			}
		}
}
OwlProject.prototype.scaningListAction = function(p){
	console.log('Scaning List Action..');
	var that = this;
	var Utils = this.Utils();
	var actionDefined = Utils.actionDefined;
	
	if(p.querySelectorAll("[data-action]").length > 0){
		if(p.attributes.id){
			var Parid = p.attributes.id.value;
			that.cleanMenuAct();
			for(ib = 0; ib < p.querySelectorAll("[data-action]").length; ib++){
				if(typeof that.menuAct[p.id] == 'undefined'){
					 that.menuAct[p.id] = new Array();
				}
				p.querySelectorAll("[data-action]")[ib].attributes['data-action'].value = 'act-active-'+ib+'-'+p.id;
				that.menuAct[p.id]['act-active-'+ib+'-'+p.id] = new this.menuActionList(p.querySelectorAll("[data-action]")[ib],that);
				
			}
		}
	}
	
}
OwlProject.prototype.scanningElement = function(p){
	var that = this;
		this.panelScroll[p.id] = new GeminiScrollbar({
			element: p,
			autoshow :true,
			onResize :function(){
				//console.log(this);
				this.update();
			}
		}).create();
	
	//console.log(that.panelScroll);
	//this.handleMouseDevice(p);
	
	this.scanLoadField(p);
	this.scanFormTag(p);
	this.ScaningTableHeaderfix(p);
	this.scaningListAction(p);
	this.scaningScriptJava(p);
}


/*** Reset ***/

OwlProject.prototype.resetProject = function(options){
	var that = this;
	var Utils = this.Utils();

	this.utils = Utils;
    if(Utils.isElement(this.master)){
        this.master.innerHTML = "";
	    this.master.style.left = null;
	    this.master.style.right = null;
    }
	
	this.options = Utils.extend(Utils.extend({}, optionsDefaults), options);
	this.actionBtn = Utils.extend(Utils.extend({}, actDefaults), this.options.actions);
	if(typeof options !== 'undefined'){
		this.frameWork = Utils.createFramework(this.options);
	}else{
		this.frameWork = Utils.createFramework();
	}
	this.refresh = function(){that.refreshProject()};
	this.panel = this.frameWork.panel;
	
	this.header = this.frameWork.header;
	this.buatbaru = {
		target:undefined,
		close:function(){}
	};

	this.filter = {
		target:undefined,
		close:function(){}
	};


	this.pdfFilter = {
		target:undefined,
		close:function(){}
	};
	
	this.master = this.frameWork.master;
	
	this.clearNewContainer();
	this.nav = Utils.btn;
	if(typeof options !== 'undefined'){
		nodeListHeader = this.header.getElementsByTagName("input");//name:mainsearch 
		for(i=0;i<nodeListHeader.length; i++){
			if(nodeListHeader[i].getAttribute("name") === "mainsearch"){
				this.search = nodeListHeader[i];
				this.search.onsearch = function(){
					that.searchInTable(that.panel);
				}
				break;
			}
		}
	}
	if (this.options.type === 'report' || this.options.filter.autoshow == true) {
		this.openFilter();
	}
	this.clearingHeadfix();
	if(typeof options !== 'undefined'){
		this.refreshProject();
	}
	this.updateOnlineStatus();
	window.addEventListener('online',  this.updateOnlineStatus);
	window.addEventListener('offline', this.updateOnlineStatus);
	
	
}

OwlProject.prototype.updateOnlineStatus = function(){
	var htmlEl = document.body.parentNode;
	 var condition = navigator.onLine ? "online" : "offline";
		htmlEl.className = condition;
		this.onlineStatus = condition;
	
}
OwlProject.prototype.refreshProject = function(dataLoad,ev){
	var that = this;
	if(typeof ev !== "undefined" && typeof ev.currentTarget !== "undefined"){
		var panel = $.panel;
		var header = $.header;
		var loader = $.loader(panel);
		result = dataLoad;
		panel.innerHTML = result.response;
		panel.insertBefore(header,panel.firstChild);
		$.scanningElement(panel);
		sendUrl = ev.currentTarget.responseURL;
		let pageCi = window.history.state;
		var ajaxUrl = new URL(sendUrl);
		
		if(pageCi.template == "owlproject"){
			urlCurrent = $.utils.getURL(pageCi);
			searchParams = new  URLSearchParams(ajaxUrl.search);
			for (const p of searchParams) {
				if(urlCurrent.searchParams.has(p[0])){
					urlCurrent.searchParams.set(p[0],p[1]);
				}else{
					urlCurrent.searchParams.append(p[0],p[1]);
				}
			}
			$.utils.reSetUrlFake(urlCurrent.href,pageCi.page,pageCi.template,pageCi.prev);
		}
		loader.off();
	}else{
		this.console('Refresh..');
		var tujuan = this.options.slave; 
		
		// if(typeof dataLoad !== "undefined"){
			// tujuan = $.utils.getURL(dataLoad);+dataLoad.search;
		// }
		var loader = this.loader(this.panel);
		loader.on();
		this.getpage(this.panel,tujuan,function(result){
			that.panel.innerHTML = result.response;
			that.panel.insertBefore(that.header,that.panel.firstChild);
			that.scanningElement(that.panel);
			loader.off();
		});
	}
	
	/* this.ajax(null,this.options.slave,function(rs){
		loader.off();
		
	}); */
}

OwlProject.prototype.clearingHeadfix = function(obj){
	if(typeof obj === 'undefined'){
		for (var k in this.fxheadModul){
			if(this.fxheadModul.hasOwnProperty(k)){
				this.fxheadModul[k].clean();
			}
		}
	}else{
		if(this.fxheadModul.hasOwnProperty(obj)){
			this.fxheadModul[obj].clean();
		}
	}
}
OwlProject.prototype.repaintHeadfix = function(obj){
	if(typeof obj === 'undefined'){
		for (var k in this.fxheadModul){
			if(this.fxheadModul.hasOwnProperty(k)){
				this.fxheadModul[k].paint();
			}
		}
	}else{
		if(this.fxheadModul.hasOwnProperty(obj)){
			this.fxheadModul[obj].paint();
		}
	}
}

OwlProject.prototype.clearNewContainer = function(element){
	var that = this;
	var Utils = this.Utils();
	var master = this.frameWork.master;
	var parentMaster = master.parentNode;
	if(typeof element == 'undefined'){
		var allNewcontainer = parentMaster.getElementsByClassName('newcontainer');
		allremove = [];
		if(allNewcontainer.length > 0){
			for (var i = 0; i < allNewcontainer.length; ++i) {
				allremove.push(allNewcontainer[i]);
			}
			this.multipleRemove(allremove,0);
		}
	}else{
		if(Utils.isElement(element)){
			element.style.transform = null;
			element.style.opacity = null;
			setTimeout(function(){ 
				if(document.contains(element)){
					that.removeELe(element);
				}
			},100);
		}
	}
}

/** Remove Element ex: document.getElementById("my-element").remove(); **/
/* OwlProject.prototype.remove = function(elem) {
    elem.parentElement.removeChild(elem);
}
NodeList.prototype.remove = HTMLCollection.prototype.remove = function() {
    for(var ix00_ = this.length - 1; ix00_ >= 0; ix00_--) {
        if(this[ix00_] && this[ix00_].parentElement) {
            this[ix00_].parentElement.removeChild(this[ix00_]);
        }
    }
} */
OwlProject.prototype.removeELe = function(elem){
	if(this.Utils().isElement(elem)){
		elem.parentElement.removeChild(elem);
	}else if(this.Utils().isNodeList(elem)){
		for(var ix00_ = this.length - 1; ix00_ >= 0; ix00_--) {
			if(elem[ix00_] && elem[ix00_].parentElement) {
				elem[ix00_].parentElement.removeChild(elem[ix00_]);
			}
		}
	}
	

}
OwlProject.prototype.multipleRemove = function(elementArr,num){
	this.removeELe(elementArr[num]);
	if(elementArr.length-1 > num){
		numPlus = num+1;
		return this.multipleRemove(elementArr,numPlus);
	}
}
OwlProject.prototype.removeEventListener = function(d,c,b,a){
	if(!(d=this.Utils().getElementById(d))){
		return
	}
	c=c.toLowerCase();
	if(d.removeEventListener){
		d.removeEventListener(c,b,a||false)
	}else{
		if(d.detachEvent){
			d.detachEvent("on"+c,b)
		}else{
			d["on"+c]=null
		}
	}
}

/*** print ***/
OwlProject.prototype.printToPDF = function(filename,appname){

	// var source = window.document.getElementsByTagName("body")[0];
		// var specialElementHandlers = {
			// '#hidden-element': function (element, renderer) {
				// return true;
			// }
		// };
		// var doc = new jsPDF({
			// orientation: 'landscape'
		// });
		// doc.setFont("courier");
		// doc.setFontType("normal");
		// doc.setFontSize(24);
		// doc.setTextColor(100);
		// doc.fromHTML(elementHTML, 15, 15, {
			// 'width': 170,
			// 'elementHandlers': specialElementHandlers
		// });
	
}
OwlProject.prototype.printToExcel = function(filename,appname){
	var that = this;
	var uri = 'data:application/vnd.ms-excel;base64,'
  , html_start = `<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">`
  , template_ExcelWorksheet = `<x:ExcelWorksheet><x:Name>{SheetName}</x:Name><x:WorksheetSource HRef="sheet{SheetIndex}.htm"/></x:ExcelWorksheet>`
  , template_ListWorksheet = `<o:File HRef="sheet{SheetIndex}.htm"/>`
  , template_HTMLWorksheet = `
Content-Location: sheet{SheetIndex}.htm
Content-Type: text/html; charset=windows-1252
` + html_start + `
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
  <link id="Main-File" rel="Main-File" href="../WorkBook.htm">
  <link rel="File-List" href="filelist.xml">
</head>
<body><table>{SheetContent}</table></body>
</html>`
  , template_WorkBook = `MIME-Version: 1.0
X-Document-Type: Workbook
Content-Type: multipart/related; boundary=""
Content-Location: WorkBook.htm
Content-Type: text/html; charset=windows-1252
` + html_start + `
<head>
<meta name="Excel Workbook Frameset">
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<link rel="File-List" href="filelist.xml">
<!--[if gte mso 9]><xml>
 <x:ExcelWorkbook>
  <x:ExcelWorksheets>{ExcelWorksheets}</x:ExcelWorksheets>
  <x:ActiveSheet>0</x:ActiveSheet>
 </x:ExcelWorkbook>
</xml><![endif]-->
</head>
<frameset>
  <frame src="sheet0.htm" name="frSheet">
  <noframes><body><p>This page uses frames, but your browser does not support them.</p></body></noframes>
</frameset>
</html>
{HTMLWorksheets}
Content-Location: filelist.xml
Content-Type: text/xml; charset="utf-8"
<xml xmlns:o="urn:schemas-microsoft-com:office:office">
  <o:MainFile HRef="../WorkBook.htm"/>
  {ListWorksheets}
  <o:File HRef="filelist.xml"/>
</xml>
`
	, base64 = function (s) { return window.btoa(unescape(encodeURIComponent(s))) }
	, format = function (s, c) { return s.replace(/{(\w+)}/g, function (m, p) { return c[p]; }) }
	
	 function downloadToExcel(filename,appname) {
		  var workbook = {
			  ExcelWorksheets:'',HTMLWorksheets: '',ListWorksheets: ''
			};
		  tables = document.getElementsByTagName("table");
		  if(tables.length > 0){
			for (var i = 0; i < tables.length; i++) {
				check_print = tables[i].getAttribute("data-print");
				name_table = tables[i].getAttribute("name");
				if(name_table!='' && name_table != null){
					name = name_table;
				}else{
					name = 'Sheet ' + (i+1);
				}
				if(check_print == "true"){
					workbook.ExcelWorksheets += format(template_ExcelWorksheet, {
						SheetIndex: i,	SheetName: name
					  });
					workbook.HTMLWorksheets += format(template_HTMLWorksheet, {
						SheetIndex: i,	SheetContent: tables[i].innerHTML
					  });
					 workbook.ListWorksheets += format(template_ListWorksheet, {
						SheetIndex: i
					  });
				}
			}
			workbooks = format(template_WorkBook, workbook);
			var link = document.createElement("A");
			  link.href =  uri + base64(workbooks);
			  link.download = filename || 'Workbook.xls';
			  link.click();
			
		  }else{
				alert("Tidak ada table yang diprint");
		  }
	  }
	  downloadToExcel(filename,appname);
	
}	
OwlProject.prototype.printToExcelXml = function(filename,appname){
	var that = this;
	function removeTags(txt){
		var rex = /(<([^>]+)>)/ig;
		return txt.replace(rex , "");
	}
	var uri = 'data:application/vnd.ms-excel;base64,'
	, tmplWorkbookXML = '<?xml version="1.0"?><?mso-application progid="Excel.Sheet"?><Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
	  + '<DocumentProperties xmlns="urn:schemas-microsoft-com:office:office"><Author>Owl Plantation System</Author><Created>{created}</Created></DocumentProperties>'
	  + '<Styles>'
	  + '<Style ss:ID="Currency"><NumberFormat ss:Format="Currency"></NumberFormat></Style>'
	  + '<Style ss:ID="Date"><NumberFormat ss:Format="Medium Date"></NumberFormat></Style>'
	  + '</Styles>' 
	  + '{worksheets}</Workbook>'
	, tmplWorksheetXML = '<Worksheet ss:Name="{nameWS}"><Table>{rows}</Table></Worksheet>'
	, tmplCellXML = '<Cell{attributeStyleID}{attributeFormula}><Data ss:Type="{nameType}">{data}</Data></Cell>'
	, base64 = function (s) { return window.btoa(unescape(encodeURIComponent(s))) }
	, format = function (s, c) { return s.replace(/{(\w+)}/g, function (m, p) { return c[p]; }) }
	
	function downloadToExcel(filename,appname) {
		var that = this;
		//data-type : ["DateTime","Number","Boolean","Error"];
		//data-style : ["Date","Currency"];
		//data-value : isi yang akan di keluarkan;
		tables = document.getElementsByTagName("table");
		var ctx = "";
		var workbookXML = "";
		var worksheetsXML = "";
		var rowsXML = "";
		if(tables.length > 0){
		for (var i = 0; i < tables.length; i++) {
			check_print = tables[i].getAttribute("data-print");
			name_table = tables[i].getAttribute("name");
			if(name_table!='' && name_table != null){
				name = name_table;
			}else{
				name = 'Sheet ' + (i+1);
			}
			if(check_print == "true"){
				if (!tables[i].nodeType) tables[i] = document.getElementById(tables[i]);
				for (var j = 0; j < tables[i].rows.length; j++) {
				  rowsXML += '<Row>';
				  for (var k = 0; k < tables[i].rows[j].cells.length; k++) {
					var dataType = tables[i].rows[j].cells[k].getAttribute("data-type");
					var dataStyle = tables[i].rows[j].cells[k].getAttribute("data-style");
					var dataValue = tables[i].rows[j].cells[k].getAttribute("data-value");
					dataValue = (dataValue)?dataValue:tables[i].rows[j].cells[k].innerHTML;
					var dataFormula = tables[i].rows[j].cells[k].getAttribute("data-formula");
					dataFormula = (dataFormula)?dataFormula:(appname=='Calc' && dataType=='DateTime')?dataValue:null;
					ctx = {  attributeStyleID: (dataStyle=='Currency' || dataStyle=='Date')?' ss:StyleID="'+dataStyle+'"':''
						   , nameType: (dataType=='Number' || dataType=='DateTime' || dataType=='Boolean' || dataType=='Error')?dataType:'String'
						   , data: (dataFormula)?'':removeTags(dataValue)
						   , attributeFormula: (dataFormula)?' ss:Formula="'+dataFormula+'"':''
						  };
					rowsXML += format(tmplCellXML, ctx);
				  }
				  rowsXML += '</Row>';
				}
				ctx = {rows: rowsXML, nameWS: name};
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
		}else{
			alert("Tidak ada table yang diprint");
		}
	}
	
	downloadToExcel(filename,appname);
}

OwlProject.prototype.searchInTable = function(ele){
	function removeTags(txt){
		var rex = /(<([^>]+)>)/ig;
		return txt.replace(rex , "");
	}
	function filterItems(data,query) {
	  return data.filter(function(el) {
		  text = removeTags(el);
		   return text.toString().toLowerCase().indexOf(query.toLowerCase()) > -1;
	  });
	}
	var value = this.search.value;
	var bodyTable = ele.getElementsByTagName('tbody')[0];
	var tr = bodyTable.getElementsByTagName('tr');
	for(isea0_=0; isea0_<tr.length; isea0_++){
		result = [];
		td =  tr[isea0_].getElementsByTagName('td');
		contains = [];
		for(isea2_=0; isea2_<td.length; isea2_++){
			contains.push(td[isea2_].textContent || td[isea2_].innerText);
		}
		result = filterItems(contains,value);
		if(result.length <1 ){
			tr[isea0_].style.display = "none";
		}else{
			tr[isea0_].style.display = "table-row";
		}
	}
	if(this.fxheadModul.hasOwnProperty('tablefix'+ele.id)){
		this.fxheadModul['tablefix'+ele.id].paint();
	}
}

OwlProject.prototype.printTable = function(event){
	const typePrint = event.target.getAttribute("name") || event.target.parentNode.getAttribute("name");
	switch(typePrint){
		case 'excel':
			filename = "Workbook.xls";
			appname = "";
			this.printToExcel(filename,appname);
		break;
		case 'csv':
			filename = "Workbook";
			appname = "";
			this.printToCsv(filename,appname);
		break;
		case 'pdf':
			filename = "downloadfile";
			appname = "";
			this.printToPDF(filename,appname);
		break;
	}
}

/*** Createor ***/


OwlProject.prototype.createFiedSet = function(title,idboth){
	if(!this.Utils().getElementById(idboth)){
		var newField = document.createElement("div");
		newField.id = idboth;
		newField.classList.add('filedset');
	}else{
		newField = this.Utils().getElementById(idboth);
	}
	return result;
}

OwlProject.prototype.createNewPage = function(title,idboth,single=false,options={}){
	var that = this;
	var Utils = this.Utils();
	var optionsPageDefault = {
		move : false,
		drag : true,
		close : true,
		fixed : false,
		height : "auto",
		width : "auto",
		x : "auto",
		y : "auto"
	}
	var optionsPage = Utils.extend(Utils.extend({}, optionsPageDefault), options);
	
	var master = this.frameWork.master;
	var windowFirst = master.parentNode;
	var newcontainer = windowFirst.getElementsByClassName('newcontainer');
	jml = 1;
	topstandart = 30;
	for (var i = 0; i < newcontainer.length; ++i) {
		jml = jml+parseInt(newcontainer[i].style.zIndex);
	}
	var judul = "";
	if(typeof title !== 'undefined'){
		judul = title;
	}
	
	if(idboth != null){
		newidboth = "newpanel_"+idboth;
	}else{
		newidboth = "newpanel_"+jml;
	}
	
	var result = {};
	if(!this.Utils().getElementById(newidboth)){
		var newPanel = document.createElement("div");
		newPanel.id = newidboth;
		newPanel.style.zIndex = jml;
		newPanel.classList.add('newcontainer');
		newPanel.classList.add('body');
		if(Number.isInteger(optionsPage.width)){
			newPanel.style.width = Math.abs(optionsPage.width)+"px";
			newPanel.style.right = "unset";
		}
		if(Number.isInteger(optionsPage.height)){
			newPanel.style.height = Math.abs(optionsPage.height)+"px";
			newPanel.style.bottom = "unset";
		}
		if(Number.isInteger(optionsPage.x)){
			//if(Number.isInteger(optionsPage.width) && (optionsPage.x+Math.abs(optionsPage.width)) > window.innerWidth){
				//console.log((optionsPage.x-Math.abs(optionsPage.width)),window.innerWidth);
				//newPanel.style.left = (optionsPage.x-Math.abs(optionsPage.width))+"px";
			//}else{
				newPanel.style.left = optionsPage.x+"px";
			//}
		}
		if(Number.isInteger(optionsPage.y)){
			/*if(Number.isInteger(optionsPage.height) && (optionsPage.y+Math.abs(optionsPage.height)) > window.innerHeight){
				newPanel.style.top = (optionsPage.y-Math.abs(optionsPage.height))+"px";
			}else{*/
				newPanel.style.top = optionsPage.y+"px";
			//}
		}
		paneBar = document.createElement("div");
		paneBar.classList.add('panel-bar');
		paneBar.id = "head"+newPanel.id;

		if (title !== 'Filter') {
			paneBar.onmousedown = function(event){
				if(optionsPage.drag == true ){
					that.followMouse(this,event,single,optionsPage.move);
				}
			};
		}
		
		if(optionsPage.close == true){
			btnClose = document.createElement("div");
			btnClose.classList.add('btnnavbar');
			btnClose.classList.add('active');
			btnClose.classList.add('active');
			btnClose.classList.add('pull-right');
			btnClose.innerHTML = '<img class="naviconBig" src="images/navigasi/times.png" title="Close">';
			btnClose.onclick = function(event){
				event.stopPropagation();
				that.removeEl(newPanel);
			};
			paneBar.appendChild(btnClose);
		}
		
		judulSpan = document.createElement("span");
		if(this.options.hasOwnProperty(idboth)){
			objNameEle = this.options[idboth];
			if(objNameEle.hasOwnProperty('image')){
				if(objNameEle.image && objNameEle.image!==""){
					icon = '<img src="'+objNameEle.image+'" style="height:10px;">';
					judul = icon+"&nbsp;&nbsp;"+judul;
				}
			}
		}
		judulSpan.innerHTML = judul;
		paneBar.appendChild(judulSpan);
		
		newPanelBody = document.createElement("div");
		newPanelBody.id = "body"+newPanel.id;
		newPanelBody.classList.add('panel');
		newPanelBody.style.top ="35px";
		newPanel.appendChild(paneBar);
		newPanel.appendChild(newPanelBody);
		windowFirst.appendChild(newPanel);

		if (title === 'Filter') {
			optionFilter = {
				width: this.options.filter.width
			}
			this.fixedRightMenu(paneBar,optionFilter);
		}
		result.panel = newPanel;
		result.body = newPanelBody;
		result.head = paneBar;
	}else{
		newPanel = this.Utils().getElementById(newidboth);
		newPanelBody = this.Utils().getElementById("body"+newidboth);
		paneBar = this.Utils().getElementById("head"+newidboth);
		result.panel = newPanel;
		result.body = newPanelBody;
		result.head = paneBar;
	}
	return result;
}

OwlProject.prototype.openPDF = function(idboth,title,slave,width,height,evt){
	var that = this;
	single=false;
	if(!Number.isInteger(width)){
		width = 400;
	}
	if(!Number.isInteger(height)){
		height = 350;
	}
	var options = {
		close : true,
		width:Math.abs(width),
		height:Math.abs(height),
		move : true,
		drag : true,
		type : 'PDF'
	};
	var el = this.createNewPage(title,idboth,single,options);
	var loader = this.loader(el.panel);
	loader.on();
	var callback = {target:el,close:function(){that.removeEl(el.panel)},refresh:function(){}};
	callback.refresh = function(){
							that.getpage(el.panel,slave,function(result){
								el.body.innerHTML = result.response;
								that.scanningElement(el.body);
							});
						};
	callback.refresh();
	return callback;
}
OwlProject.prototype.openWindow = function(options){
	//datatype : xml,html,json,script;
	var optionsWindow = {
		context :null,
		title :"New Window",
		url :"",
		dataType:"html",
		error:function(){},
		success:function(){},
		complete:function(){}
	}
	var Utils = this.Utils();
	newOptions = Utils.extend(Utils.extend({}, optionsWindow), options);
	
	var that = this;
	var opt = {
		close : true
	};
	
	var slave = newOptions.url;
	var el = this.createNewPage(newOptions.title,newOptions.context,false,opt);
	var loader = this.loader(el.panel);
	loader.on();
	var rest = {target:el,close:function(){that.removeEl(el.panel)},refresh:function(){}};
	rest.refresh = function(){
				that.getpage(el.panel,slave,function(result){
					if(typeof el.body != 'undefined'){
						el.body.innerHTML = result.response;
						that.scanningElement(el.body);
						// that.scaningScriptJava(el.body);
					}
					if(typeof newOptions.success != 'undefined' && typeof newOptions.success == 'function'){
						newOptions.success(rest);
					}
					if(typeof newOptions.complete != 'undefined' && typeof newOptions.complete == 'function'){
						newOptions.complete(rest);
					}
				});
			};
	rest.refresh();
	return rest;
}
OwlProject.prototype.newWindow = function(slave,title,idboth,callback,single=false){
	var that = this;
	var options = {
		close : true
	};
	var el = this.createNewPage(title,idboth,single,options);
	var loader = this.loader(el.panel);
	loader.on();
	var rest = {target:el,close:function(){that.removeEl(el.panel)},refresh:function(){}};
	rest.refresh = function(){
				that.getpage(el.panel,slave,function(result){
					if(typeof el.body != 'undefined'){
						el.body.innerHTML = result.response;
						that.scanningElement(el.body);
						// that.scaningScriptJava(el.body);
					}
					if(typeof callback != 'undefined' && typeof callback == 'function'){
						callback(rest);
					}
				});
			};
	rest.refresh();
	return rest;
}
OwlProject.prototype.newDialog = function(idboth,title,slave,width,height,evt){
	var that = this;
	ev = evt || window.event;
	x = (ev.clientX-this.master.parentNode.offsetLeft)+ev.width;
	y = ev.clientY+ev.height;
	//console.log(x,y);
	single=false;
	if(!Number.isInteger(width)){
		width = 400;
	}
	if(!Number.isInteger(height)){
		height = 350;
	}
	var options = {
		close : true,
		x:x,
		y:y,
		width:Math.abs(width),
		height:Math.abs(height),
		move : true,
		drag : true
		
	};
	if(idboth != null){
		newidboth = "newpanel_"+idboth;
	}
	if(this.Utils().getElementById(newidboth)){
		this.removeELe(this.Utils().getElementById(newidboth));
	}
	var el = this.createNewPage(title,idboth,single,options);
	
	var loader = this.loader(el.panel);
	loader.on();
	var expression = /https?:\/\//g;
	var regex = new RegExp(expression);

	var callback = {target:el,close:function(){that.removeEl(el.panel)},refresh:function(){}};
	if (slave.match(regex)) {
		callback.refresh = function(){
							that.getpage(el.panel,slave,function(result){
								el.body.innerHTML = result.response;
								that.scanningElement(el.body);
							});
						};
	}else{
		loader.off();
		el.body.innerHTML = slave;
		this.scanningElement(el.body);
	}
	
	return callback;
}
OwlProject.prototype.loader = function(elemBoth){
	var Utils = this.Utils();
	function off(progressEle){
		if(progressEle){
			progressEle.style.display = "none";
		}
	}
	function on(progressEle){
		if(progressEle){
			progressEle.style.display = "flex";
		}
	} 
	function changes(elem,text){
		elem.innerHTML = text;
	}
	function createloader(elem){
		var idelement = "";
		if(typeof elem !== 'undefined' && elem.getAttribute("id") != null){
			idelement = elem.getAttribute("id");
		}
		//console.log(elem);
		rect = elem.getBoundingClientRect();
		var idProgress = "progress";
		if(idelement === '' || idelement == null){
			allProgress = document.getElementsByClassName('progress');
			jml = allProgress.length;
			idProgress += "_"+jml;
		}else{
			idProgress += "_"+idelement;
		}
		let parentIsBody = false;
		//console.log(elem);
		if(elem.classList.contains('body') || elem.classList.contains('masterpanel')){
			parentIsBody = true;
		}
		if(Utils.getElementById(idProgress)){
			/* newLoader = document.getElementById('progress').cloneNode(true); */
			newLoader = Utils.getElementById(idProgress);
			textstat = Utils.getElementById(idProgress+"_responsestats");
		}else{
			newLoader = document.createElement("div");
			// newLoader.classList.add('progress');
			// newLoader.style.position = "absolute";
			// newLoader.style.display = "none";
			loaderBody = document.createElement("div");
			// loaderBody.classList.add('progress-body');
			textstat = document.createElement("div");
			textstat.id = idProgress+"_responsestats";
			textstat.style.float = "left";
			textstat.style.lineHeight= '20px';
			textstat.style.marginLeft= '10px';
			textstat.style.textTransform = 'uppercase';
			textstat.innerHTML = "Please wait..";
			imgProgress = new Image();
			imgProgress.style.width = "30px";
			imgProgress.style.float = "left";
			imgProgress.src = "images/progress.gif?v=2";
			loaderBody.appendChild(imgProgress);
			loaderBody.appendChild(textstat);
			newLoader.appendChild(loaderBody);
			newLoader.id = idProgress;
			if(typeof elem !== 'undefined'){
				//rectBody = loaderBody.getBoundingClientRect();
				//newLoader.style.transform = "translate(10px,"+rect.y+"px)";
				//newLoader.style.width = rect.width+"px"; 
				newLoader.style.height = rect.height+"px";
				var rectP = new Array();
				rectP['top'] = 0;
				rectP['bottom'] = 0;
				rectP['left'] = 0;
				rectP['right'] = 0;
				
				if(elem.parentNode){
					rectP = elem.parentNode.getBoundingClientRect();
				}
				newLoader.style.position = 'absolute';
				if(parentIsBody){
					newLoader.style.width = '100%';
					newLoader.style.top = '0px';
					newLoader.style.left = '0px';
					//newLoader.style.background = '#cee1fa';
				}else{
					newLoader.style.left = rect.left+'px';
					newLoader.style.width = (rect.right-rect.left)+'px';
					newLoader.style.top = (rect.top-rectP.top)+'px';
				}
				newLoader.style.backdropFilter = 'blur(2px)';
				newLoader.style.alignItems = 'center';
				newLoader.style.justifyContent = 'center';
  
				elem.appendChild(newLoader);
			}else{
				newLoader.style.position = "fixed";
				if(Utils.getElementById('wraperbody')){
					Utils.getElementById('wraperbody').appendChild(newLoader);
				}else{
					document.body.appendChild(newLoader);
				}
				
			}
		}
		return newLoader;
	}
	var loaderEle = createloader(elemBoth);
	
	return {
		changes : function(text){changes(textstat,text);},
		off : function(){off(loaderEle);},
		on : function(){on(loaderEle);}
	}
}
/*** AJAX ***/
OwlProject.prototype.functSubmit = function(form,evt){
	
}

/* UMAR */
OwlProject.prototype.mod = function(evt, obj, text){
	var res = '';
	switch(evt){
		case 'changes':
			res = obj.innerHTML = text;
		break;
		case 'on':
			res = obj.style.display = "";
		break;
		case 'off':
			res = obj.style.display = "none";
		break;
		case 'readonly':
			res = obj.readOnly = true;
		break;
		case 'values':
			res = obj.value = text;
		break;
		case 'disable':
			res = obj.disabled = true;
		break;
		case 'getId':
			res = document.getElementById(obj);
		break;
		case 'getClass':
			res = document.getElementsByClassName(obj);
		break;
		case 'getTag':
			res = document.getElementsByTagName(obj);
		break;
		case 'getInner':
			res = this.mod('getId', obj).innerHTML;
		break;
		case 'getOption':
			res = this.mod('getId', obj);
			res = res.options[res.selectedIndex].value;
		break;
		case 'getValue':
			res = this.mod('getId', obj).value;
		break;
		case 'getDisplay':
			res = document.getElementById(obj).style.display;
		break;
	}
	return res;
}

OwlProject.prototype.console = function(text){
	return console.log(text);
}

OwlProject.prototype.printToCsv = function(filename){
	var csv = [];
    var rows = document.querySelectorAll("table tr");
    
    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll("td, th");
        
        for (var j = 0; j < cols.length; j++) 
            row.push(cols[j].innerText);
        
        csv.push(row.join(","));        
    }

    function downloadCSV(csv, filename) {
	    var csvFile;
	    var downloadLink;

	    csvFile 					= new Blob([csv], {type: "text/csv"});
	    downloadLink 				= document.createElement("a");
	    downloadLink.download 		= filename + ".csv";
	    downloadLink.href 			= window.URL.createObjectURL(csvFile);
	    downloadLink.style.display 	= "none";

	    document.body.appendChild(downloadLink);
	    downloadLink.click();
	}

    // Download CSV file
    downloadCSV(csv.join("\n"), filename);
}


OwlProject.prototype.btnPdf = function(event){
	var btn = this.options.pdf;
	var url = this.options.slave + "?" + this.options.getpage + "=" + btn.slave;
	var create = this.newWindow(url,btn.title,btn.name,true,true);
	this.pdfFilter = create;
}

OwlProject.prototype.fixedLeftMenu = function(ele){
	var bodymaster = this.frameWork.master;
	bodymaster.style.left = 200+"px";

	panel = ele.parentNode;
	panel.style.bottom = 0 + "px";
	panel.style.width = 200 + "px";
	panel.style.transform = 'translateX(0px)';
	panel.style.transitionDuration = '0.2s';
	panel.style.zIndex = 1;
	panel.style.cursor = null;
	panel.setAttribute("parentid", ele.parentNode.id);
	panel.setAttribute("window-position","left");
	if(this.fxheadModul.hasOwnProperty('tablefix' + panel.id)){
		this.fxheadModul['tablefix' + panel.id].paint();
	}
}


OwlProject.prototype.swiperFrameMenu = function(idele,idboth,ele,event){
	div = document.getElementById(idele);
	both = document.getElementById(idboth);
	var x = event.clientX, y = event.clientY;
	ele.onmousemove = function (ev){
		leftx = ev.screenX;
		that.console(leftx);
		topy = y;
		ele.style.zIndex = 10000;
		ele.style.transform = "translateX("+ev.x+"px)";
		ele.style.transitionDuration = '0s';
	}
	ele.onmouseup  = function (e){
		//backtoasal(ele,e,zindexFirst);
	}
	window.onmouseup= function (e){
		//backtoasal(ele,e,zindexFirst);
	};
	function backtoasal(ele,event,indx) {
		div = ele.parentNode;
		ele.onmousemove = null;
		window.onmouseup = null;
		ele.style.transform = 'translateX(0px)';
		ele.style.transitionDuration = '0.2s';
		ele.style.zIndex = null;
		fxheadModul[0].paint();
	}
}
OwlProject.prototype.fixedRightMenu = function(ele,optionFixRighr){
	var Utils = this.Utils();
	let optionsFixRightDefault = {
		width : 30,
		bottom : 0,
		right : 0,
		zIndex : 1
	}
	let options = Utils.extend(Utils.extend({}, optionsFixRightDefault), optionFixRighr);
	var bodymaster = this.frameWork.master;
	rect = bodymaster.getBoundingClientRect();
	if(options.width.toString().toLowerCase().includes("px")){
		w = options.width;
	}else{
		if(Number.isInteger(options.width)){
			w = ((rect.width/100)*options.width)+"px";
		}else{
			w = ((rect.width/100)*parseInt(options.width))+"px";
		}
	}
	bodymaster.style.right = w;
	panel = ele.parentNode;
	panel.style.bottom = options.bottom + "px";
	panel.style.width = w;
	panel.style.left = "unset";
	panel.style.right =  options.right + "px";
	panel.style.transform = 'translateX(0px)';
	panel.style.transitionDuration = '0.2s';
	panel.style.zIndex = options.zIndex;
	panel.style.cursor = null;
	panel.setAttribute("parentid", ele.parentNode.id);
	panel.setAttribute("window-position","right");
	if(this.fxheadModul.hasOwnProperty('tablefix' + panel.id)){
		this.fxheadModul['tablefix' + panel.id].paint();
	}
}
OwlProject.prototype.createPaginationTable = function(jmlBaris, limit, pages, colspan, method){
	let tab 		= '';
	let isiRow 		= '';
	let limitless 	= '';
	let totalRows 	= Math.ceil(jmlBaris/limit);

	if (totalRows === 0) {totalRows = 1}
	for (var i = 0; i <= totalRows; i++) {
		let sel = '';
		if (pages === i-1) {sel = 'selected'}
		if (i == 0) 
			isiRow += '';
		else 
			isiRow += '<option value="' +i+ '" ' +sel+ '>' +i+ '</option>';		
	}

	let fromPage 	= ((pages * limit)+1);
	let toPage 		= '';
	if (((pages+1) * limit) > jmlBaris)
		toPage = jmlBaris
	else
		toPage = ((pages + 1) * limit);


	// tab += '<table width="100%" border=0>';	
	// tab += '<tr>';	
		// tab += '<td colspan="' +colspan+ '" align="center">';
			tab += '<label style="float:left;margin:5px">Showing : </label><select style="min-width:20px;float:left;margin:5px" onchange="preview(this.value)"><option value="20" ' + ((limit == 20) ? "selected" : "") + '>20</option><option value="50" ' + ((limit == 50) ? "selected" : "") + '>50</option><option value="100" ' + ((limit == 100) ? "selected" : "") + '>100</option></select>';	
			tab += '<input type="text" value="'+(pages+1)+'" onkeyup="' + method + '((this.value-1), ' + limit + ', ' + toPage + ', `nothere`, ' + jmlBaris + ')" class="myinputtext" style="margin:5px;width:20px"/> Of <span id="jmlRow">'+totalRows+'</span>';
			// tab += '<input type="text" value="'+(pages+1)+'" onkeyup="' + method + '((this.value-1), ' + limit + ', ' + toPage + ', `nothere`, ' + jmlBaris + ')" class="myinputtext" style="margin:5px;width:20px"/> Of <span id="jmlRow"></span>';
			tab += '<div style="float:right;margin:5px">';
				tab += fromPage+ ' - ' +toPage+ ' Of <span id="jmlBaris">' +jmlBaris+ '</span> ';
				// tab += fromPage+ ' - ' +toPage+ ' Of <span id="jmlBaris"></span> ';
				if (jmlBaris > limit) {
					if (pages == 0) {
						tab += '<button class="mybutton" disabled><</button>';
					} else {
						tab += '<button class="mybutton" onclick="' + method + '(' + (pages - 1) + ', ' + limit + ', ' + (fromPage-1) + ', `nothere`, ' + jmlBaris + ')"><</button>';
					}
					// tab += '<select style="min-width:20px" onchange="' + method + '((this.value-1))">' + isiRow + '</select>';
					if ((pages + 1) == totalRows) {
						tab += '<button class="mybutton" disabled>></button>';
					} else {
						tab += '<button class="mybutton" onclick="' + method + '(' + (pages + 1) + ', ' + limit + ', ' + toPage + ', `nothere`, ' + jmlBaris + ')">></button>';
					}
				}
			tab += '</div>';
		// tab += '</td>';	
	// tab += '</tr>';
	// tab += '</table>';

	return tab;	
}

OwlProject.prototype.sleep = function(duration){
	return new Promise(resolve => {
		setTimeout(() => {
			resolve()
		}, duration * 1000)
	})
}

OwlProject.prototype.stop = function(){
	return window.stop();
}

OwlProject.prototype.createTable = function(column){
	let pagination 	= document.createElement('div');
	let table 		= document.createElement('table');
	table.classList.add('sortable');
	table.classList.add('data-table');
	table.classList.add('full-width');
	let thead 		= document.createElement('thead');
	let tr 			= document.createElement('tr');
	tr.classList.add('rowheader');
	for (var i = 0; i < column.length; i++) {
		td 				= document.createElement("td");
		td.innerHTML 	= column[i];
		tr.appendChild(td);
	}
	thead.appendChild(tr);
	let tbody 		= document.createElement('tbody');
	pagination.id 	= 'pagination';
	tbody.id 		= 'tBodyData';

	table.appendChild(thead);
	table.appendChild(tbody);

	//console.log(table);

	return {
		'pagination' : pagination,
		'table' : table
	};
}
/* END UMAR */

OwlProject.prototype.errorPage = function(err){
	var src = "";
	switch(err){
		case 404:
			src = "images/navigasi/404.png";
		break;
	}
	var img = '<img class="erro_page" src="'+src+'">';
	return img;
}

OwlProject.prototype.getpage = function(elem,action,callbackEvt){
	this.sendAjax(elem,action,callbackEvt);
}

OwlProject.prototype.get = function(eleform,action,callbackEvt){
	this.sendAjax(eleform,action,callbackEvt);
}

OwlProject.prototype.post = function(eleform,action,callbackEvt){
	this.sendAjax(eleform,action,callbackEvt);
}

OwlProject.prototype.sendAjax = function(eleform,action,funct){
	//var par= parent.location.href.replace(/^http:\/\//i, 'https://');
	var conPost = this.Utils().getXMLHttpRequest();
	var that = this;
	var par= parent.location.href.replace(/(^\w+:|^)\/\//, '');
	var loader = this.loader(eleform);
	loader.on();

	/* 
	0	UNSENT  open() has not been called yet.
	1   OPENED  send() has been called.
	2   HEADERS_RECEIVED    send() has been called, and headers and status are available.
	3   LOADING Downloading; responseText holds partial data.
	4   DONE    The operation is complete.
	*/

	function sendAjaxSuccess(event){
		if(this.readyState === XMLHttpRequest.UNSENT){
			loader.changes('UNSENT');
			//that.console("Has not been called yet.");
		}else if(this.readyState === XMLHttpRequest.OPENED){
			loader.changes('OPENED');
			//that.console("Has been called.");
		}else if(this.readyState === XMLHttpRequest.HEADERS_RECEIVED){
			loader.changes('HEADERS RECEIVED');
			//that.console("Has been called, and headers and status are available.");
		}else if(this.readyState === XMLHttpRequest.LOADING){
			loader.changes('LOADING..');
			//that.console("ResponseText holds partial data.");
		}else if(this.readyState === XMLHttpRequest.DONE || this.readyState === 4){
			loader.off();
			//console.log(this.status);
			if(this.status === 200){
				try {
					//JSON Result
					var arrJ = JSON.parse(this.responseText);
					var result = {};
					result.element = eleform;
					result.response = arrJ;
					funct(result,event);
				}catch(e){
					try{
						//STRING Result
						var result = {};
						result.element = eleform;
						result.response = this.responseText;
						funct(result,event);
					 }catch(e){
						that.console(e,eleform,this.responseText);
					 }
				}
            }else if(this.status===203){
				//Relogin
				//var file = location.href.replace(/(^\w+:|^)\/\//, '');
				//uri = parent;
				//encodeURI(uri);
				//window.location = 'logout.php?';
			}else{
				that.console("Response received with status : " + this.status);
				that.console(this.responseText);
			}
        }
	}
	zz=verify();
    if(zz){
		
		var txtaction = "";
		var file = "";
		var searchTxt = "";
		if(action){
			txtaction = action;
		}else{
			if (!eleform || (eleform && typeof eleform.attributes.action == 'undefined')){
				txtaction = "";
			}else{
				if(typeof eleform.attributes.action.value != 'undefined'){
					txtaction = eleform.attributes.action.value;
				}else{
					txtaction = eleform.attributes.action;
				}
			}
		}
		//console.log(txtaction);
		var newTxtaction = txtaction;
		var link = txtaction.split("?");
		if(link.length > 0){
			file = link[0];
			if(link.length > 1){
				searchTxt = link[1];
			}
			if(searchTxt.trim() == ""){
				searchTxt = "par="+par;
			}else{
				searchTxt += "&par="+par;
			}
			newTxtaction = file+"?"+searchTxt;
		}
		if(typeof $.onlineStatus != 'undefined' && $.onlineStatus != 'online'){
			conPost.abort();
			alert("Your Application is Offline");
			return false;
		}
		if ((!eleform && newTxtaction )|| (eleform && typeof eleform.attributes.method == 'undefined' && newTxtaction)){
			conPost.open("get",newTxtaction, true);
			conPost.onreadystatechange = sendAjaxSuccess;
			conPost.onprogress = (e)=>{
				console.log(e);
			}
			conPost.send(null);
		}else if(eleform && typeof eleform.attributes.method !== 'undefined' && eleform.attributes.method.value.toLowerCase() === "post") {
			//that.console(newTxtaction);
			conPost.open("POST", newTxtaction,true);
			conPost.onreadystatechange = sendAjaxSuccess;
			conPost.onprogress = (e)=>{
				console.log(e);
			}
			if(eleform.tagName.toLowerCase() == 'form'){
				var dataform = new FormData(eleform);
				//tambahan 
				dataform.append('par', par);
				conPost.send(dataform);
			}else{
				var dataform = eleform;
				//tambahan 
				dataform.append('par', par);
				conPost.send(dataform);
			}
		}else if(eleform && typeof eleform.attributes.method !== 'undefined' && eleform.attributes.method.value.toLowerCase() === "get") {
			var oField, sFieldType, nFile, sSearch = "";
			for (var nItem = 0; nItem < eleform.elements.length; nItem++) {
			  oField = eleform.elements[nItem];
			  if (!oField.hasAttribute("name")) { continue; }
			  sFieldType = oField.nodeName.toUpperCase() === "INPUT" ?
				  oField.getAttribute("type").toUpperCase() : "TEXT";
			  if (sFieldType === "FILE") {
				for (nFile = 0; nFile < oField.files.length;
					sSearch += "&" + escape(oField.name) + "=" + escape(oField.files[nFile++].name));
			  } else if ((sFieldType !== "RADIO" && sFieldType !== "CHECKBOX") || oField.checked) {
				sSearch += "&" + escape(oField.name) + "=" + escape(oField.value);
			  }
			}
			//tambahan 
			sSearch+='&par='+par;
			conPost.open("get", newTxtaction.replace(/(?:\?.*)?$/, sSearch.replace(/^&/, "?")), true);
			conPost.onreadystatechange = sendAjaxSuccess;
			conPost.onprogress = (e)=>{
				console.log(e);
			}
			conPost.send(null);
		}
	}else{
        window.location='logout.php';
	}

}

OwlProject.prototype.togglehide = function(event){
	var e = event.target;
	var both = e.parentNode;
	var timer;
	var panel = this.frameWork.panel;
	var header = this.frameWork.header;
	var master = this.frameWork.master;
	if(e.getAttribute("status") == "hide"){
		both.classList.remove('hide');
		e.classList.remove('down');
		e.setAttribute("status","show");
		this.fxheadModul['tablefix'+panel.id].paint();
	}else{
		both.classList.add('hide');
		e.classList.add('down');
		e.setAttribute("status","hide");
		child = this.childNodes;
		this.fxheadModul['tablefix'+panel.id].paint();
	}

}

OwlProject.prototype.removeEl = function(element){
	this.clearNewContainer(element);
	if(element.getAttribute("window-position")){
		switch(element.getAttribute("window-position")){
			case'left':
				this.master.style.left = null;
			break;
			case'right':
				this.master.style.right = null;
			break;
		}
	}
}

OwlProject.prototype.btnNew = function(event){
	var that = this;
	var btn = this.options.buatbaru;
	var url = this.options.slave+"?"+this.options.getpage+"="+btn.slave
	var create = this.newWindow(url,btn.title,btn.name,true,false);
	this.buatbaru = create;
}

OwlProject.prototype.openFilter = function(event){
	var that = this;
	if(typeof that.filter.target == 'undefined' || !document.contains(that.filter.target.panel)){
		that.filter = {
			target:undefined,
			close:function(){}
		};
		var btn = that.options.filter;
		var url = that.options.slave+"?"+that.options.getpage+"="+btn.slave
		var create = that.newWindow(url,btn.title,btn.name,true,true);
		that.filter = create;
	}else{
		that.filter.close();
		that.filter = {
			target:undefined,
			close:function(){}
		};
	}
}
/*** Handler ***/

OwlProject.prototype.followMouse = function(ele,event,single,move){
	var that = this;
	var position = null;
	event.preventDefault();
	event.stopPropagation();
	var panel = ele.parentNode;
	var zindexFirst = panel.style.zIndex;
	var xpanel = event.clientX, ypanel = event.clientY;
	var wW = window.innerWidth;
	var wH = window.innerHeight;
	var x = panel.offsetLeft, y = panel.offsetTop;
	var dragIt = true;
	function endmove(ele,event,indx){
		dragIt = false;
		panel = ele.parentNode;
		window.onmouseup = null;
		window.onmousemove = null;
		panel.style.zIndex = indx;
		panel.style.cursor = null;
		panel.style.transitionDuration = '0.2s';
		if(that.fxheadModul.hasOwnProperty('tablefix'+panel.id)){
			that.fxheadModul['tablefix'+panel.id].paint();
		}
		if(that.fxheadModul.hasOwnProperty('tablefix'+that.panel.id)){
			that.fxheadModul['tablefix'+that.panel.id].paint();
		}
		if(position == "left"){
			panel.setAttribute("window-position","left");
		}else if(position == "right"){
			panel.setAttribute("window-position","right");
		}else{
			panel.removeAttribute("window-position");
		}
		
		panel.style.top = (y)+"px";
		panel.style.left = (x)+"px";
		panel.style.transform = null;
	}
	function backtoasal(ele,event,indx) {
		panel = ele.parentNode;
		window.onmouseup = null;
		window.onmousemove = null;
		panel.style.transform = 'translateX(0px)';
		panel.style.transitionDuration = '0.2s';
		panel.style.zIndex = indx;
		panel.style.cursor = null;
		if(that.fxheadModul.hasOwnProperty('tablefix'+panel.id)){
			that.fxheadModul['tablefix'+panel.id].paint();
		}
		if(that.fxheadModul.hasOwnProperty('tablefix'+that.panel.id)){
			that.fxheadModul['tablefix'+that.panel.id].paint();
		}
		if(position == "left"){
			panel.setAttribute("window-position","left");
		}else if(position == "right"){
			panel.setAttribute("window-position","right");
		}else{
			panel.removeAttribute("window-position");
		}
	}
	
	deviderLine = document.createElement("div");
	deviderLine.style = "cursor: e-resize;width:3px;position:absolute;top:30px;right:200px;bottom:0px;background:#ccc;";
	readyPlaceLeft = document.querySelectorAll('[window-position="left"]');
	var readyPlaceRight = document.querySelectorAll('[window-position="right"]');
	var readyL = true;
	var readyR = true;
	if(readyPlaceLeft.length>0){
		readyL = false;
	}
	if(readyPlaceRight.length>0){
		readyR = false;
	}
	var bodymaster = this.frameWork.master;
	var parentMaster = bodymaster.parentNode;
	window.onmousemove = function (e){
		if(dragIt){
			if(single && readyL && e.x < 30){
				this.setAttribute("parentid",this.parentNode.id);
				bodymaster.style.left = 200+"px";
				this.style.bottom = 0+"px";
				this.style.width = 200+"px";
				if(that.fxheadModul.hasOwnProperty('tablefix'+that.panel.id)){
					that.fxheadModul['tablefix'+that.panel.id].paint();
				}
				if(that.fxheadModul.hasOwnProperty('tablefix'+panel.id)){
					that.fxheadModul['tablefix'+panel.id].paint();
				}
				position = "left";
			}else if(single && readyR && e.x < 30){
					
				this.setAttribute("parentid",this.parentNode.id);
				bodymaster.style.right = 200+"px";
				this.style.bottom = 0+"px";
				this.style.width = 200+"px";
				readyPlaceRight.style.left = "unset";
				readyPlaceRight.style.right =  0+"px";
				//bodymaster.appendChild(deviderLine);
				if(that.fxheadModul.hasOwnProperty('tablefix'+that.panel.id)){
					that.fxheadModul['tablefix'+that.panel.id].paint();
				}
				if(that.fxheadModul.hasOwnProperty('tablefix'+panel.id)){
					that.fxheadModul['tablefix'+panel.id].paint();
				}
				position = "right";
			}else{
			
				balance = e.offsetX-panel.offsetLeft;
				lefty = e.clientX-xpanel;
				topy = e.clientY-ypanel;
				x = panel.offsetLeft+lefty;
				y = panel.offsetTop+topy;
				panel.style.cursor = "move";
				panel.style.zIndex = 10000;
				//if((panel.offsetLeft+lefty) >=0 && (panel.offsetTop+topy) >=0 && wW-(panel.offsetLeft+(panel.offsetWidth/2)+lefty) >=0 ){
					panel.style.transform = "translate("+lefty+"px,"+topy+"px)";
				//}
				panel.style.transitionDuration = '0s';
				rect = bodymaster.getBoundingClientRect();
				if(this.parentNode === parentMaster && e.x > rect.left){
					this.style.bottom = null;
					this.style.width = null;
					//bodymaster.style.left = null;
				}
				position = null;
			}
		}
	};
	
	window.onmouseup= function (e){
		ele.onmousemove = null;
		if(!move){
			backtoasal(ele,e,zindexFirst);
		}else{
			endmove(ele,e,zindexFirst);
		}
	};

	
}


			
function openfiter(e){
	listfilter = document.getElementById('listfilter');
	listfilter.classList.toggle('open');
	e.classList.toggle('active');
}


function cloneheader(){
	function cloneAttributes(target, source) {
	  [...source.attributes].forEach( attr => { target.setAttribute(attr.nodeName === "id" ? 'data-id' : attr.nodeName ,attr.nodeValue) })
	}
	var table = document.getElementsByClassName("sortable");
	if(table.length > 0){
		tableEle = table[0];
		both = tableEle.parentNode;
		thead = document.getElementsByTagName("thead");
		var cln = thead[0].cloneNode(true);
		newTable = document.createElement("table");
		cloneAttributes(newTable,tableEle);
		newTable.appendChild(cln);
		both.insertBefore(newTable,tableEle);
	}
}

//scrollbar custom
(function() {
  var SCROLLBAR_WIDTH, DONT_CREATE_GEMINI, CLASSNAMES;

  CLASSNAMES = {
    element: 'owl-scrollbar-container',
    verticalScrollbar: 'owl-scrollbar -vertical',
    horizontalScrollbar: 'owl-scrollbar -horizontal',
    thumb: 'thumb',
    view: 'owl-scroll-view',
    autoshow: 'owl-autoshow',
    disable: 'owl-scrollbar-disable-selection',
    prevented: 'owl-prevented',
    resizeTrigger: 'owl-resize-trigger',
  };

  function getScrollbarWidth() {
    var e = document.createElement('div'), sw;
    e.style.position = 'absolute';
    e.style.top = '-9999px';
    e.style.width = '100px';
    e.style.height = '100px';
    e.style.overflow = 'scroll';
    e.style.msOverflowStyle = 'scrollbar';
    document.body.appendChild(e);
    sw = (e.offsetWidth - e.clientWidth);
    document.body.removeChild(e);
    return sw;
  }

  function addClass(el, classNames) {
    if (el.classList) {
      return classNames.forEach(function(cl) {
        el.classList.add(cl);
      });
    }
    el.className += ' ' + classNames.join(' ');
  }

  function removeClass(el, classNames) {
    if (el.classList) {
      return classNames.forEach(function(cl) {
        el.classList.remove(cl);
      });
    }
    el.className = el.className.replace(new RegExp('(^|\\b)' + classNames.join('|') + '(\\b|$)', 'gi'), ' ');
  }


  function isIE() {
    var agent = navigator.userAgent.toLowerCase();
    return agent.indexOf("msie") !== -1 || agent.indexOf("trident") !== -1 || agent.indexOf(" edge/") !== -1;
  }

  function GeminiScrollbar(config) {
    this.element = null;
    this.autoshow = false;
    this.createElements = true;
    this.forceGemini = false;
    this.onResize = null;
    this.minThumbSize = 20;

    Object.keys(config || {}).forEach(function (propertyName) {
      this[propertyName] = config[propertyName];
    }, this);

    SCROLLBAR_WIDTH = getScrollbarWidth();
    DONT_CREATE_GEMINI = ((SCROLLBAR_WIDTH === 0) && (this.forceGemini === false));

    this._cache = {events: {}};
    this._created = false;
    this._cursorDown = false;
    this._prevPageX = 0;
    this._prevPageY = 0;

    this._document = null;
    this._viewElement = this.element;
    this._scrollbarVerticalElement = null;
    this._thumbVerticalElement = null;
    this._scrollbarHorizontalElement = null;
    this._scrollbarHorizontalElement = null;
  }

  GeminiScrollbar.prototype.create = function create() {
    if (DONT_CREATE_GEMINI) {
      addClass(this.element, [CLASSNAMES.prevented]);

      if (this.onResize) {
        // still need a resize trigger if we have an onResize callback, which
        // also means we need a separate _viewElement to do the scrolling.
        if (this.createElements === true) {
          this._viewElement = document.createElement('div');
          while(this.element.childNodes.length > 0) {
            this._viewElement.appendChild(this.element.childNodes[0]);
          }
          this.element.appendChild(this._viewElement);
        } else {
          this._viewElement = this.element.querySelector('.' + CLASSNAMES.view);
        }
        addClass(this.element, [CLASSNAMES.element]);
        addClass(this._viewElement, [CLASSNAMES.view]);
        this._createResizeTrigger();
      }
		
      return this;
    }

    if (this._created === true) {
      console.warn('calling on a already-created object');
      return this;
    }

    if (this.autoshow) {
      addClass(this.element, [CLASSNAMES.autoshow]);
    }

    this._document = document;

    if (this.createElements === true) {
      this._viewElement = document.createElement('div');
      this._scrollbarVerticalElement = document.createElement('div');
      this._thumbVerticalElement = document.createElement('div');
      this._scrollbarHorizontalElement = document.createElement('div');
      this._thumbHorizontalElement = document.createElement('div');
      while(this.element.childNodes.length > 0) {
        this._viewElement.appendChild(this.element.childNodes[0]);
      }

      this._scrollbarVerticalElement.appendChild(this._thumbVerticalElement);
      this._scrollbarHorizontalElement.appendChild(this._thumbHorizontalElement);
      this.element.appendChild(this._scrollbarVerticalElement);
      this.element.appendChild(this._scrollbarHorizontalElement);
      this.element.appendChild(this._viewElement);
    } else {
      this._viewElement = this.element.querySelector('.' + CLASSNAMES.view);
      this._scrollbarVerticalElement = this.element.querySelector('.' + CLASSNAMES.verticalScrollbar.split(' ').join('.'));
      this._thumbVerticalElement = this._scrollbarVerticalElement.querySelector('.' + CLASSNAMES.thumb);
      this._scrollbarHorizontalElement = this.element.querySelector('.' + CLASSNAMES.horizontalScrollbar.split(' ').join('.'));
      this._thumbHorizontalElement = this._scrollbarHorizontalElement.querySelector('.' + CLASSNAMES.thumb);
    }

    addClass(this.element, [CLASSNAMES.element]);
    addClass(this._viewElement, [CLASSNAMES.view]);
    addClass(this._scrollbarVerticalElement, CLASSNAMES.verticalScrollbar.split(/\s/));
    addClass(this._scrollbarHorizontalElement, CLASSNAMES.horizontalScrollbar.split(/\s/));
    addClass(this._thumbVerticalElement, [CLASSNAMES.thumb]);
    addClass(this._thumbHorizontalElement, [CLASSNAMES.thumb]);

    this._scrollbarVerticalElement.style.display = '';
    this._scrollbarHorizontalElement.style.display = '';

    this._createResizeTrigger();

    this._created = true;

    return this._bindEvents().update();
  };

  GeminiScrollbar.prototype._createResizeTrigger = function createResizeTrigger() {
    var obj = document.createElement('object');
    addClass(obj, [CLASSNAMES.resizeTrigger]);
    obj.type = 'text/html';
    obj.setAttribute('tabindex', '-1');
    var resizeHandler = this._resizeHandler.bind(this);
    obj.onload = function () {
      var win = obj.contentDocument.defaultView;
      win.addEventListener('resize', resizeHandler);
    };

    //IE: Does not like that this happens before, even if it is also added after.
    if (!isIE()) {
      obj.data = 'about:blank';
    }

    this.element.appendChild(obj);

    //IE: This must occur after adding the object to the DOM.
    if (isIE()) {
      obj.data = 'about:blank';
    }

    this._resizeTriggerElement = obj;
  };

  GeminiScrollbar.prototype.update = function update() {
    if (DONT_CREATE_GEMINI) {
      return this;
    }

    if (this._created === false) {
      console.warn('calling on a not-yet-created object');
      return this;
    }

    this._viewElement.style.width = ((this.element.offsetWidth + SCROLLBAR_WIDTH).toString() + 'px');
    this._viewElement.style.height = ((this.element.offsetHeight + SCROLLBAR_WIDTH).toString() + 'px');

    this._naturalThumbSizeX = this._scrollbarHorizontalElement.clientWidth / this._viewElement.scrollWidth * this._scrollbarHorizontalElement.clientWidth;
    this._naturalThumbSizeY = this._scrollbarVerticalElement.clientHeight / this._viewElement.scrollHeight * this._scrollbarVerticalElement.clientHeight;

    this._scrollTopMax = this._viewElement.scrollHeight - this._viewElement.clientHeight;
    this._scrollLeftMax = this._viewElement.scrollWidth - this._viewElement.clientWidth;

    if (this._naturalThumbSizeY < this.minThumbSize) {
      this._thumbVerticalElement.style.height = this.minThumbSize + 'px';
    } else if (this._scrollTopMax) {
      this._thumbVerticalElement.style.height = this._naturalThumbSizeY + 'px';
    } else {
      this._thumbVerticalElement.style.height = '0px';
    }

    if (this._naturalThumbSizeX < this.minThumbSize) {
      this._thumbHorizontalElement.style.width = this.minThumbSize + 'px';
    } else if (this._scrollLeftMax) {
      this._thumbHorizontalElement.style.width = this._naturalThumbSizeX + 'px';
    } else {
      this._thumbHorizontalElement.style.width = '0px';
    }

    this._thumbSizeY = this._thumbVerticalElement.clientHeight;
    this._thumbSizeX = this._thumbHorizontalElement.clientWidth;

    this._trackTopMax = this._scrollbarVerticalElement.clientHeight - this._thumbSizeY;
    this._trackLeftMax = this._scrollbarHorizontalElement.clientWidth - this._thumbSizeX;

    this._scrollHandler();

    return this;
  };

  GeminiScrollbar.prototype.destroy = function destroy() {
    if (this._resizeTriggerElement) {
      this.element.removeChild(this._resizeTriggerElement);
      this._resizeTriggerElement = null;
    }

    if (DONT_CREATE_GEMINI) {
      return this;
    }

    if (this._created === false) {
      console.warn('calling on a not-yet-created object');
      return this;
    }

    this._unbinEvents();

    removeClass(this.element, [CLASSNAMES.element, CLASSNAMES.autoshow]);

    if (this.createElements === true) {
      this.element.removeChild(this._scrollbarVerticalElement);
      this.element.removeChild(this._scrollbarHorizontalElement);
      while(this._viewElement.childNodes.length > 0) {
        this.element.appendChild(this._viewElement.childNodes[0]);
      }
      this.element.removeChild(this._viewElement);
    } else {
      this._viewElement.style.width = '';
      this._viewElement.style.height = '';
      this._scrollbarVerticalElement.style.display = 'none';
      this._scrollbarHorizontalElement.style.display = 'none';
    }

    this._created = false;
    this._document = null;

    return null;
  };

  GeminiScrollbar.prototype.getViewElement = function getViewElement() {
    return this._viewElement;
  };

  GeminiScrollbar.prototype._bindEvents = function _bindEvents() {
    this._cache.events.scrollHandler = this._scrollHandler.bind(this);
    this._cache.events.clickVerticalTrackHandler = this._clickVerticalTrackHandler.bind(this);
    this._cache.events.clickHorizontalTrackHandler = this._clickHorizontalTrackHandler.bind(this);
    this._cache.events.clickVerticalThumbHandler = this._clickVerticalThumbHandler.bind(this);
    this._cache.events.clickHorizontalThumbHandler = this._clickHorizontalThumbHandler.bind(this);
    this._cache.events.mouseUpDocumentHandler = this._mouseUpDocumentHandler.bind(this);
    this._cache.events.mouseMoveDocumentHandler = this._mouseMoveDocumentHandler.bind(this);

    this._viewElement.addEventListener('scroll', this._cache.events.scrollHandler);
    this._scrollbarVerticalElement.addEventListener('mousedown', this._cache.events.clickVerticalTrackHandler);
    this._scrollbarHorizontalElement.addEventListener('mousedown', this._cache.events.clickHorizontalTrackHandler);
    this._thumbVerticalElement.addEventListener('mousedown', this._cache.events.clickVerticalThumbHandler);
    this._thumbHorizontalElement.addEventListener('mousedown', this._cache.events.clickHorizontalThumbHandler);
    this._document.addEventListener('mouseup', this._cache.events.mouseUpDocumentHandler);

    return this;
  };

  GeminiScrollbar.prototype._unbinEvents = function _unbinEvents() {
    this._viewElement.removeEventListener('scroll', this._cache.events.scrollHandler);
    this._scrollbarVerticalElement.removeEventListener('mousedown', this._cache.events.clickVerticalTrackHandler);
    this._scrollbarHorizontalElement.removeEventListener('mousedown', this._cache.events.clickHorizontalTrackHandler);
    this._thumbVerticalElement.removeEventListener('mousedown', this._cache.events.clickVerticalThumbHandler);
    this._thumbHorizontalElement.removeEventListener('mousedown', this._cache.events.clickHorizontalThumbHandler);
    this._document.removeEventListener('mouseup', this._cache.events.mouseUpDocumentHandler);
    this._document.removeEventListener('mousemove', this._cache.events.mouseMoveDocumentHandler);

    return this;
  };

  GeminiScrollbar.prototype._scrollHandler = function _scrollHandler() {
    var x = (this._viewElement.scrollLeft * this._trackLeftMax / this._scrollLeftMax) || 0;
    var y = (this._viewElement.scrollTop * this._trackTopMax / this._scrollTopMax) || 0;

    this._thumbHorizontalElement.style.msTransform = 'translateX(' + x + 'px)';
    this._thumbHorizontalElement.style.webkitTransform = 'translate3d(' + x + 'px, 0, 0)';
    this._thumbHorizontalElement.style.transform = 'translate3d(' + x + 'px, 0, 0)';

    this._thumbVerticalElement.style.msTransform = 'translateY(' + y + 'px)';
    this._thumbVerticalElement.style.webkitTransform = 'translate3d(0, ' + y + 'px, 0)';
    this._thumbVerticalElement.style.transform = 'translate3d(0, ' + y + 'px, 0)';
  };

  GeminiScrollbar.prototype._resizeHandler = function _resizeHandler() {
    this.update();
    if (this.onResize) {
      this.onResize();
    }
  };

  GeminiScrollbar.prototype._clickVerticalTrackHandler = function _clickVerticalTrackHandler(e) {
    if(e.target !== e.currentTarget) {
      return;
    }
    var offset = e.offsetY - this._naturalThumbSizeY * .5
      , thumbPositionPercentage = offset * 100 / this._scrollbarVerticalElement.clientHeight;

    this._viewElement.scrollTop = thumbPositionPercentage * this._viewElement.scrollHeight / 100;
  };

  GeminiScrollbar.prototype._clickHorizontalTrackHandler = function _clickHorizontalTrackHandler(e) {
    if(e.target !== e.currentTarget) {
      return;
    }
    var offset = e.offsetX - this._naturalThumbSizeX * .5
      , thumbPositionPercentage = offset * 100 / this._scrollbarHorizontalElement.clientWidth;

    this._viewElement.scrollLeft = thumbPositionPercentage * this._viewElement.scrollWidth / 100;
  };

  GeminiScrollbar.prototype._clickVerticalThumbHandler = function _clickVerticalThumbHandler(e) {
    this._startDrag(e);
    this._prevPageY = this._thumbSizeY - e.offsetY;
  };

  GeminiScrollbar.prototype._clickHorizontalThumbHandler = function _clickHorizontalThumbHandler(e) {
    this._startDrag(e);
    this._prevPageX = this._thumbSizeX - e.offsetX;
  };

  GeminiScrollbar.prototype._startDrag = function _startDrag(e) {
    this._cursorDown = true;
    addClass(document.body, [CLASSNAMES.disable]);
    this._document.addEventListener('mousemove', this._cache.events.mouseMoveDocumentHandler);
    this._document.onselectstart = function() {return false;};
  };

  GeminiScrollbar.prototype._mouseUpDocumentHandler = function _mouseUpDocumentHandler() {
    this._cursorDown = false;
    this._prevPageX = this._prevPageY = 0;
    removeClass(document.body, [CLASSNAMES.disable]);
    this._document.removeEventListener('mousemove', this._cache.events.mouseMoveDocumentHandler);
    this._document.onselectstart = null;
  };

  GeminiScrollbar.prototype._mouseMoveDocumentHandler = function _mouseMoveDocumentHandler(e) {
    if (this._cursorDown === false) {return;}

    var offset, thumbClickPosition;

    if (this._prevPageY) {
      offset = e.clientY - this._scrollbarVerticalElement.getBoundingClientRect().top;
      thumbClickPosition = this._thumbSizeY - this._prevPageY;

      this._viewElement.scrollTop = this._scrollTopMax * (offset - thumbClickPosition) / this._trackTopMax;

      return void 0;
    }

    if (this._prevPageX) {
      offset = e.clientX - this._scrollbarHorizontalElement.getBoundingClientRect().left;
      thumbClickPosition = this._thumbSizeX - this._prevPageX;

      this._viewElement.scrollLeft = this._scrollLeftMax * (offset - thumbClickPosition) / this._trackLeftMax;
    }
  };

  if (typeof exports === 'object') {
    module.exports = GeminiScrollbar;
  } else {
    window.GeminiScrollbar = GeminiScrollbar;
  }
})();


/* var swiperL; */
/* 
function swiperFuncL(idele,idboth,ele,event){
	event.preventDefault();
	div = document.getElementById(idele);
	both = document.getElementById(idboth);
	var x = event.clientX, y = event.clientY;
	ele.onmousemove = function (ev){
		leftx = ev.screenX;
		that.console(leftx);
		topy = y;
		ele.style.zIndex = 10000;
		ele.style.transform = "translateX("+ev.x+"px)";
		ele.style.transitionDuration = '0s';
	}
	ele.onmouseup  = function (e){
		//backtoasal(ele,e,zindexFirst);
	}
	window.onmouseup= function (e){
		//backtoasal(ele,e,zindexFirst);
	};
	function backtoasal(ele,event,indx) {
		div = ele.parentNode;
		ele.onmousemove = null;
		window.onmouseup = null;
		ele.style.transform = 'translateX(0px)';
		ele.style.transitionDuration = '0.2s';
		ele.style.zIndex = null;
		fxheadModul[0].paint();
	}
} */
var jumper;
function notifBox(content){
	this.clean = function()
	  {
		dBox();
	  };
	if(content){cBox()}
	function cBox(){
		if(document.getElementById('notif')){
			warp = document.getElementById('notif');
			dBox();
		}else{
			var warp = document.createElement("div");
			warp.id = "notif";
			warp.setAttribute('class','panel-notif-box');
			warp.innerHTML = content;
			document.body.appendChild(warp);
		}
	}
	function dBox(){
		if(document.getElementById('notif')){
			document.getElementById('notif').remove();
		}
	}
}
function jumpMainmenu(){
	html = '<div style="margin-top:10px;"><i class="fa fa-fast-forward" aria-hidden="true"></i> Code :<br>';
	html += '<input id="jumpMainmenu" type="search" class="col-12" onkeypress="javascript:ifEnterForJummping(this);"><div>';
	jumper = new notifBox(html);
}
function isKeyPressed(event){
    if (event.ctrlKey){
        if(event.keyCode == 10 || event.code == "KeyM"){
			if(document.getElementById('jumpMainmenu') == null){
				jumpMainmenu();
				if(document.getElementById('jumpMainmenu')){
					var idjumpMainmenu = document.getElementById('jumpMainmenu');
					idjumpMainmenu.focus();
				}
			}
		}
    }
}
function ifEnterForJummping(e,jumper="jumper"){
	if(event.code == 'Enter' || jumper == 'refresh'){
		if((typeof e.value != 'undefined ' && e.value !== "") || (typeof e.page != 'undefined ' && e.page !== "")){
			var val = e.page || e.value;
			//get on the List Menu
			var slave = 'load_childmenu.php?'+jumper+'='+val;
			$.getpage($.panel,slave,function(result){
				var attr = result.response;
				if(typeof attr.class !== 'undefined'){
					checkPriviledge(attr.id,function(priv){
						if(jumper  == 'refresh'){
							var search = new Array();
							if(window.history.state !== null){
								search = window.history.state.search.split(window.history.state.page);
							}
							
							if(search.length > 1){
								attr.action = attr.action+search[1];
							}
						}
						do_load(attr.action,attr.id,this.event,attr.class,priv);
					});
					activeParent(attr.id,event);
					result.body.innerHTML = result.response;
					$.scanningElement(result.body);
				}
			});
			
		}
	}
}