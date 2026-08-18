/*  Copyright Mihai Bazon, 2002-2005  |  www.bazon.net/mishoo
 * -----------------------------------------------------------
 *
 * The DHTML Calendar, version 1.0 "It is happening again"
 *
 * Details and latest version at:
 * www.dynarch.com/projects/calendar
 *
 * This script is developed by Dynarch.com.  Visit us at www.dynarch.com.
 *
 * This script is distributed under the GNU Lesser General Public License.
 * Read the entire license text here: http://www.gnu.org/licenses/lgpl.html
 */

// $Id$

/** The Calendar object constructor. */
Calendar = function (firstDayOfWeek, dateStr, onSelected, onClose) {
	// member variables
	this.activeDiv = null;
	this.currentDateEl = null;
	this.getDateStatus = null;
	this.getDateToolTip = null;
	this.getDateText = null;
	this.timeout = null;
	this.onSelected = onSelected || null;

	this.onClose = onClose || null;
	this.dragging = false;
	this.hidden = false;
	this.minYear = 1970;
	this.maxYear = 2050;
	this.dateFormat = Calendar._TT["DEF_DATE_FORMAT"];
	this.ttDateFormat = Calendar._TT["TT_DATE_FORMAT"];
	this.isPopup = true;
	this.weekNumbers = true;
	this.firstDayOfWeek = typeof firstDayOfWeek == "number" ? firstDayOfWeek : Calendar._FD; // 0 for Sunday, 1 for Monday, etc.
	this.showsOtherMonths = false;
	this.dateStr = dateStr;
	this.ar_days = null;
	this.showsDate = false;
	this.showsTime = false;
	this.time24 = true;
	this.yearStep = 2;
	this.hiliteToday = true;
	this.multiple = null;
	// HTML elements
	this.table = null;
	this.element = null;
	this.tbody = null;
	this.firstdayname = null;
	// Combo boxes
	this.monthsCombo = null;
	this.yearsCombo = null;
	this.hilitedMonth = null;
	this.activeMonth = null;
	this.hilitedYear = null;
	this.activeYear = null;
	// Information
	this.dateClicked = false;
	this.hoursView = null;
	this.minutesView = null;
	this.currentView = null;
	this.spanHours;
	this.spanMinutes;
	this.contentClockBlock;
	this.dateBlock;
	this.clockBlock;
	this.signTag;
	this.AP = null;
	
	// one-time initializations
	if (typeof Calendar._SDN == "undefined") {
		// table of short day names
		if (typeof Calendar._SDN_len == "undefined")
			Calendar._SDN_len = 3;
		var ar = new Array();
		for (var i = 8; i > 0;) {
			ar[--i] = Calendar._DN[i].substr(0, Calendar._SDN_len);
		}
		Calendar._SDN = ar;
		// table of short month names
		if (typeof Calendar._SMN_len == "undefined")
			Calendar._SMN_len = 3;
		ar = new Array();
		for (var i = 12; i > 0;) {
			ar[--i] = Calendar._MN[i].substr(0, Calendar._SMN_len);
		}
		Calendar._SMN = ar;
	}
};

// ** constants

/// "static", needed for event handlers.
Calendar._C = null;

/// detect a special case of "web browser"
Calendar.is_ie = ( /msie/i.test(navigator.userAgent) &&
		   !/opera/i.test(navigator.userAgent) );

Calendar.is_ie5 = ( Calendar.is_ie && /msie 5\.0/i.test(navigator.userAgent) );

/// detect Opera browser
Calendar.is_opera = /opera/i.test(navigator.userAgent);

/// detect KHTML-based browsers
Calendar.is_khtml = /Konqueror|Safari|KHTML/i.test(navigator.userAgent);

// BEGIN: UTILITY FUNCTIONS; beware that these might be moved into a separate
//        library, at some point.
function leadingZero(num) {
	return (num < 10 ? '0' : '') + num;
}
// harus di perbaikan 
Calendar.Def = function() {
	for(var ix03_=0,a=arguments.length;ix03_<a;++ix03_){
		if(typeof(arguments[ix03_])==="undefined"){
			return false
		}
	}
	return true
}
Calendar.Num = function() {
	for(var ix04_=0,a=arguments.length;ix04_<a;++ix04_){
		if(isNaN(arguments[ix04_])||typeof(arguments[ix04_])!=="number"){
			return false
		}
	}
	return true
}
Calendar.PageY = function(a) {
	var b=0;
	while(a){
		if(Calendar.Def(a.offsetTop)){
			b+=a.offsetTop;
		}
		a=Calendar.Def(a.offsetParent)?a.offsetParent:null
		if(a !== null && (a.classList.contains('masterpanel') || a.classList.contains('panel'))){
			break;
		}
	}
	
	return b
}
Calendar.PageX = function(b) {
	var a=0;
	while(b){
		if(Calendar.Def(b.offsetLeft)){
			a+=b.offsetLeft
		} 
		b=Calendar.Def(b.offsetParent)?b.offsetParent:null
		if(b !== null && (b.classList.contains('masterpanel') || b.classList.contains('panel'))){
			break;
		}
	}
	return a
}
Calendar.isElement = function(obj) {
  try {
    //Using W3 DOM2 (works for FF, Opera and Chrome)
    return obj instanceof HTMLElement;
  }
  catch(e){
    //Browsers not supporting W3 DOM2 don't have HTMLElement and
    //an exception is thrown and we end up here. Testing some
    //properties that all elements have (works on IE7)
    return (typeof obj==="object") &&
      (obj.nodeType===1) && (typeof obj.style === "object") &&
      (typeof obj.ownerDocument ==="object");
  }
}
Calendar.ScrollTop = function(c) {
	var a,d=0;
	while(c){
		if(Calendar.Def(c.scrollTop)){
			d+=c.scrollTop
		}
		c=Calendar.Def(c.parentNode)?c.parentNode:null
		if(c !== null && c.classList && (c.classList.contains('masterpanel') == true || c.classList.contains('panel') == true || c.classList.contains('owl-scroll-view') == true)){
			
			break;
		}
	}
	return d
}
Calendar.ScrollLeft = function(c) {
	var a,d=0;
	while(c){
		if(Calendar.Def(c.scrollLeft)){
			d+=c.scrollLeft
		}
		c=Calendar.Def(c.parentNode)?c.parentNode:null
		if(c !== null && c.classList && (c.classList.contains('masterpanel') == true || c.classList.contains('panel') == true)){
			break;
		}
	}
	return d
}
Calendar.getAbsolutePos = function(el) {
	var SL = Calendar.ScrollLeft(el), ST = Calendar.ScrollTop(el);
	var r = { x: Calendar.PageX(el) - SL, y: Calendar.PageY(el) - ST};
	return r;
};

Calendar.isRelated = function (el, evt) {
	var related = evt.relatedTarget;
	if (!related) {
		var type = evt.type;
		if (type == "mouseover") {
			related = evt.fromElement;
		} else if (type == "mouseout") {
			related = evt.toElement;
		}
	}
	while (related) {
		if (related == el) {
			return true;
		}
		related = related.parentNode;
	}
	return false;
};

Calendar.removeClass = function(el, className) {
	if (!(el && el.className)) {
		return;
	}
	var cls = el.className.split(" ");
	var ar = new Array();
	for (var i = cls.length; i > 0;) {
		if (cls[--i] != className) {
			ar[ar.length] = cls[i];
		}
	}
	el.className = ar.join(" ");
};

Calendar.addClass = function(el, className) {
	Calendar.removeClass(el, className);
	el.className += " " + className;
};

// FIXME: the following 2 functions totally suck, are useless and should be replaced immediately.
Calendar.getElement = function(ev) {
	var f = Calendar.is_ie ? window.event.srcElement : ev.currentTarget;
	while (f.nodeType != 1 || /^div$/i.test(f.tagName))
		f = f.parentNode;
	return f;
};

Calendar.getTargetElement = function(ev) {
	var f = Calendar.is_ie ? window.event.srcElement : ev.target;
	while (f.nodeType != 1)
		f = f.parentNode;
	return f;
};

Calendar.stopEvent = function(ev) {
	ev || (ev = window.event);
	if (Calendar.is_ie) {
		ev.cancelBubble = true;
		ev.returnValue = false;
	} else {
		ev.preventDefault();
		ev.stopPropagation();
	}
	return false;
};

Calendar.addEvent = function(el, evname, func) {
	if (el.attachEvent) { // IE
		el.attachEvent("on" + evname, func);
	} else if (el.addEventListener) { // Gecko / W3C
		el.addEventListener(evname, func, true);
	} else {
		el["on" + evname] = func;
	}
};

Calendar.removeEvent = function(el, evname, func) {
	if (el.detachEvent) { // IE
		el.detachEvent("on" + evname, func);
	} else if (el.removeEventListener) { // Gecko / W3C
		el.removeEventListener(evname, func, true);
	} else {
		el["on" + evname] = null;
	}
};

Calendar.createElement = function(type, areaDsplay) {
	var el = null;
	if (document.createElementNS) {
		// use the XHTML namespace; IE won't normally get here unless
		// _they_ "fix" the DOM2 implementation.
		el = document.createElementNS("http://www.w3.org/1999/xhtml", type);
	} else {
		el = document.createElement(type);
		
	}
	if(type =="button"){
		el.type = type;
	}
	if (typeof areaDsplay != "undefined") {
		areaDsplay.appendChild(el);
	}
	return el;
};

// END: UTILITY FUNCTIONS

// BEGIN: CALENDAR STATIC FUNCTIONS

/** Internal -- adds a set of events to make some element behave like a button. */
Calendar._add_evs = function(el) {
	with (Calendar) {
		addEvent(el, "mouseover", dayMouseOver);
		addEvent(el, "mousedown", dayMouseDown);
		addEvent(el, "mouseout", dayMouseOut);
		if (is_ie) {
			addEvent(el, "dblclick", dayMouseDblClick);
			el.setAttribute("unselectable", true);
		}
	}
};

Calendar.findMonth = function(el) {
	if (typeof el.month != "undefined") {
		return el;
	} else if (typeof el.parentNode.month != "undefined") {
		return el.parentNode;
	}
	return null;
};

