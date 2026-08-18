 (function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
	var dataGridEx = require('./data-grid-ex.js');
	(function(window, document){
		// AMD
		if (typeof define === 'function' && define.amd) {
			define('data-grid-ex', function () {
		  return dataGridEx;
		});
		// CMD
		}else if (typeof module !== 'undefined' && module.exports) {
			module.exports = dataGridEx;
			// Browser
			// Keep exporting globally as module.exports is available because of browserify
			window.dataGridEx = dataGridEx;
		}
	})(window, document);
},{"./data-grid-ex.js":3}],2:[function(require,module,exports){	
	module.exports = {
		/**
		* Extends an object
		*
		* @param  {Object} target object to extend
		* @param  {Object} source object to take properties from
		* @return {Object}        extended object
		*/
		extend: function(target, source) {
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
		/**
		* Checks if an object is an Object
		*
		* @param  {Object}  o Object
		* @return {Boolean}   returns true if object is an Object
		*/
		, isObject: function(o){
			return Object.prototype.toString.call(o) === '[object Object]';
		}
		/**
		* Checks if an object is a DOM element
		*
		* @param  {Object}  o HTML element or String
		* @return {Boolean}   returns true if object is a DOM element
		*/
		,isElement: function(o){
			return (
			  o instanceof HTMLElement || //DOM2
			  (o && typeof o === 'object' && o !== null && o.nodeType === 1 && typeof o.nodeName === 'string')
			);
		}
		,getElement : function(table,elementOrSelector) {
			var element;
			if (!this.isElement(elementOrSelector)) {
				// If selector provided
				if (typeof elementOrSelector === 'string' || elementOrSelector instanceof String) {
					t = this.getTable(table);
					tag = t.getElementsByTagName(elementOrSelector);
					if(tag.length > 0){
						element = tag;
					}else{
						throw new Error('Provided selector did not find any elements. Selector: ' + elementOrSelector)
						 return null
					}
				}else{
					throw new Error('Provided selector is not an HTML object nor String')
					return null
				}
			}
			return element;
		}
		,getThead : function(table) {
			var element,thead;
			element = this.getElement(table,'thead');
			if(element.length > 0){
				thead = tag[0];
			}else{
				throw new Error('Provided selector did not find any elements. Selector: ' + elementOrSelector)
				return null
			}
			return thead;
		}
		,isNumber : function(dateTxt) {
			var result = true;
			if(isNaN(dateTxt)){
				result = false;
			}
			return result;
		}
		,lpad : function(len, c,string){
			var s= '', c= c || '0', len= (len || 2)-string.length;
			while(s.length<len) s+= c;
			return s+string;
		}
		,dateFormat : function(formatTxt,strDate) {
			var format = formatTxt.trim();
			var formatExiest = strDate.format;
			var newDate = strDate.date;
			var reformat = new Array();
			var spliter = /[\.\-\/]/;
			var params = newDate.split(spliter);
			var of = formatExiest.toLowerCase().split(spliter);
			var newFormat = format.toLowerCase().split(spliter);
			var sparator = format.toLowerCase().replace(/[a-z]/g, "");
			var y,m,d;
			for(i=0;i<params.length; i++){
				if(typeof of[i] !== 'undefined'){
					switch(of[i]){
						case 'y':
							y = params[i];
						break;
						case 'm':
							m = this.lpad(2,0,params[i]);
						break;
						case 'd':
							d = this.lpad(2,0,params[i]);
						break;
					}
				}
			}
			for(i=0;i<newFormat.length; i++){
				switch(newFormat[i]){
					case 'y':
						reformat.push(y);
					break;
					case 'm':
						reformat.push(m);
					break;
					case 'd':
						reformat.push(d);
					break;
				}
			}
			console.log(sparator.length);
			if(sparator.length>0){
				newDate = reformat.join(sparator[0]);
			}
			
			return newDate;
		}
		,isDate : function(dateTxt) {
			var result;
			//var dateRegexp = /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$/;
			//var dateRegexp = /^(0?[1-9]|[12][0-9]|3[01])[\.\-\/](0?[1-9]|1[012])[\.\-\/]\d{4}$/;
			//var result = dateTxt.match(dateRegexp);
			var spliter = /[\.\-\/]/;
			var params = dateTxt.split(spliter);
			if(params.length >= 3 ){
				lenD = 0;
				x = -1;
				for(o=0; o<params.length; o++){
					if(params[o].length > lenD){
						x=o;
						lenD = params[o].length;
					}
				}
				if(x == 2){
					var dateRegexp = /^(0?[1-9]|[12][0-9]|3[01])[\.\-\/](0?[1-9]|1[012])[\.\-\/]\d{4}$/;//d/m/Y
					var format = "d/m/Y";
				}else if(x == 0){
					var dateRegexp = /^\d{4}[\.\-\/](0?[1-9]|1[012])[\.\-\/](0?[1-9]|[12][0-9]|3[01])$/;//Y/m/d
					var format = "Y/m/d";
				}
				mached = dateTxt.match(dateRegexp);
				if(mached !== null){
					result = {date : dateTxt,format : format,validation:mached};
				}else{
					result = mached;
				}
			}
			return result;
		}
		,isTime : function(dateTxt){
			var d = new Date();
			var H = d.getHours();
			var i = d.getMinutes();
			var s = d.getSeconds();
		 
			result = H.lpad(2,"0")+":"+i.lpad(2,"0")+":"+s.lpad(2,"0");
			return result;
		}
		,getTbody : function(table) {
			var element,tbody;
			element = this.getElement(table,'tbody');
			if(element.length > 0){
				tbody = tag[0];
			}else{
				throw new Error('Provided selector did not find any elements. Selector: ' + elementOrSelector)
				return null
			}
			return tbody;
		}
		,getTable: function(elementOrSelector) {
			var element, table;
			if (!this.isElement(elementOrSelector)) {
			  // If selector provided
			  if (typeof elementOrSelector === 'string' || elementOrSelector instanceof String) {
				// Try to find the element
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

			if (element.tagName.toLowerCase() === 'table') {
				table = element;
			} else {
				throw new Error('Cannot get Table.');
			}

			return table;
		 }
	}
},{}],3:[function(require,module,exports){	
	var Utils = require('./utilities');
	var optionsDefaults = {
		addcolumn: false, // true, add new any row and andy colomn
		maxaddcolumn: 0, // 
		addrow: false, // true, add new any row and andy colomn
		maxaddrow: 0, // 
		setup : {
			datacolomn : {},
			typecolomn : {},
			colomn : {},
			row : 0,
			dateformat: "Y-m-d"
		}
	}
	var DataGridEx = function(table, options) {
	  this.init(table, options)
	}
	DataGridEx.prototype.init = function(table, options) {
		var that = this
		this.table = table
		this.result = [];
		var publicInstance = this.getPublicInstance()
		this.options = Utils.extend(Utils.extend({}, optionsDefaults), options)
		this.loadGrid(this.table)
	}
	DataGridEx.prototype.setupHandlers = function(e) {
		var that = this, prevEvt = null;
		e.onpaste = function(evt){
			return that.readPasteExcel(evt,e);
		}
	}
	DataGridEx.prototype.getColom = function(x) {
		var result = null;
		var t = this.table;
		tbody = Utils.getTbody(t);
		td = tbody.getElementsByTagName("td");
		if(typeof td[x] !== 'undefined'){
			ele = td[x];
			result = this.getData(null,x);
		}
		return result
	}
	DataGridEx.prototype.rowSuccess = function(y) {
		if (!Utils.isElement(y)){
			var t = this.table;
			tbody = Utils.getTbody(t);
			tr = tbody.getElementsByTagName("tr");
			if(typeof tr[y] !== 'undefined'){
				ele = tr[y];
				ele.classList.remove("warning");
				ele.classList.remove("error");
				ele.classList.add("success");
			}
		}else{
			ele = y;
			ele.classList.remove("success");
			ele.classList.remove("warning");
			ele.classList.add("error");
		}
	}
	DataGridEx.prototype.rowWarning = function(y) {
		if (!Utils.isElement(y)){
			var t = this.table;
			tbody = Utils.getTbody(t);
			tr = tbody.getElementsByTagName("tr");
			if(typeof tr[y] !== 'undefined'){
				ele = tr[y];
				ele.classList.remove("success");
				ele.classList.remove("error");
				ele.classList.add("warning");
			}
		}else{
			ele = y;
			ele.classList.remove("success");
			ele.classList.remove("warning");
			ele.classList.add("error");
		}
	}
	DataGridEx.prototype.rowError = function(y) {
		if (!Utils.isElement(y)){
			var t = this.table;
			tbody = Utils.getTbody(t);
			tr = tbody.getElementsByTagName("tr");
			if(typeof tr[y] !== 'undefined'){
				ele = tr[y];
				ele.classList.remove("success");
				ele.classList.remove("warning");
				ele.classList.add("error");
			}
		}else{
			ele = y;
			ele.classList.remove("success");
			ele.classList.remove("warning");
			ele.classList.add("error");
		}
	}
	DataGridEx.prototype.getRowError = function() {
		var result = [];
		var t = this.table;
		tbody = Utils.getTbody(t);
		tr = tbody.getElementsByClassName("error");
		yArr = new Array();
		if(tr.length > 0){
			for(i=0; i<tr.length; i++){
				if(typeof tr[i] !== 'undefined'){
					y = (tr[i].rowIndex-1);
					yArr.push(y);
				}
			}
			result = this.getData(yArr);
		}
		return result
	}
	DataGridEx.prototype.getRowSuccess = function() {
		var result = [];
		var t = this.table;
		tbody = Utils.getTbody(t);
		tr = tbody.getElementsByClassName("success");
		yArr = new Array();
		if(tr.length > 0){
			for(i=0; i<tr.length; i++){
				if(typeof tr[i] !== 'undefined'){
					y = (tr[i].rowIndex-1);
					yArr.push(y);
				}
			}
			result = this.getData(yArr);
		}
		return result
	}
	DataGridEx.prototype.getRowWarning = function() {
		var result = [];
		var t = this.table;
		tbody = Utils.getTbody(t);
		tr = tbody.getElementsByClassName("warning");
		yArr = new Array();
		if(tr.length > 0){
			for(i=0; i<tr.length; i++){
				if(typeof tr[i] !== 'undefined'){
					y = (tr[i].rowIndex-1);
					yArr.push(y);
				}
			}
			result = this.getData(yArr);
		}
		return result
	}
	DataGridEx.prototype.getRow = function(y) {
		var result = null;
		var t = this.table;
		tbody = Utils.getTbody(t);
		tr = tbody.getElementsByTagName("tr");
		if(typeof tr[y] !== 'undefined'){
			result = this.getData(y);
		}
		return result
	}
	DataGridEx.prototype.normalizeInput = function(tr) {
		_input = tr.getElementsByTagName("input");
		select = tr.getElementsByTagName("select");
		if(_input.length > 0){
			input = _input;
		}else{
			input = select;
		}
		for(n=0; n<input.length; n++){
			if(input[n]){
				this.publicInstance.setupHandlers(input[n]);
			}
		}		
	}
	DataGridEx.prototype.getData = function(y,x){
		var t = this.table;
		var row = null;
		var col = null;
		if(typeof y !== 'undefined'){
			row = y;
		}
		if(typeof x !== 'undefined'){
			col = x;
		}
		var colomn = Object.keys(this.options.setup.colomn);
		var result = new Array();
		tbody = Utils.getTbody(t);
		var tr = tbody.getElementsByTagName("tr");
		var numRow = 0;
		var jmlRow = tr.length;
		if(row != null){
			if(Array.isArray(row)){
				numRow = 0;
				jmlRow = row.length;
			}else{
				numRow = row;
				jmlRow = (row+1);
			}
			
		}
		if(tr.length > 0){
			for(ix=numRow; ix<jmlRow; ix++){
				r = ix;
				if(row != null){
					if(Array.isArray(row)){
						r = row[ix];
					}
				}
				obj = {};
				if(typeof tr[r] !== 'undefined'){
					td = tr[r].getElementsByTagName("td");
					var numCol= 0;
					var jmlCol = td.length;
					if(col != null){
						numCol = col;
						jmlCol = (col+1);
					}
					if(jmlCol > 0){
						for(i=numCol; i<jmlCol; i++){
							if(typeof td[i] !== 'undefined'){
								_input = td[i].getElementsByTagName("input");
								select = td[i].getElementsByTagName("select");
								if(_input.length > 0){
									input = _input;
								}else{
									input = select;
								}
								text = input[0].value;
								obj[colomn[i]] = text;
							}
						}
					}
				}
				result.push(obj);
			}
		}
		
		return result;
	}
	DataGridEx.prototype.normalizeTd = function(t,x,y,l,jmlCol) {
		thead = Utils.getThead(t);
		trh = thead.getElementsByTagName("tr");
		var optNewCol = this.options.addcolumn;
		var optNewRow = this.options.addrow;
		if(optNewCol == true){
			if(trh.length > 0){
				trhead = trh[0];
				th = trhead.getElementsByTagName("th");
				if((jmlCol+x)>th.length){
					var lastTh = trh[0].lastChild;
					sTh = ((jmlCol+x)-th.length);
					ele = new Array();
					for(ih=th.length; ih<(th.length+sTh); ih++){
						newTh = lastTh.cloneNode(true);
						newTh.innerHTML = "Row "+ih;
						newTh.setAttribute("name","Row_"+ih);
						ele.push(newTh);
					}
					if(ele.length >0){
						for(i=0; i<ele.length; i++){
							trhead.appendChild(ele[i]);
						}
					}
				}
			}
		
			tbody = Utils.getTbody(t);
			tr = tbody.getElementsByTagName("tr");
			jmlRow = ((l.length+y)-1);
			for(ix=0; ix<tr.length; ix++){
				if(ix < y || ix > jmlRow){
					td = tr[ix].getElementsByTagName("td");
					if((jmlCol+x)>td.length){
						sTd = ((jmlCol+x)-td.length);
						for(i=0; i<sTd; i++){
							var lastTd = tr[ix].lastChild;
							var newTd = lastTd.cloneNode(true);
							_input = newTd.getElementsByTagName("input");
							select = newTd.getElementsByTagName("select");
							if(_input.length > 0){
								input = _input;
							}else{
								input = select;
							}
							if(input[0]){
								input[0].value = "";
								this.setupHandlers(input[0]);
							}
							tr[ix].appendChild(newTd);
						}
					}
				}
			}
		}
	}
	
	DataGridEx.prototype.validation = function(val,typecolomn){
		var result = val;
		var format = this.options.setup.dateformat;
		var type = typecolomn.toLowerCase();
		switch(type){
			case'number':
				if(Utils.isNumber(val) == true){
					result = val;
				}else{
					result = false;
				}
			break;
			case'date':
				if(Utils.isDate(val) != null){
					date = Utils.isDate(val);
					result = Utils.dateFormat(format,Utils.isDate(val),date);
				}else{
					result = false;
				}
				
			break;
		}	
		return result;
	}
	DataGridEx.prototype.readPasteExcel = function(evt,e){
		var optNewCol = this.options.addcolumn;
		var optNewRow = this.options.addrow;
		var maxaddrow = this.options.maxaddrow;
		var maxaddcolumn = this.options.maxaddcolumn;
		var that = e;
		var table = that.closest('table');
		var tbody = Utils.getTbody(table);
		var tr = tbody.querySelectorAll("tr");
		var items = evt.clipboardData.items;
		var publicInstance = this.publicInstance;
		
		for(i=0; i<items.length; i++){
			v = items[i];
			if(v.type === 'text/plain'){
				v.getAsString(function(text){
					
					var x = that.closest('td').cellIndex,
						y = (that.closest('tr').rowIndex-1)
						
					text = text.trim('\r\n');
					listtext = text.split('\r\n');
					if(listtext.length > 0){
						var l = listtext,t = table;
						var indexX = x;
						var jmlCol = (l[0].split('\t').length);
						for(ix=0; ix<l.length; ix++){
							v2 = l[ix];
							rw = v2.split('\t');
							var row = y+ix;
							if(typeof tr[row] === "undefined"){
								if(optNewRow == false){
									break;
								}
								if(row > maxaddrow){
									break;
								}
								itm = tr[0];
								tbody = itm.parentNode;
								newTR = itm.cloneNode(true);
								newTR.removeAttribute("class");
								trTable = newTR;
							}else{
								trTable = tr[row];
							}
							
							publicInstance.normalizeInput(trTable);
							var anyFalse = "true";
							for(iox=0; iox<jmlCol; iox++){
								var col = x+iox;
								if(trTable){
									td = trTable.getElementsByTagName("td");
									if(td[col]){
										_input = td[col].getElementsByTagName("input");
										select = td[col].getElementsByTagName("select");
										if(_input.length > 0){
											input = _input;
										}else{
											input = select;
										}
										if(input[0]){
											txt = "";
											if(typeof rw[iox] !== 'undefined'){
												txt = rw[iox];
											}
											valTXT = txt;
											if(typeof dataTypecolomn[colomn[col]] !== 'undefined'){
												valTXT = publicInstance.validation(txt,dataTypecolomn[colomn[col]]);
											}
											input[0].value = valTXT;
											if(valTXT != false){
												input[0].setAttribute("validation","true");
												input[0].classList.remove("error");
												td[col].classList.remove("error");
											}else{
												input[0].setAttribute("validation","false");
												input[0].classList.add("error");
												td[col].classList.add("error");
												anyFalse = "false";
											}
											publicInstance.setupHandlers(input[0]);
											if(dataTypecolomn[colomn[col]] == 'date'){
												attFormat = '%Y-%m-%d';
												input[0].addEventListener('click', datepicker(input[0],attFormat));
											}
										}
									}else{
										if(optNewCol == false){
											break;
										}
										var lastTd = trTable.lastChild;
										var newTd = lastTd.cloneNode(true);
										_input = newTd.getElementsByTagName("input");
										select = newTd.getElementsByTagName("select");
										if(_input.length > 0){
											input = _input;
										}else{
											input = select;
										}
										if(input[0]){
											txt = "";
											if(typeof rw[iox] !== 'undefined'){
												txt = rw[iox];
											}
											valTXT = txt;
											if(typeof dataTypecolomn[colomn[col]] !== 'undefined'){
												valTXT = publicInstance.validation(txt,dataTypecolomn[colomn[col]]);
											}
											input[0].value = valTXT;
											if(valTXT != false){
												input[0].setAttribute("validation","true");
												input[0].classList.remove("error");
												newTd.classList.remove("error");
											}else{
												input[0].setAttribute("validation","false");
												input[0].classList.add("error");
												newTd.classList.add("error");
												anyFalse = "false";
											}
											publicInstance.setupHandlers(input[0]);
											if(dataTypecolomn[colomn[col]] == 'date'){
												attFormat = '%Y-%m-%d';
												input[0].addEventListener('click', datepicker(input[0],attFormat));
											}
										}
										trTable.appendChild(newTd);
									}
									
									
								}
							}
							//if any false 
							if(anyFalse == "false"){
								publicInstance.rowError(trTable);
							}
							if(typeof tr[row] === "undefined"){
								tbody.appendChild(trTable);
							}
						}
						publicInstance.normalizeTd(t,x,y,l,jmlCol);
					}
				});
			}
		}
		return false;
		
	}
	DataGridEx.prototype.correction = function(){
		var validate;
		table = this.table;
		dataValuecolomn = this.options.setup.datacolomn;
		dataTypecolomn = this.options.setup.typecolomn;
		dataColomn = this.options.setup.colomn;
		colomn = Object.keys(dataColomn);
		tbody = table.getElementsByTagName("tbody");
		if(tbody.length > 0){
			Tbdy = tbody[0];
			tr = Tbdy.getElementsByTagName("tr");
			
			for(var i=0; i<tr.length; i++){
				td = tr[i].getElementsByTagName("td");
				anyFalse = "";
				for(var ix=0; ix<td.length; ix++){
					_input = td[ix].getElementsByTagName("input");
					select = td[ix].getElementsByTagName("select");
					if(_input.length > 0){
						input = _input;
					}else{
						input = select;
					}
					txt = input[0].value;
					x = td[ix].cellIndex;
					console.log(txt,dataTypecolomn[colomn[x]]);
					validate = this.validation(txt,dataTypecolomn[colomn[x]]);
					if(validate != false){
						console.log(validate);
						input[0].setAttribute("validation","true");
						input[0].classList.remove("error");
						td[ix].classList.remove("error");
					}else{
						input[0].setAttribute("validation","false");
						input[0].classList.add("error");
						td[ix].classList.add("error");
						anyFalse = "false";
					}
				}
				if(anyFalse == "false"){
					this.rowError(tr[i]);
				}else{
					tr[i].classList.remove("error")
				}
			}
		}
	}
	DataGridEx.prototype.reset = function(){
		//reset function
		this.loadGrid(this.table);
	}
	DataGridEx.prototype.destroy = function() {
		  var that = this

		  // Reset
		  this.reset()

		  // Remove instance from instancesStore
		  instancesStore = instancesStore.filter(function(instance){
			return instance.table !== that.table
		  })

		  // Delete options and its contents
		  delete this.options

		  // Destroy public instance and rewrite getPublicInstance
		  delete this.publicInstance
		  delete this.pi
		  this.getPublicInstance = function(){
			return null
		  }
	}
	DataGridEx.prototype.loadGrid = function(table) {
		table.innerHTML = "";
		table.classList.add("table_data_ex");
		//var colomn = new Array();
		dataValuecolomn = this.options.setup.datacolomn;
		dataTypecolomn = this.options.setup.typecolomn;
		dataColomn = this.options.setup.colomn;
		colomn = Object.keys(dataColomn);
		row = this.options.setup.row;
		thead = document.createElement("thead");
		trh = document.createElement("tr");
		for(let ix=0; ix<colomn.length; ix++){
			th = document.createElement("th");
			th.setAttribute("name",colomn[ix]);
			th.setAttribute("type-validation",dataTypecolomn[colomn[ix]]);
			th.innerHTML = dataColomn[colomn[ix]];
			trh.appendChild(th);
		}
		thead.appendChild(trh);
		table.appendChild(thead);
		
		tbody = document.createElement("tbody");
		for(let i=0; i<row; i++){
			tr = document.createElement("tr");
			for(let ix=0; ix<colomn.length; ix++){
				td = document.createElement("td");
				console.log(dataTypecolomn[colomn[ix]],colomn[ix]);
				if(dataTypecolomn[colomn[ix]].toLowerCase() == 'select'){
					input = document.createElement("select");//<select class="data-ex"/>
					opt = dataValuecolomn[colomn[ix]];
					var newOpt = "";
					if(Object.keys(opt).length > 0){
						for (var key in opt) {
							newOpt +='<option value="'+key+'">'+opt[key]+'</option>';
						}
						input.innerHTML = newOpt;
					}
				}else{
					input = document.createElement("input");//<input class="data-ex"/>
					if(dataTypecolomn[colomn[ix]] == 'date'){
						attFormat = '%Y-%m-%d';
						input.setAttribute('format',attFormat);
						input.setAttribute('placeholder','YYYY-MM-DD');
						input.addEventListener('click', datepicker(input,attFormat));
					}
				}
				
				input.classList.add("data-ex");
				this.setupHandlers(input);
				td.appendChild(input);
				tr.appendChild(td);
			}
			tbody.appendChild(tr);
		}
		table.appendChild(tbody);
	   
	}
	DataGridEx.prototype.getPublicInstance = function() {
	   var that = this
		// Create cache
		if (!this.publicInstance) {
		   this.publicInstance = this.pi = {
			  getTable: function(){return that.table;}
			  ,normalizeInput: function(tr){that.normalizeInput(tr); return that.pi}
			  ,normalizeTd: function(t,x,y,l,jmlCol){that.normalizeTd(t,x,y,l,jmlCol); return that.pi}
			  ,setupHandlers: function(e){that.setupHandlers(e); return that.pi}
			  ,validation: function(v,type){return that.validation(v,type);}
			  ,reset: function(){that.reset(); return that.pi}
			  ,destroy: function(){that.destroy(); return that.pi}
			  ,getData: function(y,x){return that.getData(y,x);}
			  ,getRow: function(y){return that.getRow(y);}
			  ,getColom: function(x){return that.getColom(x);}
			  ,getOption: function() {return that.options;}
			  ,rowSuccess : function(y) {return that.rowSuccess(y);}
			  ,rowWarning : function(y) {return that.rowWarning(y);}
			  ,rowError : function(y) {return that.rowError(y);}
			  ,getRowError : function() {return that.getRowError();}
			  ,correction : function() {return that.correction();}
			  ,getRowWarning : function() {return that.getRowWarning();}
			  ,getRowSuccess : function() {return that.getRowSuccess();}
		   }
		}
	   return this.publicInstance
	}
	var instancesStore = []
	var dataGridEx = function(elementOrSelector, options) {
		var table = Utils.getTable(elementOrSelector);
		if (table === null) {
			return null
		}else {
		    for(var i = instancesStore.length - 1; i >= 0; i--) {
			  if (instancesStore[i].table === table) {
				return instancesStore[i].instance.getPublicInstance()
			  }
			}
			 instancesStore.push({
			  table: table, instance: new DataGridEx(table, options)
			})
			// Return just pushed instance
			return instancesStore[instancesStore.length - 1].instance.getPublicInstance()
		}
		
		
	}
	module.exports = dataGridEx;
},{"./utilities":2}]},{},[1]);
	
	
