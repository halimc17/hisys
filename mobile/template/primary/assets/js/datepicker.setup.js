/*  Copyright Mihai Bazon, 2002, 2003  |  http://dynarch.com/mishoo/
 * ---------------------------------------------------------------------------
 *
 * The DHTML Calendar
 *
 * Details and latest version at:
 * http://dynarch.com/mishoo/calendar.epl
 *
 * This script is distributed under the GNU Lesser General Public License.
 * Read the entire license text here: http://www.gnu.org/licenses/lgpl.html
 *
 * This file defines helper functions for setting up the calendar.  They are
 * intended to help non-programmers get a working calendar on their site
 * quickly.  This script should not be seen as part of the calendar.  It just
 * shows you what one can do with the calendar, while in the same time
 * providing a quick and simple method for setting it up.  If you need
 * exhaustive customization of the calendar creation process feel free to
 * modify this code to suit your needs (this is recommended and much better
 * than modifying calendar.js itself).
 */

// $Id$

/**
 *  This function "patches" an input field (or other element) to use a calendar
 *  widget for date selection.
 *
 *  The "params" is a single object that can have the following properties:
 *
 *    prop. name   | description
 *  -------------------------------------------------------------------------------------------------
 *   inputField    | the ID of an input field to store the date
 *   displayArea   | the ID of a DIV or other element to show the date
 *   button        | ID of a button or other element that will trigger the calendar
 *   eventName     | event that will trigger the calendar, without the "on" prefix (default: "click")
 *   ifFormat      | date format that will be stored in the input field
 *   daFormat      | the date format that will be used to display the date in displayArea
 *   singleClick   | (true/false) wether the calendar is in single click mode or not (default: true)
 *   firstDay      | numeric: 0 to 6.  "0" means display Sunday first, "1" means display Monday first, etc.
 *   align         | alignment (default: "Br"); if you don't know what's this see the calendar documentation
 *   range         | array with 2 elements.  Default: [1900, 2999] -- the range of years available
 *   weekNumbers   | (true/false) if it's true (default) the calendar will display week numbers
 *   flat          | null or element ID; if not null the calendar will be a flat calendar having the parent with the given ID
 *   flatCallback  | function that receives a JS Date object and returns an URL to point the browser to (for flat calendar)
 *   disableFunc   | function that receives a JS Date object and should return true if that date has to be disabled in the calendar
 *   onSelect      | function that gets called when a date is selected.  You don't _have_ to supply this (the default is generally okay)
 *   onClose       | function that gets called when the calendar is closed.  [default]
 *   onUpdate      | function that gets called after the date is updated in the input field.  Receives a reference to the calendar.
 *   date          | the date that the calendar will be initially displayed to
 *   showsTime     | default: false; if true the calendar will include a time selector
 *   timeFormat    | the time format; can be "12" or "24", default is "12"
 *   electric      | if true (default) then given fields/date areas are updated for each move; otherwise they're updated only on close
 *   step          | configures the step of the years in drop-down boxes; default: 2
 *   position      | configures the calendar absolute position; default: null
 *   cache         | if "true" (but default: "false") it will reuse the same calendar object, where possible
 *   showOthers    | if "true" (but default: "false") it will show days from other months too
 *
 *  None of them is required, they all have default values.  However, if you
 *  pass none of "inputField", "displayArea" or "button" you'll get a warning
 *  saying "nothing to setup".
 */