Calendar.findYear = function(el) {
	if (typeof el.year != "undefined") {
		return el;
	} else if (typeof el.parentNode.year != "undefined") {
		return el.parentNode;
	}
	return null;
};

Calendar.showMonthsCombo = function () {
	var cal = Calendar._C;
	if (!cal) {
		return false;
	}
	var cal = cal;
	var cd = cal.activeDiv;
	var mc = cal.monthsCombo;
	if (cal.hilitedMonth) {
		Calendar.removeClass(cal.hilitedMonth, "hilite");
	}
	if (cal.activeMonth) {
		Calendar.removeClass(cal.activeMonth, "active");
	}
	var mon = cal.monthsCombo.getElementsByTagName("div")[cal.date.getMonth()];
	Calendar.addClass(mon, "active");
	cal.activeMonth = mon;
	var s = mc.style;
	s.display = "block";
	if (cd.navtype < 0)
		s.left = cd.offsetLeft + "px";
	else {
		var mcw = mc.offsetWidth;
		if (typeof mcw == "undefined")
			// Konqueror brain-dead techniques
			mcw = 50;
		s.left = (cd.offsetLeft + cd.offsetWidth - mcw) + "px";
	}
	s.top = (cd.offsetTop + cd.offsetHeight) + "px";
};

Calendar.showYearsCombo = function (fwd) {
	var cal = Calendar._C;
	if (!cal) {
		return false;
	}
	var cal = cal;
	var cd = cal.activeDiv;
	var yc = cal.yearsCombo;
	if (cal.hilitedYear) {
		Calendar.removeClass(cal.hilitedYear, "hilite");
	}
	if (cal.activeYear) {
		Calendar.removeClass(cal.activeYear, "active");
	}
	cal.activeYear = null;
	var Y = cal.date.getFullYear() + (fwd ? 1 : -1);
	var yr = yc.firstChild;
	var show = false;
	for (var i = 12; i > 0; --i) {
		if (Y >= cal.minYear && Y <= cal.maxYear) {
			yr.innerHTML = Y;
			yr.year = Y;
			yr.style.display = "block";
			show = true;
		} else {
			yr.style.display = "none";
		}
		yr = yr.nextSibling;
		Y += fwd ? cal.yearStep : -cal.yearStep;
	}
	if (show) {
		var s = yc.style;
		s.display = "block";
		if (cd.navtype < 0)
			s.left = cd.offsetLeft + "px";
		else {
			var ycw = yc.offsetWidth;
			if (typeof ycw == "undefined")
				// Konqueror brain-dead techniques
				ycw = 50;
			s.left = (cd.offsetLeft + cd.offsetWidth - ycw) + "px";
		}
		s.top = (cd.offsetTop + cd.offsetHeight) + "px";
	}
};

// event handlers

Calendar.tableMouseUp = function(ev) {
	var cal = Calendar._C;
	if (!cal) {
		return false;
	}
	if (cal.timeout) {
		clearTimeout(cal.timeout);
	}
	var el = cal.activeDiv;
	if (!el) {
		return false;
	}
	var target = Calendar.getTargetElement(ev);
	ev || (ev = window.event);
	Calendar.removeClass(el, "active");
	if (target == el || target.parentNode == el) {
		Calendar.cellClick(el, ev);
	}
	var mon = Calendar.findMonth(target);
	var date = null;
	if (mon) {
		date = new Date(cal.date);
		if (mon.month != date.getMonth()) {
			date.setMonth(mon.month);
			cal.setDate(date);
			cal.dateClicked = false;
			cal.callHandler();
		}
	} else {
		var year = Calendar.findYear(target);
		if (year) {
			date = new Date(cal.date);
			if (year.year != date.getFullYear()) {
				date.setFullYear(year.year);
				cal.setDate(date);
				cal.dateClicked = false;
				cal.callHandler();
			}
		}
	}
	with (Calendar) {
		removeEvent(document, "mouseup", tableMouseUp);
		removeEvent(document, "mouseover", tableMouseOver);
		removeEvent(document, "mousemove", tableMouseOver);
		cal._hideCombos();
		_C = null;
		return stopEvent(ev);
	}
};

Calendar.tableMouseOver = function (ev) {
	var cal = Calendar._C;
	if (!cal) {
		return;
	}
	var el = cal.activeDiv;
	var target = Calendar.getTargetElement(ev);
	if (target == el || target.parentNode == el) {
		Calendar.addClass(el, "hilite active");
		Calendar.addClass(el.parentNode, "rowhilite");
	} else {
		if (typeof el.navtype == "undefined" || (el.navtype != 50 && (el.navtype == 0 || Math.abs(el.navtype) > 2)))
			Calendar.removeClass(el, "active");
		Calendar.removeClass(el, "hilite");
		Calendar.removeClass(el.parentNode, "rowhilite");
	}
	ev || (ev = window.event);
	if (el.navtype == 50 && target != el) {
		var pos = Calendar.getAbsolutePos(el);
		var w = el.offsetWidth;
		var x = ev.clientX;
		var dx;
		var decrease = true;
		if (x > pos.x + w) {
			dx = x - pos.x - w;
			decrease = false;
		} else
			dx = pos.x - x;

		if (dx < 0) dx = 0;
		var range = el._range;
		var current = el._current;
		var count = Math.floor(dx / 10) % range.length;
		for (var i = range.length; --i >= 0;)
			if (range[i] == current)
				break;
		while (count-- > 0)
			if (decrease) {
				if (--i < 0)
					i = range.length - 1;
			} else if ( ++i >= range.length )
				i = 0;
		var newval = range[i];
		el.innerHTML = newval;

		cal.onUpdateTime();
	}
	var mon = Calendar.findMonth(target);
	if (mon) {
		if (mon.month != cal.date.getMonth()) {
			if (cal.hilitedMonth) {
				Calendar.removeClass(cal.hilitedMonth, "hilite");
			}
			Calendar.addClass(mon, "hilite");
			cal.hilitedMonth = mon;
		} else if (cal.hilitedMonth) {
			Calendar.removeClass(cal.hilitedMonth, "hilite");
		}
	} else {
		if (cal.hilitedMonth) {
			Calendar.removeClass(cal.hilitedMonth, "hilite");
		}
		var year = Calendar.findYear(target);
		if (year) {
			if (year.year != cal.date.getFullYear()) {
				if (cal.hilitedYear) {
					Calendar.removeClass(cal.hilitedYear, "hilite");
				}
				Calendar.addClass(year, "hilite");
				cal.hilitedYear = year;
			} else if (cal.hilitedYear) {
				Calendar.removeClass(cal.hilitedYear, "hilite");
			}
		} else if (cal.hilitedYear) {
			Calendar.removeClass(cal.hilitedYear, "hilite");
		}
	}
	return Calendar.stopEvent(ev);
};

Calendar.tableMouseDown = function (ev) {
	if (Calendar.getTargetElement(ev) == Calendar.getElement(ev)) {
		return Calendar.stopEvent(ev);
	}
};

Calendar.calDragIt = function (ev) {
	var cal = Calendar._C;
	if (!(cal && cal.dragging)) {
		return false;
	}
	var posX;
	var posY;
	if (Calendar.is_ie) {
		posY = window.event.clientY + document.body.scrollTop;
		posX = window.event.clientX + document.body.scrollLeft;
	} else {
		posX = ev.pageX;
		posY = ev.pageY;
	}
	cal.hideShowCovered();
	var st = cal.element.style;
	st.left = (posX - cal.xOffs) + "px";
	st.top = (posY - cal.yOffs) + "px";
	return Calendar.stopEvent(ev);
};

Calendar.calDragEnd = function (ev) {
	var cal = Calendar._C;
	if (!cal) {
		return false;
	}
	cal.dragging = false;
	with (Calendar) {
		removeEvent(document, "mousemove", calDragIt);
		removeEvent(document, "mouseup", calDragEnd);
		tableMouseUp(ev);
	}
	cal.hideShowCovered();
};

Calendar.dayMouseDown = function(ev) {
	var el = Calendar.getElement(ev);
	if (el.disabled) {
		return false;
	}
	var cal = el.calendar;
	cal.activeDiv = el;
	Calendar._C = cal;
	if (el.navtype != 300) with (Calendar) {
		if (el.navtype == 50) {
			el._current = el.innerHTML;
			addEvent(document, "mousemove", tableMouseOver);
		} else
			addEvent(document, Calendar.is_ie5 ? "mousemove" : "mouseover", tableMouseOver);
		addClass(el, "hilite active");
		addEvent(document, "mouseup", tableMouseUp);
	} else if (cal.isPopup) {
		cal._dragStart(ev);
	}
	if (el.navtype == -1 || el.navtype == 1) {
		if (cal.timeout) clearTimeout(cal.timeout);
		cal.timeout = setTimeout("Calendar.showMonthsCombo()", 250);
	} else if (el.navtype == -2 || el.navtype == 2) {
		if (cal.timeout) clearTimeout(cal.timeout);
		cal.timeout = setTimeout((el.navtype > 0) ? "Calendar.showYearsCombo(true)" : "Calendar.showYearsCombo(false)", 250);
	} else {
		cal.timeout = null;
	}
	return Calendar.stopEvent(ev);
};

Calendar.dayMouseDblClick = function(ev) {
	Calendar.cellClick(Calendar.getElement(ev), ev || window.event);
	if (Calendar.is_ie) {
		document.selection.empty();
	}
};

Calendar.dayMouseOver = function(ev) {
	var el = Calendar.getElement(ev);
	if (Calendar.isRelated(el, ev) || Calendar._C || el.disabled) {
		return false;
	}
	if (el.ttip) {
		if (el.ttip.substr(0, 1) == "_") {
			el.ttip = el.caldate.print(el.calendar.ttDateFormat) + el.ttip.substr(1);
		}
		var SL = Calendar.ScrollLeft(el), ST = Calendar.ScrollTop(el);
		x = ev.layerX;
		y = ev.layerY;
		let geserX = 20;
		let geserY = 20;
		if(document.body.offsetWidth<(ev.pageX+geserX+100)){
			geserX = -(60);
		}
		if(document.body.offsetHeight<(ev.pageY+geserY+40)){
			geserY = -(40);
		}
		el.calendar.tooltips.style.left = (geserX+x)+"px";
		el.calendar.tooltips.style.top = (geserY+y)+"px";
		el.calendar.tooltips.classList.remove("stop");
		el.calendar.tooltips.classList.add("move");
		el.calendar.tooltips.innerHTML = el.ttip;
	}
	if (el.navtype != 300) {
		Calendar.addClass(el, "hilite");
		if (el.caldate) {
			Calendar.addClass(el.parentNode, "rowhilite");
		}
	}
	return Calendar.stopEvent(ev);
};

Calendar.dayMouseOut = function(ev) {
	with (Calendar) {
		//console.log(ev);
		var el = getElement(ev);
		if (isRelated(el, ev) || _C || el.disabled)
			return false;
		removeClass(el, "hilite");
		if (el.caldate)
			removeClass(el.parentNode, "rowhilite");
		if (el.calendar)
			el.calendar.tooltips.innerHTML = _TT["SEL_DATE"];
			el.calendar.tooltips.classList.remove("move");
			el.calendar.tooltips.classList.add("stop");
			el.calendar.tooltips.style.left = null;
			el.calendar.tooltips.style.top = null;
		return stopEvent(ev);
	}
};

/**
 *  A generic "click" handler :) handles all types of buttons defined in this
 *  calendar.
 */
Calendar.cellClick = function(el, ev) {
	var cal = el.calendar;
	var closing = false;
	var newdate = false;
	var date = null;
	if (typeof el.navtype == "undefined") {
		if (cal.currentDateEl) {
			Calendar.removeClass(cal.currentDateEl, "selected");
			Calendar.addClass(el, "selected");
			closing = (cal.currentDateEl == el);
			if (!closing) {
				cal.currentDateEl = el;
			}
		}
		cal.date.setDateOnly(el.caldate);
		date = cal.date;
		var other_month = !(cal.dateClicked = !el.otherMonth);
		if (!other_month && !cal.currentDateEl)
			cal._toggleMultipleDate(new Date(date));
		else
			newdate = !el.disabled;
		// a date was clicked
		if (other_month)
			cal._init(cal.firstDayOfWeek, date);
	} else {
		if (el.navtype == 200) {
			Calendar.removeClass(el, "hilite");
			cal.callCloseHandler();
			return;
		}
		date = new Date(cal.date);
		if (el.navtype == 0)
			date.setDateOnly(new Date()); // TODAY
		// unless "today" was clicked, we assume no date was clicked so
		// the selected handler will know not to close the calenar when
		// in single-click mode.
		// cal.dateClicked = (el.navtype == 0);
		cal.dateClicked = false;
		var year = date.getFullYear();
		var mon = date.getMonth();
		function setMonth(m) {
			var day = date.getDate();
			var max = date.getMonthDays(m);
			if (day > max) {
				date.setDate(max);
			}
			date.setMonth(m);
		};
		switch (el.navtype) {
		    case 400:
			Calendar.removeClass(el, "hilite");
			var text = Calendar._TT["ABOUT"];
			if (typeof text != "undefined") {
				text += cal.showsTime ? Calendar._TT["ABOUT_TIME"] : "";
			} else {
				// FIXME: this should be removed as soon as lang files get updated!
				text = "Help and about box text is not translated into this language.\n" +
					"If you know this language and you feel generous please update\n" +
					"the corresponding file in \"lang\" subdir to match calendar-[lang].js\n";
			}
			alert(text);
			return;
		    case -2:
			if (year > cal.minYear) {
				date.setFullYear(year - 1);
			}
			break;
		    case -1:
			if (mon > 0) {
				setMonth(mon - 1);
			} else if (year-- > cal.minYear) {
				date.setFullYear(year);
				setMonth(11);
			}
			break;
		    case 1:
			if (mon < 11) {
				setMonth(mon + 1);
			} else if (year < cal.maxYear) {
				date.setFullYear(year + 1);
				setMonth(0);
			}
			break;
		    case 2:
			if (year < cal.maxYear) {
				date.setFullYear(year + 1);
			}
			break;
		    case 100:
			cal.setFirstDayOfWeek(el.fdow);
			return;
		    case 50:
			var range = el._range;
			var current = el.innerHTML;
			for (var i = range.length; --i >= 0;)
				if (range[i] == current)
					break;
			if (ev && ev.shiftKey) {
				if (--i < 0)
					i = range.length - 1;
			} else if ( ++i >= range.length )
				i = 0;
			var newval = range[i];
			el.innerHTML = newval;
			cal.onUpdateTime();
			return;
		    case 0:
			// TODAY will bring us here
			if ((typeof cal.getDateStatus == "function") &&
			    cal.getDateStatus(date, date.getFullYear(), date.getMonth(), date.getDate())) {
				return false;
			}
			break;
		}
		if (!date.equalsTo(cal.date)) {
			cal.setDate(date);
			newdate = true;
		} else if (el.navtype == 0)
			newdate = closing = true;
	}
	if (newdate) {
		ev && cal.callHandler();
	}
	if (closing) {
		Calendar.removeClass(el, "hilite");
		ev && cal.callCloseHandler();
	}
};
// Toggle to hours or minutes view
Calendar.prototype.toggleClass = function(element, className, flag){
	if(flag){
		if (!element || !className){
			return;
		}
		var classString = element.className, nameIndex = classString.indexOf(className);
		if (nameIndex == -1) {
			classString += ' ' + className;
		}
		else {
			classString = classString.substr(0, nameIndex) + classString.substr(nameIndex+className.length);
		}
		element.className = classString;
	}
}
function raiseCallback(callbackFunction) {
	if (callbackFunction && typeof callbackFunction === "function") {
		callbackFunction();
	}
}
Calendar.prototype.toggleView = function(view, delay){
	
	var raiseAfterHourSelect = false;
	if (view === 'minutes' && this.hoursView.style.visibility === "visible") {
		//raiseCallback(this.options.beforeHourSelect);
		raiseAfterHourSelect = true;
	}
	var isHours = view === 'hours',
		nextView = isHours ? this.hoursView : this.minutesView,
		hideView = isHours ? this.minutesView : this.hoursView;

	this.currentView = view;
	//this.toggleClass(isHours,'text-primary');
	//this.toggleClass(! isHours,'text-primary');
	this.toggleClass(this.spanHours,'text-primary',isHours);
	this.toggleClass(this.spanMinutes,'text-primary',!isHours);
	//this.spanMinutes.toggleClass('text-primary',! isHours);
	// Let's make transitions
	hideView.classList.add('clockpicker-dial-out');
	nextView.style.visibility = 'visible';
	nextView.classList.remove('clockpicker-dial-out');

	// Reset clock hand
	this.resetClock(delay);

	// After transitions ended
	clearTimeout(this.toggleViewTimer);
	this.toggleViewTimer = setTimeout(function(){
		hideView.style.visibility = 'hidden';
	}, this.duration);

	if (raiseAfterHourSelect) {
		//raiseCallback(this.options.afterHourSelect);
	}
};
Calendar.prototype.resetClock = function(delay){
	var view = this.currentView,
		value = this[view],
		isHours = view === 'hours',
		unit = Math.PI / (isHours ? 6 : 30),
		radian = value * unit,
		radius = isHours && value > 0 && value < 13 ? this.innerRadius : this.outerRadius,
		x = Math.sin(radian) * radius,
		y = - Math.cos(radian) * radius,
		self = this;
	if (this.svgSupported && delay) {
		self.canvas.classList.add('clockpicker-canvas-out');
		setTimeout(function(){
			self.canvas.classList.remove('clockpicker-canvas-out');
			self.setHand(x, y);
		}, delay);
	} else {
		this.setHand(x, y);
	}
};
// END: CALENDAR STATIC FUNCTIONS

// BEGIN: CALENDAR OBJECT FUNCTIONS

/**
 *  This function creates the calendar inside the given parent.  If _par is
 *  null than it creates a popup calendar inside the BODY element.  If _par is
 *  an element, be it BODY, then it creates a non-popup calendar (still
 *  hidden).  Some properties need to be set before calling this function.
 */