Calendar.setup = function (params) {
	//console.log(params);
	function param_default(pname, def) { 
		if (typeof params[pname] == "undefined"){ 
			params[pname] = def; 
		} 
	};

	param_default("inputField",     null);
	param_default("displayArea",    null);
	param_default("button",         null);
	param_default("eventName",      "click");
	param_default("ifFormat",       "%d-%m-%Y");
	param_default("daFormat",       "%d/%m/%Y");
	param_default("singleClick",    true);
	param_default("disableFunc",    null);
	param_default("dateStatusFunc", params["disableFunc"]);	// takes precedence if both are defined
	param_default("dateText",       null);
	param_default("firstDay",       null);
	param_default("align",          "Br");
	param_default("range",          [1900, 2999]);
	param_default("weekNumbers",    false);
	param_default("flat",           null);
	param_default("flatCallback",   null);
	param_default("onSelect",       null);
	param_default("onClose",        null);
	param_default("onUpdate",       null);
	param_default("date",           null);
	param_default("showsTime",      false);
	param_default("showsDate",      true);
	param_default("timeFormat",     "24");
	param_default("electric",       true);
	param_default("step",           2);
	param_default("position",       null);
	param_default("cache",          false);
	param_default("showOthers",     false);
	param_default("multiple",       null);
	param_default("widthMaxMobile",670);
	param_default("isMobile",      false);
	
	var tmp = ["inputField", "displayArea", "button"];
	for (var i in tmp) {
		if (typeof params[tmp[i]] == "string") {
			params[tmp[i]] = document.getElementById(params[tmp[i]]);
		}
	}
	if(isMobile.any()){
		params.isMobile = true;
	}
	if(document.documentElement.clientWidth < params.widthMaxMobile){
		params.isMobile = true;
	}
	
	if (!(params.flat || params.multiple || params.inputField || params.displayArea || params.button)) {
		alert("Calendar.setup:\n  Nothing to setup (no fields found).  Please check your code");
		return false;
	}

	function onSelect(cal) {
		var p = cal.params;
		var update = (cal.dateClicked || p.electric);
		if (update && p.inputField) {
			p.inputField.value = cal.date.print(p.ifFormat);
			if (typeof p.inputField.onchange == "function")
				p.inputField.onchange();
		}
		if (update && p.displayArea)
			p.displayArea.innerHTML = cal.date.print(p.daFormat);
		if (update && typeof p.onUpdate == "function")
			p.onUpdate(cal);
		if (update && p.flat) {
			if (typeof p.flatCallback == "function")
				p.flatCallback(cal);
		}
		if (update && p.singleClick && cal.dateClicked)
			cal.callCloseHandler();
	};

	if (params.flat != null) {
		if (typeof params.flat == "string")
			params.flat = document.getElementById(params.flat);
		if (!params.flat) {
			alert("Calendar.setup:\n  Flat specified but can't find parent.");
			return false;
		}
		var cal = new Calendar(params.firstDay, params.date, params.onSelect || onSelect);
		cal.showsOtherMonths = params.showOthers;
		cal.showsDate = params.showsDate;
		cal.showsTime = params.showsTime;
		cal.time24 = (params.timeFormat == "24");
		cal.params = params;
		cal.weekNumbers = params.weekNumbers;
		cal.setRange(params.range[0], params.range[1]);
		cal.setDateStatusHandler(params.dateStatusFunc);
		cal.getDateText = params.dateText;
		if (params.ifFormat) {
			cal.setDateFormat(params.ifFormat);
		}
		if (params.inputField && typeof params.inputField.value == "string") {
			cal.parseDate(params.inputField.value);
		}
		cal.create(params.flat);
		cal.show();
		return false;
	}

	var triggerEl = params.button || params.displayArea || params.inputField;
	triggerEl["on" + params.eventName] = function() {
		var dateEl = params.inputField || params.displayArea;
		var dateFmt = params.inputField ? params.ifFormat : params.daFormat;
		var mustCreate = false;
		var cal = window.calendar;
		if (dateEl)
			params.date = Date.parseDate(dateEl.value || dateEl.innerHTML, dateFmt);
		if (!(cal && params.cache)) {
			window.calendar = cal = new Calendar(params.firstDay,
							     params.date,
							     params.onSelect || onSelect,
							     params.onClose || function(cal) { cal.hide(); });
			cal.showsDate = params.showsDate;
			cal.showsTime = params.showsTime;
			cal.time24 = (params.timeFormat == "24");
			cal.weekNumbers = params.weekNumbers;
			mustCreate = true;
		} else {
			if (params.date)
				cal.setDate(params.date);
			cal.hide();
		}
		if (params.multiple) {
			cal.multiple = {};
			for (var i = params.multiple.length; --i >= 0;) {
				var d = params.multiple[i];
				var ds = d.print("%Y%m%d");
				cal.multiple[ds] = d;
			}
		}
		cal.showsOtherMonths = params.showOthers;
		cal.yearStep = params.step;
		cal.setRange(params.range[0], params.range[1]);
		cal.params = params;
		cal.setDateStatusHandler(params.dateStatusFunc);
		cal.getDateText = params.dateText;
		cal.setDateFormat(dateFmt);
		if (mustCreate)
			cal.create();
		cal.refresh();
		if (!params.position)
			cal.showAtElement(params.button || params.displayArea || params.inputField, params.align);
		else
			cal.showAt(params.position[0], params.position[1]);
		return false;
	};

	return cal;
};

//=======================================================================
//========================SETTING========================================
Calendar._DN = new Array
("Minggu",
 "Senin",
 "Selasa",
 "Rabu",
 "Kamis",
 "Jumat",
 "Sabtu",
 "Minggu");

// Please note that the following array of short day names (and the same goes
// for short month names, _SMN) isn't absolutely necessary.  We give it here
// for exemplification on how one can customize the short day names, but if
// they are simply the first N letters of the full name you can simply say:
//
//   Calendar._SDN_len = N; // short day name length
//   Calendar._SMN_len = N; // short month name length
//
// If N = 3 then this is not needed either since we assume a value of 3 if not
// present, to be compatible with translation files that were written before
// this feature.