Calendar.prototype.setHand = function (x, y, roundBy5, dragging){
	self = this;
	var radian = Math.atan2(x, - y),
		isHours = self.currentView === 'hours',
		unit = Math.PI / (isHours || roundBy5 ? 6 : 30),
		z = Math.sqrt(x * x + y * y),
		inner = isHours && z < (this.outerRadius + this.innerRadius) / 2,
		radius = inner ? this.innerRadius : this.outerRadius,value;
		
		if (this.twelvehour) {
			radius = this.outerRadius;
		}
	// Radian should in range [0, 2PI]
	if (radian < 0) {
		radian = Math.PI * 2 + radian;
	}

	// Get the round value
	value = Math.round(radian / unit);

	// Get the round radian
	radian = value * unit;
	// Correct the hours or minutes
	if (this.twelvehour) {
		if (isHours) {
			if (value === 0) {
				value = 12;
			}
		} else {
			if (roundBy5) {
				value *= 5;
			}
			if (value === 60) {
				value = 0;
			}
		}
	} else {
		if (isHours) {
			if (value === 12) {
				value = 0;
			}
			value = inner ? (value === 0 ? 12 : value) : value === 0 ? 0 : value + 12;
		} else {
			if (roundBy5) {
				value *= 5;
			}
			if (value === 60) {
				value = 0;
			}
		}
	}
	this[self.currentView] = value;
	var elementChange = this[isHours ? 'spanHours' : 'spanMinutes'];
	elementChange.innerHTML = leadingZero(value);
	
	var date = self.date;
	var h = parseInt(self.spanHours.innerHTML, 10);
	var t12 = !self.time24;
	if (t12) {
		if (/pm/i.test(self.AP.innerHTML) && h < 12)
			h += 12;
		else if (/am/i.test(self.AP.innerHTML) && h == 12)
			h = 0;
	}
	if (isHours) {
		date.setHours(h);
	}else{
		date.setMinutes(parseInt(self.spanMinutes.innerHTML, 10));
	}
	
	// Place clock hand at the top when dragging
	if (dragging || (! isHours && value % 5)) {
		this.g.insertBefore(this.hand, this.bearing);
		this.g.insertBefore(this.bg, this.fg);
		this.bg.setAttribute('class', 'clockpicker-canvas-bg clockpicker-canvas-bg-trans');
	} else {
		// Or place it at the bottom
		this.g.insertBefore(this.hand, this.bg);
		this.g.insertBefore(this.fg, this.bg);
		this.bg.setAttribute('class', 'clockpicker-canvas-bg');
	}

	// Set clock hand and others' position
	var cx = Math.sin(radian) * radius,
		cy = - Math.cos(radian) * radius;
	this.hand.setAttribute('x2', cx);
	this.hand.setAttribute('y2', cy);
	this.bg.setAttribute('cx', cx);
	this.bg.setAttribute('cy', cy);
	this.fg.setAttribute('cx', cx);
	this.fg.setAttribute('cy', cy);
};
Calendar.prototype.create = function (_par) {
	var areaDsplay = null;
	var self = this;
	
	if (! _par) {
		if(this.params.isMobile){
			areaDsplay = document.getElementsByTagName("body")[0];
		}else{
			if(Calendar.isElement(self.params.button)){
				areaDsplay = self.params.button.parentNode;
			}else if(typeof document.getElementById('containerbody') != 'undefined'){
				if(document.getElementById('containerbody').classList.contains("owl-scrollbar-container")){
					bodyParent = document.getElementById('containerbody').getElementsByClassName('owl-scroll-view');
					if(bodyParent.length > 0){
						areaDsplay = document.getElementById('containerbody').getElementsByClassName('owl-scroll-view')[0];
					}else{
						areaDsplay = document.getElementById('containerbody');
					}
				}else{
					areaDsplay = document.getElementById('containerbody');
				}
			}else{
				areaDsplay = document.getElementsByTagName("body")[0];
			}
		}
		this.isPopup = true;
	} else {
		areaDsplay = _par;
		this.isPopup = false;
	}
	this.date = this.dateStr ? new Date(this.dateStr) : new Date();
	
	var calBoth = Calendar.createElement("div");
	calBoth.classList.add("div-table");
	
	this.table = calBoth;
	calBoth.calendar = this;
	Calendar.addEvent(calBoth, "mousedown", Calendar.tableMouseDown);

	var div = Calendar.createElement("div");
	this.element = div;
	div.className = "calendar";
	if (this.isPopup) {
		div.style.position = "fixed";
		div.style.display = "none";
	}
	div.appendChild(calBoth);
	
	var calHader = Calendar.createElement("div", calBoth);
	calHader.classList.add("div-table-header");
	var cal = this;
	var cell = null;
	var row = null;
	var hh = function (text, cs, navtype,ico,eleName) {
		if(typeof eleName === "undefined"){
			var eleName = "button";
		}
		cell = Calendar.createElement(eleName, row);
		cell.className = "div-table-col col-"+cs+" button";
		if (navtype != 0 && Math.abs(navtype) <= 2)
			cell.className += " nav";
		Calendar._add_evs(cell);
		cell.calendar = cal;
		cell.navtype = navtype;
		if(typeof ico !== "undefined"){
			cell.innerHTML = "<div class='"+text+"' unselectable='on'></div>";
		}else{
			cell.innerHTML = "<div unselectable='on'>" + text + "</div>";
		}
		return cell;
	};
	

	row = Calendar.createElement("div", calHader);
	row.classList.add("div-table-row");
	var title_length = 6;
	(this.isPopup) && --title_length;
	(this.weekNumbers) && ++title_length;

	hh("?", 1, 400).ttip = Calendar._TT["INFO"];
	this.title = hh("", title_length, 300);
	this.title.className = "div-table-col col-6 title";
	if (this.isPopup) {
		this.title.ttip = Calendar._TT["DRAG_TO_MOVE"];
		this.title.style.cursor = "move";
		var closeicon = "&#x00d7;";
		if(Calendar._TT["CLOSEICON"]){
			closeicon = Calendar._TT["CLOSEICON"];
		}
		hh(closeicon, 1, 200,'icon').ttip = Calendar._TT["CLOSE"];
	}

	row = Calendar.createElement("div", calHader);
	row.classList.add("div-table-row");
	row.classList.add("headrow");
	if(!this.showsDate){
		row.style.display = "none";
	}
	prev_yearicon = "&#x00ab;";
	if(Calendar._TT["PREV_YEARICON"]){
		prev_yearicon = Calendar._TT["PREV_YEARICON"];
	}
	this._nav_py = hh(prev_yearicon, 1, -2,'icon');
	this._nav_py.ttip = Calendar._TT["PREV_YEAR"];
	prev_monthicon = "&#x2039;";
	if(Calendar._TT["PREV_MONTHICON"]){
		prev_monthicon = Calendar._TT["PREV_MONTHICON"];
	}
	this._nav_pm = hh(prev_monthicon, 1, -1,'icon');
	this._nav_pm.ttip = Calendar._TT["PREV_MONTH"];
	
	this._nav_now = hh(Calendar._TT["TODAY"], this.weekNumbers ? 4 : 4, 0);
	this._nav_now.ttip = Calendar._TT["GO_TODAY"];
	next_monthicon = "&#x203a;";
	if(Calendar._TT["NEXT_MONTHICON"]){
		next_monthicon = Calendar._TT["NEXT_MONTHICON"];
	}
	this._nav_nm = hh(next_monthicon, 1, 1,'icon');
	this._nav_nm.ttip = Calendar._TT["NEXT_MONTH"];
	next_yearicon = "&#x00bb;";
	if(Calendar._TT["NEXT_YEARICON"]){
		next_yearicon = Calendar._TT["NEXT_YEARICON"];
	}
	this._nav_ny = hh(next_yearicon, 1, 2,'icon');
	this._nav_ny.ttip = Calendar._TT["NEXT_YEAR"];

	// day names
	
	row = Calendar.createElement("div", calHader);
	row.classList.add("div-table-row");
	row.classList.add("daynames");
	if(!this.showsDate){
		row.style.display = "none";
	}
	if (this.weekNumbers) {
		cell = Calendar.createElement("button", row);//div-table-col
		cell.className = "div-table-col name wn";
		cell.innerHTML = Calendar._TT["WK"];
	}else{
		row.classList.add("nonwk");
	}
	for (var i = 7; i > 0; --i) {
		cell = Calendar.createElement("button", row);
		if (!i) {
			cell.navtype = 100;
			cell.calendar = this;
			Calendar._add_evs(cell);
		}
	}
	
	this.firstdayname = (this.weekNumbers) ? row.firstChild.nextSibling : row.firstChild;
	this._displayWeekdays();
	this.currentView = 'hours';
	this.hours = 0;
	this.minutes = 0;
	var tbody = Calendar.createElement("div", calBoth);
	tbody.className = "div-table-body";
	if (this.weekNumbers) {
		//row.classList.add("nonwk");
	}else{
		tbody.classList.add("nonwk");
		
	}
	this.dateBlock = tbody;
	this.tbody = tbody;

	for (i = 6; i > 0; --i) {
		row = Calendar.createElement("div", tbody);
		if (this.weekNumbers) {
			cell = Calendar.createElement("button", row);
		}
		for (var j = 7; j > 0; --j) {
			cell = Calendar.createElement("button", row);
			cell.calendar = this;
			Calendar._add_evs(cell);
		}
	}
	
	var tfoot = Calendar.createElement("div", calBoth);
	tfoot.className = "div-table-footer";
	

	if (this.showsTime) {
		// Can I use transition ?
	var transitionSupported = (function(){
		var style = document.createElement('div').style;
		return 'transition' in style ||
			'WebkitTransition' in style ||
			'MozTransition' in style ||
			'msTransition' in style ||
			'OTransition' in style;
	})();
		// Clock size
	var dialRadius = 100,
		outerRadius = 80,
		// innerRadius = 80 on 12 hour clock
		innerRadius = 54,
		tickRadius = 13,
		diameter = dialRadius * 2,
		duration = transitionSupported ? 350 : 1;
		
	this.dialRadius = dialRadius;
	this.outerRadius = outerRadius;
	this.innerRadius = innerRadius;
	this.tickRadius = tickRadius;
	this.diameter = diameter;
	this.duration = duration;
	
	
	var svgNS = 'http://www.w3.org/2000/svg',
		svgSupported = 'SVGAngle' in window && (function(){
			var supported,
				el = document.createElement('div');
			el.innerHTML = '<svg/>';
			supported = (el.firstChild && el.firstChild.namespaceURI) == svgNS;
			el.innerHTML = '';
			return supported;
		})();	
	this.svgSupported = svgSupported;	
	
		row = Calendar.createElement("div", tfoot);
		row.classList.add("div-table-row");
		row.classList.add("time");
		
		var clock = Calendar.createElement("div", row);
		clock.className = "popover clockpicker-popover bottom clockpicker-align-left";
		//clock.style = "display: block; top: 623px; left: 43px;";
		this.clockBlock = clock;
		
		var titleClock = Calendar.createElement("div", clock);
		titleClock.className = "popover-title";
		var contentClock = Calendar.createElement("div", clock);
		contentClock.className = "popover-content";
		this.contentClockBlock = contentClock;
		
		var plate = Calendar.createElement("div", contentClock);
		plate.className = "clockpicker-plate";
		var canvasDiv = Calendar.createElement("div", plate);
		canvasDiv.className = "clockpicker-canvas";
		var hoursClock = Calendar.createElement("div", plate);
		hoursClock.className = "clockpicker-dial clockpicker-hours";
		hoursClock.style = "visibility: visible;";
		var minutesClock = Calendar.createElement("div", plate);
		minutesClock.className = "clockpicker-dial clockpicker-minutes";//clockpicker-dial-out
		this.hoursView = hoursClock;
		this.minutesView = minutesClock;
		this.canvas = canvasDiv;
		
		var clockSVG = '<svg class="clockpicker-svg" width="200" height="200"><g transform="translate(100,100)"><line x1="0" y1="0" x2="-54" y2="9.91963907309356e-15"></line><circle class="clockpicker-canvas-fg" r="3.5" cx="-54" cy="9.91963907309356e-15"></circle><circle class="clockpicker-canvas-bg" r="13" cx="-54" cy="9.91963907309356e-15"></circle><circle class="clockpicker-canvas-bearing" cx="0" cy="0" r="2"></circle></g></svg>';
		//canvasDiv.innerHTML = clockSVG;
		function createSvgElement(name) {
			return document.createElementNS(svgNS, name);
		}
		if (svgSupported) {
			svg = createSvgElement('svg');
			svg.setAttribute('class', 'clockpicker-svg');
			svg.setAttribute('width', diameter);
			svg.setAttribute('height', diameter);
			var g = createSvgElement('g');
			g.setAttribute('transform', 'translate(' + dialRadius + ',' + dialRadius + ')');
			var bearing = createSvgElement('circle');
			bearing.setAttribute('class', 'clockpicker-canvas-bearing');
			bearing.setAttribute('cx', 0);
			bearing.setAttribute('cy', 0);
			bearing.setAttribute('r', 2);
			var hand = createSvgElement('line');
			hand.setAttribute('x1', 0);
			hand.setAttribute('y1', 0);
			var bg = createSvgElement('circle');
			bg.setAttribute('class', 'clockpicker-canvas-bg');
			bg.setAttribute('r', tickRadius);
			var fg = createSvgElement('circle');
			fg.setAttribute('class', 'clockpicker-canvas-fg');
			fg.setAttribute('r', 3.5);
			g.appendChild(hand);
			g.appendChild(bg);
			g.appendChild(fg);
			g.appendChild(bearing);
			svg.appendChild(g);
			canvasDiv.appendChild(svg);
			
			this.hand = hand;
			this.bg = bg;
			this.fg = fg;
			this.bearing = bearing;
			this.g = g;
		}
		

		(function(){
			//Mousedown or touchstart
			plate.onmousedown = function(e){
				if (!e.target.closest('.clockpicker-tick')) {
					mousedown(e, true);
				}
			}
			
			function mousedown(e, space) {
				//console.log("Mouse Down");
				var offset = plate.getBoundingClientRect();
				var isTouch = /^touch/.test(e.type),
					x0 = offset.left + dialRadius,
					y0 = offset.top + dialRadius,
					dx = (isTouch ? e.originalEvent.touches[0] : e).pageX - x0,
					dy = (isTouch ? e.originalEvent.touches[0] : e).pageY - y0,
					z = Math.sqrt(dx * dx + dy * dy),
					moved = false;
				// When clicking on minutes view space, check the mouse position
				//console.log(e.pageX,x0,dx);
				//console.log(e.pageY,y0,dy);
				//console.log(z,outerRadius - tickRadius);
				if(z < outerRadius - tickRadius){
					//console.log("Kesalahan ke 1");
				}
				if(z > outerRadius + tickRadius){
					//console.log("Kesalahan ke 2");
				}
				if (space && (z < outerRadius - tickRadius || z > outerRadius + tickRadius)) {
					return;
				}
				
				e.preventDefault();
				// Set cursor style of body after 200ms
				var movingTimer = setTimeout(function(){
					document.body.classList.add('clockpicker-moving');
				}, 200);
				
				self.setHand(dx, dy, ! space, true);
				document.onmousemove = null;
				document.onmousemove = function(e){
					//console.log("move");
					//console.log(e);
					e.preventDefault();
					var isTouch = /^touch/.test(e.type),
						x = (isTouch ? e.originalEvent.touches[0] : e).pageX - x0,
						y = (isTouch ? e.originalEvent.touches[0] : e).pageY - y0;
						//console.log(x,y,dx,dy);
					if (! moved && x === dx && y === dy) {
						// Clicking in chrome on windows will trigger a mousemove event
						return;
					}
					moved = true;
					self.setHand(x, y, false, true);
				};

				document.onmouseup = null;
				document.onmouseup = function(e){
					document.onmousemove = null;
					e.preventDefault();
					//console.log("up");
					var isTouch = /^touch/.test(e.type),
						x = (isTouch ? e.originalEvent.changedTouches[0] : e).pageX - x0,
						y = (isTouch ? e.originalEvent.changedTouches[0] : e).pageY - y0;
					if ((space || moved) && x === dx && y === dy) {
						self.setHand(x, y);
					}
					if (self.currentView === 'hours') {
						self.toggleView('minutes', duration / 2);
					} else {
						self.callHandler();
						
						/*
						if (options.autoclose) {
							self.minutesView.classList.add('clockpicker-dial-out');
							setTimeout(function(){
								self.done();
							}, duration / 2);
						}*/
					}
					
					
					
					document.body.classList.remove('clockpicker-moving');
					clearTimeout(movingTimer);
				};
					
				
			}
			// Clicking on minutes view space
			// plate.onmousedown = function(e){
				// if (e.target.closest('.clockpicker-tick').length === 0) {
					//mousedown(e, true);
					// alert();
				// }
			// };
			
			function makeTimePart(className, init, range_start, range_end) {
				var part = Calendar.createElement("span", titleClock);
				part.className = className;
				part.innerHTML = init;
				part.calendar = cal;
				part.ttip = Calendar._TT["TIME_PART"];
				part.navtype = 50;
				part._range = [];
				if (typeof range_start != "number")
					part._range = range_start;
				else {
					for (var i = range_start; i <= range_end; ++i) {
						var txt;
						if (i < 10 && range_end >= 10) txt = '0' + i;
						else txt = '' + i;
						part._range[part._range.length] = txt;
					}
				if(range_start == 0 && range_end == 59){
					self.minutes = txt;
					// Minutes view
					for (i = 0; i < 60; i += 5) {
						var tick = Calendar.createElement("div", minutesClock);
						tick.className = "clockpicker-tick";
						radian = i / 30 * Math.PI;
						tick.style.left = (dialRadius + Math.sin(radian) * radius - tickRadius)+"px";
						tick.style.top = (dialRadius - Math.cos(radian) * radius - tickRadius)+"px";
						tick.style.fontSize = "120%";
						tick.innerHTML = leadingZero(i);
						tick.onmousedown = function(e){
							mousedown(e,true);
						}
					}
				}else{
					self.hours = txt;
					for (i = 0; i < 24; i += 1) {
						//tick = tickTpl.clone();
						var tick = Calendar.createElement("div", hoursClock);
						tick.className = "clockpicker-tick";
						
						radian = i / 6 * Math.PI;
						var inner = i > 0 && i < 13;
						radius = inner ? innerRadius : outerRadius;
						tick.style.left = (dialRadius + Math.sin(radian) * radius - tickRadius)+"px";
						tick.style.top = (dialRadius - Math.cos(radian) * radius - tickRadius)+"px";
						tick.style.fontSize = "120%";
						tick.innerHTML = (i === 0 ? '00' : i);
						tick.onmousedown = function(e){
							mousedown(e,false);
						}
						
					}
				}	
				}
				
				Calendar._add_evs(part);
				return part;
			};
			var hrs = cal.date.getHours();
			var mins = cal.date.getMinutes();
			var t12 = !cal.time24;
			var pm = (hrs > 12);
			if (t12 && pm) hrs -= 12;
			var H = makeTimePart("hour clockpicker-span-hours text-primary", hrs, t12 ? 1 : 0, t12 ? 12 : 23);
			self.spanHours = H;
	
			var span = Calendar.createElement("span", titleClock);
			span.innerHTML = ":";
			span.className = "colon";
			var M = makeTimePart("minute clockpicker-span-minutes", mins, 0, 59);
			self.spanMinutes = M;
			
			cell = Calendar.createElement("div", row);
			cell.className = "div-table-col time";
			cell.colSpan = 2;
			if (t12)
				this.AP = makeTimePart("clockpicker-span-am-pm", pm ? "pm" : "am", ["am", "pm"]);
			else
				cell.style.width="100%";
				cell.style.textAlign="center";
				closeBtn = Calendar.createElement("button", cell);
				Calendar.addClass(closeBtn, "button");
				Calendar.addClass(closeBtn, "mybutton");
				closeBtn.onclick = function(){
					cal.callCloseHandler();
				}
				closeBtn.ontouchend = function(){
					cal.callCloseHandler();
				}
				closeBtn.innerHTML = "Close";
				closeBtn.style.width="95%";
				//cell.innerHTML = "&nbsp;";
				
			cal.onSetTime = function() {
				var pm, hrs = this.date.getHours(),
					mins = this.date.getMinutes();
				if (t12) {
					pm = (hrs >= 12);
					if (pm) hrs -= 12;
					if (hrs == 0) hrs = 12;
					this.AP.innerHTML = pm ? "pm" : "am";
				}
				H.innerHTML = (hrs < 10) ? ("0" + hrs) : hrs;
				M.innerHTML = (mins < 10) ? ("0" + mins) : mins;
				self.hours = hrs;
				self.minutes = mins;
				self.resetClock(duration/2);
			};
			cal.onUpdateTime = function() {
				var date = this.date;
				var h = parseInt(H.innerHTML, 10);
				if (t12) {
					if (/pm/i.test(this.AP.innerHTML) && h < 12)
						h += 12;
					else if (/am/i.test(this.AP.innerHTML) && h == 12)
						h = 0;
				}
				var d = date.getDate();
				var m = date.getMonth();
				var y = date.getFullYear();
				date.setHours(h);
				date.setMinutes(parseInt(M.innerHTML, 10));
				self.hours = h;
				self.minutes = parseInt(M.innerHTML, 10);
				date.setFullYear(y);
				date.setMonth(m);
				date.setDate(d);
				this.dateClicked = false;
				this.callHandler();
			};
		})();
	} else {
		this.onSetTime = this.onUpdateTime = function() {};
	}

	var tfoot = Calendar.createElement("div", calBoth);
	tfoot.className = "div-table-footer";
	row = Calendar.createElement("div", tfoot);
	row.className = "div-table-row footrow";
	cell = hh(Calendar._TT["SEL_DATE"], this.weekNumbers ? 8 : 7, 300,"","div");
	cell.className = "ttip";
	if (this.isPopup) {
		cell.ttip = Calendar._TT["DRAG_TO_MOVE"];
	}
	this.tooltips = cell;

	div = Calendar.createElement("div", this.element);
	this.monthsCombo = div;
	div.className = "combo";
	for (i = 0; i < Calendar._MN.length; ++i) {
		var mn = Calendar.createElement("div");
		mn.className = Calendar.is_ie ? "label-IEfix" : "label";
		mn.month = i;
		mn.innerHTML = Calendar._SMN[i];
		div.appendChild(mn);
	}

	div = Calendar.createElement("div", this.element);
	this.yearsCombo = div;
	div.className = "combo";
	for (i = 12; i > 0; --i) {
		var yr = Calendar.createElement("div");
		yr.className = Calendar.is_ie ? "label-IEfix" : "label";
		div.appendChild(yr);
	}

	this._init(this.firstDayOfWeek, this.date);
	areaDsplay.appendChild(this.element);
	//console.log(areaDsplay.offsetWidth);
};

/** keyboard navigation, only for popup calendars */
Calendar._keyEvent = function(ev) {
	var cal = window._atwal_popupCalendar;
	if (!cal || cal.multiple)
		return false;
	(Calendar.is_ie) && (ev = window.event);
	var act = (Calendar.is_ie || ev.type == "keypress"),
		K = ev.keyCode;
	if (ev.ctrlKey) {
		switch (K) {
		    case 37: // KEY left
			act && Calendar.cellClick(cal._nav_pm);
			break;
		    case 38: // KEY up
			act && Calendar.cellClick(cal._nav_py);
			break;
		    case 39: // KEY right
			act && Calendar.cellClick(cal._nav_nm);
			break;
		    case 40: // KEY down
			act && Calendar.cellClick(cal._nav_ny);
			break;
		    default:
			return false;
		}
	} else switch (K) {
	    case 32: // KEY space (now)
		Calendar.cellClick(cal._nav_now);
		break;
	    case 27: // KEY esc
		act && cal.callCloseHandler();
		break;
	    case 37: // KEY left
	    case 38: // KEY up
	    case 39: // KEY right
	    case 40: // KEY down
		if (act) {
			var prev, x, y, ne, el, step;
			prev = K == 37 || K == 38;
			step = (K == 37 || K == 39) ? 1 : 7;
			function setVars() {
				el = cal.currentDateEl;
				var p = el.pos;
				x = p & 15;
				y = p >> 4;
				ne = cal.ar_days[y][x];
			};setVars();
			function prevMonth() {
				var date = new Date(cal.date);
				date.setDate(date.getDate() - step);
				cal.setDate(date);
			};
			function nextMonth() {
				var date = new Date(cal.date);
				date.setDate(date.getDate() + step);
				cal.setDate(date);
			};
			while (1) {
				switch (K) {
				    case 37: // KEY left
					if (--x >= 0)
						ne = cal.ar_days[y][x];
					else {
						x = 6;
						K = 38;
						continue;
					}
					break;
				    case 38: // KEY up
					if (--y >= 0)
						ne = cal.ar_days[y][x];
					else {
						prevMonth();
						setVars();
					}
					break;
				    case 39: // KEY right
					if (++x < 7)
						ne = cal.ar_days[y][x];
					else {
						x = 0;
						K = 40;
						continue;
					}
					break;
				    case 40: // KEY down
					if (++y < cal.ar_days.length)
						ne = cal.ar_days[y][x];
					else {
						nextMonth();
						setVars();
					}
					break;
				}
				break;
			}
			if (ne) {
				if (!ne.disabled)
					Calendar.cellClick(ne);
				else if (prev)
					prevMonth();
				else
					nextMonth();
			}
		}
		break;
	    case 13: // KEY enter
		if (act)
			Calendar.cellClick(cal.currentDateEl, ev);
		break;
	    default:
		return false;
	}
	return Calendar.stopEvent(ev);
};