// short day names
Calendar._SDN = new Array
("Min",
 "Sen",
 "Sel",
 "Rab",
 "Kam",
 "Jum",
 "Sab",
 "Min");

// First day of the week. "0" means display Sunday first, "1" means display
// Monday first, etc.
Calendar._FD = 0;

// full month names
Calendar._MN = new Array
("Januari",
 "Februari",
 "Maret",
 "April",
 "Mei",
 "Juni",
 "Juli",
 "Agustus",
 "September",
 "Oktober",
 "Nopember",
 "Desember");

// short month names
Calendar._SMN = new Array
("Jan",
 "Feb",
 "Mar",
 "Apr",
 "Mei",
 "Jun",
 "Jul",
 "Agu",
 "Sep",
 "Okt",
 "Nop",
 "Des");

// tooltips
Calendar._TT = {};
Calendar._TT["INFO"] = "Tentang Kalender";

Calendar._TT["ABOUT"] =
"DHTML Selector:\n" +
"Hubungi nangkoel@gmail.com\n";

Calendar._TT["PREV_YEAR"] = "Thn Lalu (Tekan untuk menu)";
Calendar._TT["PREV_MONTH"] = "Bln Lalu (Tekan untuk menu)";
Calendar._TT["GO_TODAY"] = "Hari Ini";
Calendar._TT["NEXT_MONTH"] = "Bln Depan (Tekan untuk menu)";
Calendar._TT["NEXT_YEAR"] = "Thn Depan (Tekan untuk menu)";
Calendar._TT["SEL_DATE"] = "Pilih Tanggal";
Calendar._TT["DRAG_TO_MOVE"] = "Seret";
Calendar._TT["PART_TODAY"] = " (Hari Ini)";

// the following is to inform that "%s" is to be the first day of week
// %s will be replaced with the day name.
Calendar._TT["DAY_FIRST"] = "Tampilkan %s di Depan";

// This may be locale-dependent.  It specifies the week-end days, as an array
// of comma-separated numbers.  The numbers are from 0 to 6: 0 means Sunday, 1
// means Monday, etc.
Calendar._TT["WEEKEND"] = "0,6";

Calendar._TT["CLOSE"] = "Tutup";
Calendar._TT["TODAY"] = "Hari Ini";
Calendar._TT["TIME_PART"] = "(Shift-)Click atau Seret untuk menggati nilai";

// date formats
Calendar._TT["DEF_DATE_FORMAT"] = "%d-%m-%Y";
Calendar._TT["TT_DATE_FORMAT"] = "%a, %b %e";
Calendar._TT["TT_TIME_FORMAT"] = "%H:%M:%I";

Calendar._TT["WK"] = "Mgg";
Calendar._TT["TIME"] = "Jam:";

// icon
Calendar._TT["CLOSEICON"]="fa fa-times";
Calendar._TT["NEXT_YEARICON"]="fa fa-angle-double-right";
Calendar._TT["PREV_YEARICON"]="fa fa-angle-double-left";
Calendar._TT["PREV_MONTHICON"]="fa fa-angle-left";
Calendar._TT["NEXT_MONTHICON"]="fa fa-angle-right";


//setup 
var datePrickerOWL = [];
function setCalendar(ele,format,i){
	if(typeof i == "undefined"){
		i = 0;
	}
	if(typeof format == "undefined"){
		format = "%d-%m-%Y";
	}
	if(typeof ele == "string"){
		ele = document.getElementById(ele);
	}
	if(!$.getElementById(ele).getAttribute('autocomplete')){
		ele = $.getElementById(ele);
		ele.setAttribute("autocomplete","off");
	}
	
	var len = datePrickerOWL.length;
	newNum = (len+i);
	var dateFormat = ["%a","%A","%b","%B","%C","%d","%e","%y","%Y","%m"];
	var timeFormat = ["%H","%I","%j","%k","%l","%n","%p","%P","%s","%S","%t","%M"];
	var showsDateFlag = false;
	var showsTimeFlag = false;
	for(i=0; i<dateFormat.length; i++){
		n = format.search(dateFormat[i]);
		if(n>=0){
			showsDateFlag = true;
			break;
		}
	}
	for(i=0; i<timeFormat.length; i++){
		n = format.search(timeFormat[i]);
		if(n>=0){
			showsTimeFlag = true;
			break;
		}
	}
	if(!showsDateFlag && !showsTimeFlag){
		showsDateFlag = true;
	}
	
	datePrickerOWL[newNum] = new Calendar.setup({
		inputField     :    ele,	// id of the input field
		ifFormat       :    format,	// format of the input field
		showsDate      :    showsDateFlag,	// will display a date selector
		showsTime      :    showsTimeFlag,	// will display a time selector
		button         :    ele,	// trigger for the calendar (button ID)
		singleClick    :    true,	// double-click mode
		step           :    1		// show all years in drop-down boxes (instead of every other year as default)
	});
}