/**
 *  (RE)Initializes the calendar to the given date and firstDayOfWeek
 */
Calendar.prototype._init = function (firstDayOfWeek, date) {
	var today = new Date(),
		TY = today.getFullYear(),
		TM = today.getMonth(),
		TD = today.getDate();
	this.table.style.visibility = "hidden";
	var year = date.getFullYear();
	if (year < this.minYear) {
		year = this.minYear;
		date.setFullYear(year);
	} else if (year > this.maxYear) {
		year = this.maxYear;
		date.setFullYear(year);
	}
	this.firstDayOfWeek = firstDayOfWeek;
	this.date = new Date(date);
	var month = date.getMonth();
	var mday = date.getDate();
	var no_days = date.getMonthDays();

	// calendar voodoo for computing the first day that would actually be
	// displayed in the calendar, even if it's from the previous month.
	// WARNING: this is magic. ;-)
	date.setDate(1);
	var day1 = (date.getDay() - this.firstDayOfWeek) % 7;
	if (day1 < 0)
		day1 += 7;
	date.setDate(-day1);
	date.setDate(date.getDate() + 1);

	var row = this.tbody.firstChild;
	var MN = Calendar._SMN[month];
	var ar_days = this.ar_days = new Array();
	var weekend = Calendar._TT["WEEKEND"];
	var dates = this.multiple ? (this.datesCells = {}) : null;
	for (var i = 0; i < 6; ++i, row = row.nextSibling) {
		var cell = row.firstChild;
		if (this.weekNumbers) {
			cell.className = "div-table-col day wn";
			cell.innerHTML = date.getWeekNumber();
			cell = cell.nextSibling;
		}
		row.className = "div-table-row daysrow";
		var hasdays = false, iday, dpos = ar_days[i] = [];
		for (var j = 0; j < 7; ++j, cell = cell.nextSibling, date.setDate(iday + 1)) {
			iday = date.getDate();
			var wday = date.getDay();
			cell.className = "div-table-col day";
			cell.pos = i << 4 | j;
			dpos[j] = cell;
			var current_month = (date.getMonth() == month);
			if (!current_month) {
				if (this.showsOtherMonths) {
					cell.className += " othermonth";
					cell.otherMonth = true;
				} else {
					cell.className = "div-table-col emptycell";
					cell.innerHTML = "&nbsp;";
					cell.disabled = true;
					continue;
				}
			} else {
				cell.otherMonth = false;
				hasdays = true;
			}
			cell.disabled = false;
			cell.innerHTML = this.getDateText ? this.getDateText(date, iday) : iday;
			if (dates)
				dates[date.print("%Y%m%d")] = cell;
			if (this.getDateStatus) {
				var status = this.getDateStatus(date, year, month, iday);
				if (this.getDateToolTip) {
					var toolTip = this.getDateToolTip(date, year, month, iday);
					if (toolTip)
						cell.title = toolTip;
				}
				if (status === true) {
					cell.className += " disabled";
					cell.disabled = true;
				} else {
					if (/disabled/i.test(status))
						cell.disabled = true;
					cell.className += " " + status;
				}
			}
			if (!cell.disabled) {
				cell.caldate = new Date(date);
				cell.ttip = "_";
				if (!this.multiple && current_month
				    && iday == mday && this.hiliteToday) {
					cell.className += " selected";
					this.currentDateEl = cell;
				}
				if (date.getFullYear() == TY &&
				    date.getMonth() == TM &&
				    iday == TD) {
					cell.className += " today";
					cell.ttip += Calendar._TT["PART_TODAY"];
				}
				if (weekend.indexOf(wday.toString()) != -1)
					cell.className += cell.otherMonth ? " oweekend" : " weekend";
			}
		}
		if (!(hasdays || this.showsOtherMonths))
			row.className = "div-table-row emptyrow";
	}
	this.title.innerHTML = Calendar._MN[month] + ", " + year;
	this.onSetTime();
	this.table.style.visibility = "visible";
	this._initMultipleDates();
	// PROFILE
	// this.tooltips.innerHTML = "Generated in " + ((new Date()) - today) + " ms";
};

Calendar.prototype._initMultipleDates = function() {
	if (this.multiple) {
		for (var i in this.multiple) {
			var cell = this.datesCells[i];
			var d = this.multiple[i];
			if (!d)
				continue;
			if (cell)
				cell.className += " selected";
		}
	}
};

Calendar.prototype._toggleMultipleDate = function(date) {
	if (this.multiple) {
		var ds = date.print("%Y%m%d");
		var cell = this.datesCells[ds];
		if (cell) {
			var d = this.multiple[ds];
			if (!d) {
				Calendar.addClass(cell, "selected");
				this.multiple[ds] = date;
			} else {
				Calendar.removeClass(cell, "selected");
				delete this.multiple[ds];
			}
		}
	}
};

Calendar.prototype.setDateToolTipHandler = function (unaryFunction) {
	this.getDateToolTip = unaryFunction;
};

/**
 *  Calls _init function above for going to a certain date (but only if the
 *  date is different than the currently selected one).
 */
Calendar.prototype.setDate = function (date) {
	if (!date.equalsTo(this.date)) {
		this._init(this.firstDayOfWeek, date);
	}
};

/**
 *  Refreshes the calendar.  Useful if the "disabledHandler" function is
 *  dynamic, meaning that the list of disabled date can change at runtime.
 *  Just * call this function if you think that the list of disabled dates
 *  should * change.
 */
Calendar.prototype.refresh = function () {
	this._init(this.firstDayOfWeek, this.date);
};

/** Modifies the "firstDayOfWeek" parameter (pass 0 for Synday, 1 for Monday, etc.). */
Calendar.prototype.setFirstDayOfWeek = function (firstDayOfWeek) {
	this._init(firstDayOfWeek, this.date);
	this._displayWeekdays();
};

/**
 *  Allows customization of what dates are enabled.  The "unaryFunction"
 *  parameter must be a function object that receives the date (as a JS Date
 *  object) and returns a boolean value.  If the returned value is true then
 *  the passed date will be marked as disabled.
 */
Calendar.prototype.setDateStatusHandler = Calendar.prototype.setDisabledHandler = function (unaryFunction) {
	this.getDateStatus = unaryFunction;
};

/** Customization of allowed year range for the calendar. */
Calendar.prototype.setRange = function (a, z) {
	this.minYear = a;
	this.maxYear = z;
};

/** Calls the first user handler (selectedHandler). */
Calendar.prototype.callHandler = function () {
	if (this.onSelected) {
		this.onSelected(this, this.date.print(this.dateFormat));
	}
};

/** Calls the second user handler (closeHandler). */
Calendar.prototype.callCloseHandler = function () {
	if (this.onClose) {
		this.onClose(this);
	}
	this.hideShowCovered();
};

/** Removes the calendar object from the DOM tree and destroys it. */
Calendar.prototype.destroy = function () {
	var el = this.element.parentNode;
	el.removeChild(this.element);
	Calendar._C = null;
	window._atwal_popupCalendar = null;
};

/**
 *  Moves the calendar element to a different section in the DOM tree (changes
 *  its parent).
 */
Calendar.prototype.reparent = function (new_parent) {
	var el = this.element;
	el.parentNode.removeChild(el);
	new_parent.appendChild(el);
};

// This gets called when the user presses a mouse button anywhere in the
// document, if the calendar is shown.  If the click was outside the open
// calendar this function closes it.
Calendar._checkCalendar = function(ev) {
	var calendar = window._atwal_popupCalendar;
	if (!calendar) {
		return false;
	}
	var el = Calendar.is_ie ? Calendar.getElement(ev) : Calendar.getTargetElement(ev);
	for (; el != null && el != calendar.element; el = el.parentNode);
	if (el == null) {
		// calls closeHandler which should hide the calendar.
		window._atwal_popupCalendar.callCloseHandler();
		return Calendar.stopEvent(ev);
	}
};

/** Shows the calendar. */
Calendar.prototype.show = function () {
	
	var rows = this.table.getElementsByTagName("tr");
	for (var i = rows.length; i > 0;) {
		var row = rows[--i];
		Calendar.removeClass(row, "rowhilite");
		var cells = row.getElementsByTagName("td");
		for (var j = cells.length; j > 0;) {
			var cell = cells[--j];
			Calendar.removeClass(cell, "hilite");
			Calendar.removeClass(cell, "active");
		}
	}
	
 // if(this.params.isMobile){
		// this.element.style.position = null;
		// this.element.style.display = null;
		// this.element.style.left = null;
		// this.element.style.top = null;
	// }else{
	// }
		this.element.style.display = "block";
	this.hidden = false;
	if (this.isPopup) {
		window._atwal_popupCalendar = this;
		Calendar.addEvent(document, "keydown", Calendar._keyEvent);
		Calendar.addEvent(document, "keypress", Calendar._keyEvent);
		Calendar.addEvent(document, "mousedown", Calendar._checkCalendar);
	}
	let fa = "fa fa-calendar-o onselect-datepicker";
	let faMarginTop = 4;
	if (this.showsTime && this.showsDate) {
		this.dateBlock.style.display = null;
		this.clockBlock.style.display = "block";
		this.contentClockBlock.style.display = "none";
	}else if (this.showsTime && !this.showsDate) {
		this.dateBlock.style.display = "none";
		this.clockBlock.style.display = "block";
		this.contentClockBlock.style.display = "block";
		fa = "fa fa-clock-o onselect-datepicker";
		faMarginTop = 5;
	}else if (!this.showsTime && this.showsDate) {
		this.dateBlock.style.display = null;
	}
	this.hideShowCovered();
	parentBtn = this.params.button.parentNode;
	//console.log(parentBtn);
	var signTag = Calendar.createElement("i");
	parentBtn.insertBefore(signTag,this.params.button.nextSibling);
	signTag.className = fa;
	sH = signTag.offsetHeight;
	sw = signTag.offsetWidth;
	t = this.params.button.offsetTop;
	l = this.params.button.offsetLeft;
	x = ((l+this.params.button.offsetWidth))-(sw+15);
	y = (t+((this.params.button.offsetHeight-sH)/2));
	signTag.style.marginLeft = -15+"px";
	signTag.style.marginTop = faMarginTop+"px";
	signTag.style.position = "relative";
	this.signTag = signTag;
};

/**
 *  Hides the calendar.  Also removes any "hilite" from the class of any TD
 *  element.
 */
Calendar.prototype.hide = function () {
	if (this.isPopup) {
		Calendar.removeEvent(document, "keydown", Calendar._keyEvent);
		Calendar.removeEvent(document, "keypress", Calendar._keyEvent);
		Calendar.removeEvent(document, "mousedown", Calendar._checkCalendar);
	}
	//this.element.style.display = "none";
	//this.element.remove();
	this.hidden = true;
	this.hideShowCovered();
	if(this.element.parentNode != null){
		this.element.parentNode.removeChild(this.element);
		if(this.signTag){
			this.signTag.parentNode.removeChild(this.signTag);
		}
	}
};

/**
 *  Shows the calendar at a given absolute position (beware that, depending on
 *  the calendar element style -- position property -- this might be relative
 *  to the parent's containing rectangle).
 */
Calendar.prototype.showAt = function (x, y) {
	var s = this.element.style;
	s.left = x + "px";
	s.top = y + "px";
	this.show();
};

/** Shows the calendar near a given element. */
Calendar.prototype.showAtElement = function (el, opts) {
	var self = this;
	var p = Calendar.getAbsolutePos(el);
	if (!opts || typeof opts != "string") {
		this.showAt(p.x, p.y + el.offsetHeight);
		return true;
	}
	function fixPosition(box) {
		if (box.x < 0)
			box.x = 0;
		if (box.y < 0)
			box.y = 0;
		var cp = document.createElement("div");
		var s = cp.style;
		s.position = "fixed";
		s.right = s.bottom = s.width = s.height = "0px";
		document.body.appendChild(cp);
		var br = Calendar.getAbsolutePos(cp);
		document.body.removeChild(cp);
		if (Calendar.is_ie) {
			br.y += document.body.scrollTop;
			br.x += document.body.scrollLeft;
		} else {
			br.y += window.scrollY;
			br.x += window.scrollX;
		}
		var tmp = box.x + box.width - br.x;
		if (tmp > 0) box.x -= tmp;
		tmp = box.y + box.height - br.y;
		if (tmp > 0) box.y -= tmp;
	};
	//this.element.style.display = "block";
	Calendar.continuation_for_the_fucking_khtml_browser = function() {
		var w = self.element.offsetWidth;
		var h = self.element.offsetHeight;
		self.element.style.display = "none";
		var valign = opts.substr(0, 1);
		var halign = "l";
		if (opts.length > 1) {
			halign = opts.substr(1, 1);
		}
		// vertical alignment
		switch (valign) {
		    case "T": p.y -= h; break;
		    case "B": p.y += el.offsetHeight; break;
		    case "C": p.y += (el.offsetHeight - h) / 2; break;
		    case "t": p.y += el.offsetHeight - h; break;
		    case "b": break; // already there
		}
		// horizontal alignment
		switch (halign) {
		    case "L": p.x -= w; break;
		    case "R": p.x += el.offsetWidth; break;
		    case "C": p.x += (el.offsetWidth - w) / 2; break;
		    case "l": p.x += el.offsetWidth - w; break;
		    case "r": break; // already there
		}
		p.width = w;
		p.height = h + 40;
		self.monthsCombo.style.display = "none";
		fixPosition(p);
		self.showAt(p.x, p.y);
		//console.log(p.x,el.offsetWidth);
	};
	if (Calendar.is_khtml)
		setTimeout("Calendar.continuation_for_the_fucking_khtml_browser()", 10);
	else
		Calendar.continuation_for_the_fucking_khtml_browser();
};

/** Customizes the date format. */
Calendar.prototype.setDateFormat = function (str) {
	this.dateFormat = str;
};

/** Customizes the tooltip date format. */
Calendar.prototype.setTtDateFormat = function (str) {
	this.ttDateFormat = str;
};

/**
 *  Tries to identify the date represented in a string.  If successful it also
 *  calls this.setDate which moves the calendar to the given date.
 */
Calendar.prototype.parseDate = function(str, fmt) {
	if (!fmt)
		fmt = this.dateFormat;
	this.setDate(Date.parseDate(str, fmt));
};

Calendar.prototype.hideShowCovered = function () {
	if (!Calendar.is_ie && !Calendar.is_opera)
		return;
	function getVisib(obj){
		var value = obj.style.visibility;
		if (!value) {
			if (document.defaultView && typeof (document.defaultView.getComputedStyle) == "function") { // Gecko, W3C
				if (!Calendar.is_khtml)
					value = document.defaultView.
						getComputedStyle(obj, "").getPropertyValue("visibility");
				else
					value = '';
			} else if (obj.currentStyle) { // IE
				value = obj.currentStyle.visibility;
			} else
				value = '';
		}
		return value;
	};

	var tags = new Array("applet", "iframe", "select");
	var el = this.element;

	var p = Calendar.getAbsolutePos(el);
	var EX1 = p.x;
	var EX2 = el.offsetWidth + EX1;
	var EY1 = p.y;
	var EY2 = el.offsetHeight + EY1;

	for (var k = tags.length; k > 0; ) {
		var ar = document.getElementsByTagName(tags[--k]);
		var cc = null;

		for (var i = ar.length; i > 0;) {
			cc = ar[--i];

			p = Calendar.getAbsolutePos(cc);
			var CX1 = p.x;
			var CX2 = cc.offsetWidth + CX1;
			var CY1 = p.y;
			var CY2 = cc.offsetHeight + CY1;

			if (this.hidden || (CX1 > EX2) || (CX2 < EX1) || (CY1 > EY2) || (CY2 < EY1)) {
				if (!cc.__msh_save_visibility) {
					cc.__msh_save_visibility = getVisib(cc);
				}
				cc.style.visibility = cc.__msh_save_visibility;
			} else {
				if (!cc.__msh_save_visibility) {
					cc.__msh_save_visibility = getVisib(cc);
				}
				cc.style.visibility = "hidden";
			}
		}
	}
};

/** Internal function; it displays the bar with the names of the weekday. */
Calendar.prototype._displayWeekdays = function () {
	var fdow = this.firstDayOfWeek;
	var cell = this.firstdayname;
	var weekend = Calendar._TT["WEEKEND"];
	for (var i = 0; i < 7; ++i) {
		cell.className = "div-table-col day name";
		var realday = (i + fdow) % 7;
		if (i) {
			cell.ttip = Calendar._TT["DAY_FIRST"].replace("%s", Calendar._DN[realday]);
			cell.navtype = 100;
			cell.calendar = this;
			cell.fdow = realday;
			Calendar._add_evs(cell);
		}
		if (weekend.indexOf(realday.toString()) != -1) {
			Calendar.addClass(cell, "weekend");
		}
		cell.innerHTML = Calendar._SDN[(i + fdow) % 7];
		cell = cell.nextSibling;
	}
};

/** Internal function.  Hides all combo boxes that might be displayed. */
Calendar.prototype._hideCombos = function () {
	this.monthsCombo.style.display = "none";
	this.yearsCombo.style.display = "none";
};

/** Internal function.  Starts dragging the element. */
Calendar.prototype._dragStart = function (ev) {
	if (this.dragging) {
		return;
	}
	this.dragging = true;
	var posX;
	var posY;
	if (Calendar.is_ie) {
		posY = window.event.clientY + document.body.scrollTop;
		posX = window.event.clientX + document.body.scrollLeft;
	} else {
		posY = ev.clientY + window.scrollY;
		posX = ev.clientX + window.scrollX;
	}
	var st = this.element.style;
	this.xOffs = posX - parseInt(st.left);
	this.yOffs = posY - parseInt(st.top);
	with (Calendar) {
		addEvent(document, "mousemove", calDragIt);
		addEvent(document, "mouseup", calDragEnd);
	}
};

// BEGIN: DATE OBJECT PATCHES

/** Adds the number of days array to the Date object. */
Date._MD = new Array(31,28,31,30,31,30,31,31,30,31,30,31);

/** Constants used for time computations */
Date.SECOND = 1000 /* milliseconds */;
Date.MINUTE = 60 * Date.SECOND;
Date.HOUR   = 60 * Date.MINUTE;
Date.DAY    = 24 * Date.HOUR;
Date.WEEK   =  7 * Date.DAY;

Date.parseDate = function(str, fmt) {
	var today = new Date();
	var y = 0;
	var m = -1;
	var d = 0;
	var a = str.split(/\W+/);
	var b = fmt.match(/%./g);
	var i = 0, j = 0;
	var hr = 0;
	var min = 0;
	for (i = 0; i < a.length; ++i) {
		if (!a[i])
			continue;
		switch (b[i]) {
		    case "%d":
		    case "%e":
			d = parseInt(a[i], 10);
			break;

		    case "%m":
			m = parseInt(a[i], 10) - 1;
			break;

		    case "%Y":
		    case "%y":
			y = parseInt(a[i], 10);
			(y < 100) && (y += (y > 29) ? 1900 : 2000);
			break;

		    case "%b":
		    case "%B":
			for (j = 0; j < 12; ++j) {
				if (Calendar._MN[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { m = j; break; }
			}
			break;

		    case "%H":
		    case "%I":
		    case "%k":
		    case "%l":
			hr = parseInt(a[i], 10);
			break;

		    case "%P":
		    case "%p":
			if (/pm/i.test(a[i]) && hr < 12)
				hr += 12;
			else if (/am/i.test(a[i]) && hr >= 12)
				hr -= 12;
			break;

		    case "%M":
			min = parseInt(a[i], 10);
			break;
		}
	}
	if (isNaN(y)) y = today.getFullYear();
	if (isNaN(m)) m = today.getMonth();
	if (isNaN(d)) d = today.getDate();
	if (isNaN(hr)) hr = today.getHours();
	if (isNaN(min)) min = today.getMinutes();
	if (y != 0 && m != -1 && d != 0)
		return new Date(y, m, d, hr, min, 0);
	y = 0; m = -1; d = 0;
	for (i = 0; i < a.length; ++i) {
		if (a[i].search(/[a-zA-Z]+/) != -1) {
			var t = -1;
			for (j = 0; j < 12; ++j) {
				if (Calendar._MN[j].substr(0, a[i].length).toLowerCase() == a[i].toLowerCase()) { t = j; break; }
			}
			if (t != -1) {
				if (m != -1) {
					d = m+1;
				}
				m = t;
			}
		} else if (parseInt(a[i], 10) <= 12 && m == -1) {
			m = a[i]-1;
		} else if (parseInt(a[i], 10) > 31 && y == 0) {
			y = parseInt(a[i], 10);
			(y < 100) && (y += (y > 29) ? 1900 : 2000);
		} else if (d == 0) {
			d = a[i];
		}
	}
	if (y == 0)
		y = today.getFullYear();
	if (m != -1 && d != 0)
		return new Date(y, m, d, hr, min, 0);
	return today;
};

/** Returns the number of days in the current month */
Date.prototype.getMonthDays = function(month) {
	var year = this.getFullYear();
	if (typeof month == "undefined") {
		month = this.getMonth();
	}
	if (((0 == (year%4)) && ( (0 != (year%100)) || (0 == (year%400)))) && month == 1) {
		return 29;
	} else {
		return Date._MD[month];
	}
};

/** Returns the number of day in the year. */
Date.prototype.getDayOfYear = function() {
	var now = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
	var then = new Date(this.getFullYear(), 0, 0, 0, 0, 0);
	var time = now - then;
	return Math.floor(time / Date.DAY);
};

/** Returns the number of the week in year, as defined in ISO 8601. */
Date.prototype.getWeekNumber = function() {
	var d = new Date(this.getFullYear(), this.getMonth(), this.getDate(), 0, 0, 0);
	var DoW = d.getDay();
	d.setDate(d.getDate() - (DoW + 6) % 7 + 3); // Nearest Thu
	var ms = d.valueOf(); // GMT
	d.setMonth(0);
	d.setDate(4); // Thu in Week 1
	return Math.round((ms - d.valueOf()) / (7 * 864e5)) + 1;
};

/** Checks date and time equality */
Date.prototype.equalsTo = function(date) {
	return ((this.getFullYear() == date.getFullYear()) &&
		(this.getMonth() == date.getMonth()) &&
		(this.getDate() == date.getDate()) &&
		(this.getHours() == date.getHours()) &&
		(this.getMinutes() == date.getMinutes()));
};

/** Set only the year, month, date parts (keep existing time) */
Date.prototype.setDateOnly = function(date) {
	var tmp = new Date(date);
	this.setDate(1);
	this.setFullYear(tmp.getFullYear());
	this.setMonth(tmp.getMonth());
	this.setDate(tmp.getDate());
};

/** Prints the date in a string according to the given format. */
Date.prototype.print = function (str) {
	var m = this.getMonth();
	var d = this.getDate();
	var y = this.getFullYear();
	var wn = this.getWeekNumber();
	var w = this.getDay();
	var s = {};
	var hr = this.getHours();
	var pm = (hr >= 12);
	var ir = (pm) ? (hr - 12) : hr;
	var dy = this.getDayOfYear();
	if (ir == 0)
		ir = 12;
	var min = this.getMinutes();
	var sec = this.getSeconds();
	s["%a"] = Calendar._SDN[w]; // abbreviated weekday name [FIXME: I18N]
	s["%A"] = Calendar._DN[w]; // full weekday name
	s["%b"] = Calendar._SMN[m]; // abbreviated month name [FIXME: I18N]
	s["%B"] = Calendar._MN[m]; // full month name
	// FIXME: %c : preferred date and time representation for the current locale
	s["%C"] = 1 + Math.floor(y / 100); // the century number
	s["%d"] = (d < 10) ? ("0" + d) : d; // the day of the month (range 01 to 31)
	s["%e"] = d; // the day of the month (range 1 to 31)
	// FIXME: %D : american date style: %m/%d/%y
	// FIXME: %E, %F, %G, %g, %h (man strftime)
	s["%H"] = (hr < 10) ? ("0" + hr) : hr; // hour, range 00 to 23 (24h format)
	s["%I"] = (ir < 10) ? ("0" + ir) : ir; // hour, range 01 to 12 (12h format)
	s["%j"] = (dy < 100) ? ((dy < 10) ? ("00" + dy) : ("0" + dy)) : dy; // day of the year (range 001 to 366)
    // FIX MAGNOLIA-1290
    s["%k"] = (!hr) ? "00" : hr;// hour, range 0 to 23 (24h format)
    s["%l"] = (!ir) ? '0' : i;// hour, range 1 to 12 (12h format)
	s["%m"] = (m < 9) ? ("0" + (1+m)) : (1+m); // month, range 01 to 12
	s["%M"] = (min < 10) ? ("0" + min) : min; // minute, range 00 to 59
	s["%n"] = "\n";		// a newline character
	s["%p"] = pm ? "PM" : "AM";
	s["%P"] = pm ? "pm" : "am";
	// FIXME: %r : the time in am/pm notation %I:%M:%S %p
	// FIXME: %R : the time in 24-hour notation %H:%M
	s["%s"] = Math.floor(this.getTime() / 1000);
	s["%S"] = (sec < 10) ? ("0" + sec) : sec; // seconds, range 00 to 59
	s["%t"] = "\t";		// a tab character
	// FIXME: %T : the time in 24-hour notation (%H:%M:%S)
	s["%U"] = s["%W"] = s["%V"] = (wn < 10) ? ("0" + wn) : wn;
	s["%u"] = w + 1;	// the day of the week (range 1 to 7, 1 = MON)
	s["%w"] = w;		// the day of the week (range 0 to 6, 0 = SUN)
	// FIXME: %x : preferred date representation for the current locale without the time
	// FIXME: %X : preferred time representation for the current locale without the date
	s["%y"] = ('' + y).substr(2, 2); // year without the century (range 00 to 99)
	s["%Y"] = y;		// year with the century
	s["%%"] = "%";		// a literal '%' character

	var re = /%./g;
	if (!Calendar.is_ie5 && !Calendar.is_khtml)
		return str.replace(re, function (par) { return s[par] || par; });

	var a = str.match(re);
	for (var i = 0; i < a.length; i++) {
		var tmp = s[a[i]];
		if (tmp) {
			re = new RegExp(a[i], 'g');
			str = str.replace(re, tmp);
		}
	}

	return str;
};

Date.prototype.__msh_oldSetFullYear = Date.prototype.setFullYear;
Date.prototype.setFullYear = function(y) {
	var d = new Date(this);
	d.__msh_oldSetFullYear(y);
	if (d.getMonth() != this.getMonth())
		this.setDate(28);
	this.__msh_oldSetFullYear(y);
};

// END: DATE OBJECT PATCHES


// global object that remembers the calendar
window._atwal_popupCalendar = null;