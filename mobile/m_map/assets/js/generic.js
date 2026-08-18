/**
 * @author repindra.ginting
 */
//platform ==================================
var scrHeight=screen.availHeight;
var scrWidth=screen.availWidth;
var platform=navigator.platform;
//****************************************

var isIE = document.all?true:false; 
var tanggalsekarang = new Date();
var dd = String(tanggalsekarang.getDate()).padStart(2, '0');
var mm = String(tanggalsekarang.getMonth() + 1).padStart(2, '0'); //January is 0!
var yyyy = tanggalsekarang.getFullYear();

tanggalsekarang = dd + '-' + mm + '-' + yyyy;

function getMouseP(e) {// e is event
//this work when calling from function or html object
	var tempX = 0;
	var tempY = 0;
		if (isIE) { // grab the x-y pos.s if browser is IE
			var ScrollTop = (document.body.parentElement) ? document.body.parentElement.scrollTop:document.body.scrollTop;
			var ScrollLeft = (document.body.parentElement) ? document.body.parentElement.scrollLeft:document.body.scrollLeft;
			tempX = ScrollTop+150;
			tempY = ScrollLeft+150;			
		}
		else {  // grab the x-y pos.s if browser is NS
			tempX = e.pageX;
			tempY = e.pageY;
		}
  
	if (tempX < 0){tempX = 0;}
	if (tempY < 0){tempY = 0;}  
	arr= new Array();
	arr[0]=tempX;
	arr[1]= tempY;
	return arr; //arr[0]= x coord arr[1]=y coord
}

function getMousePDefault(e) {// e is event
//this is uses when position directly accessed from html element
//if you call this function from other function, IE browser will not work
//if you want this posible calling through function use getMouseP above
	var tempX = 0;
	var tempY = 0;
		if (isIE) { // grab the x-y pos.s if browser is IE
			try {
				tempX = ev.clientX + document.documentElement.scrollLeft;
				tempY = ev.clientY + document.documentElement.scrollTop;				
			} 
			catch (e) {		
				tempX = ev.clientX + document.body.scrollLeft;
				tempY =ev.clientY + document.body.scrollTop;	
              }
		}
		else {  // grab the x-y pos.s if browser is NS
			tempX = e.pageX;
			tempY = e.pageY;
		}
	if (tempX < 0){tempX = 0;}
	if (tempY < 0){tempY = 0;}  
	arr= new Array();
	arr[0]=tempX;
	arr[1]= tempY;
	return arr; //arr[0]= x coord arr[1]=y coord
}
//************************************************
function docHeight(){
  if(isIE)
  return(document.body.offsetHeight);
  else	
   return (document.height);
}
function docWidth(){
  if(isIE)
  return(document.body.offsetWidth);
  else	
   return (document.width);
}
//===============================================
function setOpacity(id, opacStart, opacEnd, sec) {//id=element id, opacstart= integer opacity start,sec =integer represent total time
    //speed for each frame
	millisec=sec*1000;
    var speed = Math.round(millisec / 100);
    var timer = 0;

    //determine the direction for the blending, if start and end are the same nothing happens
    if(opacStart > opacEnd) {
        for(i = opacStart; i >= opacEnd; i--) {
            t=setTimeout("changeOpac(" + i + ",'" + id + "')",(timer * speed));
            timer++;
			if(i==opacEnd)
			  clearTimeout(t);			
        }
    } else if(opacStart < opacEnd) {
        for(i = opacStart; i <= opacEnd; i++)
            {
            t=setTimeout("changeOpac(" + i + ",'" + id + "')",(timer * speed));
            timer++;
			if(i==opacEnd)
			  clearTimeout(t);
        }
    }
}

//change the opacity for different browsers
function changeOpac(opacity, id) {
	var object = document.getElementById(id).style;
    object.opacity = (opacity / 100);
    object.MozOpacity = (opacity / 100);
    object.KhtmlOpacity = (opacity / 100);
    object.filter = "alpha(opacity=" + opacity + ")";
} 
//**************************************************************
function getImgSize(imgSrc)
{
imgSrc=document.getElementById(imgSrc).src;
var newImg = new Image();
newImg.src = imgSrc;
var height = newImg.height;
var width = newImg.width;
     this.x=function(){
	  return width;
	 }
     this.y=function(){
	  return height;
	 }
//here to use
//function imgSize(d){
//s= new getImgSize(d);
//test=s.y();
//alert(test);
//}
}
//******************************************************
function chg_color(obj,tocolor)//chage object background color
{
	obj.style.backgroundColor=tocolor;
}
//================================================================
function busy_on()//set busy on
{
	document.getElementById('progress').style.display='';//you must have object with id=progress on your documents
	document.body.style.cursor='wait';
}

function busy_off()//set busy off
{
	document.getElementById('progress').style.display='none';//you must have object with id=progress on your documents
	document.body.style.cursor='default';
}
//===================================================================
function disable_on(objtodisable)//Disable Object
{
	objtodisable.disabled=true;
}
function disable_off(objtodisable)//Enable Object
{
	objtodisable.disabled=false;
}

//=============================================================

function createXMLHttpRequest() {
   try { return new ActiveXObject("Msxml2.XMLHTTP"); } 
   catch (e) {}
   try { return new ActiveXObject("Microsoft.XMLHTTP"); } 
   catch (e) {}
   try { return new XMLHttpRequest(); } 
   catch(e) {}
   alert("XMLHttpRequest Tidak didukung oleh browser");
   return null;
 }

 var con = createXMLHttpRequest();


function get_reponse_text(tujuan,funct)
{
	busy_on();
                    zz=verify();
                       if(zz){       
                            par=parent.location.href.replace("http://","");
                            par=par.replace("https://","");
                            tujuan+='&par='+par;                           
                           con.open("GET",tujuan,true);
                           con.onreadystatechange= eval(funct);
                           con.send(null);
                       }
                       else
                           window.location='logout.php';
}
function post_response_text(tujuan,param,functiontoexecute)
{
busy_on();
// zz=verify();
    // if(zz){
        par=parent.location.href.replace("http://","");
        par=par.replace("https://","");
        param+='&par='+par;
        con.open("POST", tujuan, true);
        con.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        //con.setRequestHeader("Content-length", param.length);
        //con.setRequestHeader("Connection", "close");
        con.onreadystatechange = eval(functiontoexecute);
        con.send(param);
    // }
    // else
        // window.location='logout.php';
}
function error_catch(x)
{
	switch (x){
      case 203:
		alert('Dibutuhkan Authority');
	  break;
	  case 400:
		alert('Error Server');
	  break;
	  case 403:
		alert('Anda dilarang masuk');
	  break;
	  case 404:
		alert('File tidak ditemukan');
	  break;
	  case 405:
		alert('Method tidak diijinkan');
	  break;
	  case 407:
		alert('Proxy Error');
	  break;
	  case 408:
		alert('Permintaan terlalu lama');
	  break;
	  case 409:
		alert('Query Conflict');
	  break;
	  case 414:
		alert('ULI terlalu panjang');
	  break;
	  case 412:
		alert('Variable terlalu banyak');
	  break;
	  case 415:
		alert('Unsupported Media Type');
	  break;
	  case 500:
		alert('Server busy, try submit later');
	  break;
	  case 502:
		alert('Bad gateway');
	  break;
	  case 505:
		alert('Browser anda terlalu tua');	    
      break;
	  
}
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

//==============================================================
/*disable rightclick on document
var message='';
function clickIE() 
{
	if (document.all) 
	{
		(message);
		return false;
	}
}
function clickNS(e) 
{
	if (document.layers||(document.getElementById&&!document.all)) 
	{
		if (e.which==2||e.which==3) 
			{
				(message);
				return false;
				}
			}
		}
if (document.layers) 
	{
		document.captureEvents(Event.MOUSEDOWN);
		document.onmousedown=clickNS;
	}
else
{
	document.onmouseup=clickNS;//penggunaan ini bisa bentrok dengan drag
	document.oncontextmenu=clickIE;
	}
document.oncontextmenu=new Function('return false')
*/
//==========================================================
function disable_paste(e) //disable ctrl+v
{
        var forbiddenKeys = new Array('v');
        var key;
        var isCtrl;

        if(window.event)
        {
                key = window.event.keyCode;     //IE
                if(window.event.ctrlKey)
                        isCtrl = true;
                else
                        isCtrl = false;
        }
        else
        {
                key = e.which;     //firefox
                if(e.ctrlKey)
                        isCtrl = true;
                else
                        isCtrl = false;
        }
        if(isCtrl)
        {
                for(i=0; i<forbiddenKeys.length; i++)
                {
                        if(forbiddenKeys[i].toLowerCase() == String.fromCharCode(key).toLowerCase())
                        {
                                return false;
                        }
                }
        }
        return true;
}
//====================================================
function trim(stringToTrim){//trim space not support by IE
    retval=stringToTrim.replace(/^\s+|\s+$/g, "");
	return (retval);
}
//========================================================
//Numbering format
//use _formatted(x,y)     //x is string eg._formatted('123')
						  //y is decimal eg _formatted(2342.234)
						  //y or x can be blank from caller source
						  
function NumberFormat(num, inputDecimal)
{
	// constants
	this.COMMA = ',';
	this.PERIOD = '.';
	this.DASH = '-'; // v1.5.0 - new - used internally
	this.LEFT_PAREN = '('; // v1.5.0 - new - used internally
	this.RIGHT_PAREN = ')'; // v1.5.0 - new - used internally
	this.LEFT_OUTSIDE = 0; // v1.5.0 - new - currency
	this.LEFT_INSIDE = 1;  // v1.5.0 - new - currency
	this.RIGHT_INSIDE = 2;  // v1.5.0 - new - currency
	this.RIGHT_OUTSIDE = 3;  // v1.5.0 - new - currency
	this.LEFT_DASH = 0; // v1.5.0 - new - negative
	this.RIGHT_DASH = 1; // v1.5.0 - new - negative
	this.PARENTHESIS = 2; // v1.5.0 - new - negative
	this.NO_ROUNDING = -1 // v1.5.1 - new

	// member variables
	this.num;
	this.numOriginal;
	this.hasSeparators = false;  // v1.5.0 - new
	this.separatorValue;  // v1.5.0 - new
	this.inputDecimalValue; // v1.5.0 - new
	this.decimalValue;  // v1.5.0 - new
	this.negativeFormat; // v1.5.0 - new
	this.negativeRed; // v1.5.0 - new
	this.hasCurrency;  // v1.5.0 - modified
	this.currencyPosition;  // v1.5.0 - new
	this.currencyValue;  // v1.5.0 - modified
	this.places;
	this.roundToPlaces; // v1.5.1 - new

	// external methods
	this.setNumber = setNumberNF;
	this.toUnformatted = toUnformattedNF;
	this.setInputDecimal = setInputDecimalNF; // v1.5.0 - new
	this.setSeparators = setSeparatorsNF; // v1.5.0 - new - for separators and decimals
	this.setCommas = setCommasNF;
	this.setNegativeFormat = setNegativeFormatNF; // v1.5.0 - new
	this.setNegativeRed = setNegativeRedNF; // v1.5.0 - new
	this.setCurrency = setCurrencyNF;
	this.setCurrencyPrefix = setCurrencyPrefixNF;
	this.setCurrencyValue = setCurrencyValueNF; // v1.5.0 - new - setCurrencyPrefix uses this
	this.setCurrencyPosition = setCurrencyPositionNF; // v1.5.0 - new - setCurrencyPrefix uses this
	this.setPlaces = setPlacesNF;
	this.toFormatted = toFormattedNF;
	this.toPercentage = toPercentageNF;
	this.getOriginal = getOriginalNF;
	this.moveDecimalRight = moveDecimalRightNF;
	this.moveDecimalLeft = moveDecimalLeftNF;

	// internal methods
	this.getRounded = getRoundedNF;
	this.preserveZeros = preserveZerosNF;
	this.justNumber = justNumberNF;
	this.expandExponential = expandExponentialNF;
	this.getZeros = getZerosNF;
	this.moveDecimalAsString = moveDecimalAsStringNF;
	this.moveDecimal = moveDecimalNF;

	// setup defaults
	if (inputDecimal == null) {
		this.setNumber(num, this.PERIOD);
	} else {
		this.setNumber(num, inputDecimal); // v.1.5.1 - new
	}
	this.setCommas(true);
	this.setNegativeFormat(this.LEFT_DASH); // v1.5.0 - new
	this.setNegativeRed(false); // v1.5.0 - new
	this.setCurrency(false); // v1.5.1 - false by default
	this.setCurrencyPrefix('$');
	this.setPlaces(2);
}

/*
 * setInputDecimal
 * val - The decimal value for the input.
 *
 * v1.5.0 - new
 */
function setInputDecimalNF(val)
{
	this.inputDecimalValue = val;
}
function setNumberNF(num, inputDecimal)
{
	if (inputDecimal != null) {
		this.setInputDecimal(inputDecimal); // v.1.5.1 - new
	}
	
	this.numOriginal = num;
	this.num = this.justNumber(num);
}

function toUnformattedNF()
{
	return (this.num);
}

function getOriginalNF()
{
	return (this.numOriginal);
}


function setNegativeFormatNF(format)
{
	this.negativeFormat = format;
}

function setNegativeRedNF(isRed)
{
	this.negativeRed = isRed;
}

function setSeparatorsNF(isC, separator, decimal)
{
	this.hasSeparators = isC;
	
	// Make sure a separator was passed in
	if (separator == null) separator = this.COMMA;
	
	// Make sure a decimal was passed in
	if (decimal == null) decimal = this.PERIOD;
	
	if (separator == decimal) {
		this.decimalValue = (decimal == this.PERIOD) ? this.COMMA : this.PERIOD;
	} else {
		this.decimalValue = decimal;
	}
	
	this.separatorValue = separator;
}


function setCommasNF(isC)
{
	this.setSeparators(isC, this.COMMA, this.PERIOD);
}

function setCurrencyNF(isC)
{
	this.hasCurrency = isC;
}


function setCurrencyValueNF(val)
{
	this.currencyValue = val;
}


function setCurrencyPrefixNF(cp)
{
	this.setCurrencyValue(cp);
	this.setCurrencyPosition(this.LEFT_OUTSIDE);
}


function setCurrencyPositionNF(cp)
{
	this.currencyPosition = cp
}


function setPlacesNF(p)
{
	this.roundToPlaces = !(p == this.NO_ROUNDING); // v1.5.1
	this.places = (p < 0) ? 0 : p; // v1.5.1 - Don't leave negatives.
}


function toFormattedNF()
{	
	var pos;
	var nNum = this.num; // v1.0.1 - number as a number
	var nStr;            // v1.0.1 - number as a string
	var splitString = new Array(2);   // v1.5.0
	
	// round decimal places - modified v1.5.1
	// Note: Take away negative temporarily with Math.abs
	if (this.roundToPlaces) {
		nNum = this.getRounded(nNum);
		nStr = this.preserveZeros(Math.abs(nNum)); // this step makes nNum into a string. v1.0.1 Math.abs
	} else {
		nStr = this.expandExponential(Math.abs(nNum)); // expandExponential is called in preserveZeros, so call it here too
	}

	// the separator and decimal values have to be different
	// this is enforced in justNumber
	if (nStr.indexOf(this.PERIOD) == -1) {
		splitString[0] = nStr;
		splitString[1] = '';
	} else {
		splitString = nStr.split(this.PERIOD, 2);
	}

	// separators
	if (this.hasSeparators) {
		pos = splitString[0].length;
		while (pos > 0) {
			pos -= 3;
			if (pos <= 0) break;

			splitString[0] = splitString[0].substring(0,pos)
				+ this.separatorValue
				+ splitString[0].substring(pos, splitString[0].length);
		}
	}
	
	// decimal
	if (splitString[1].length > 0) {
		nStr = splitString[0] + this.decimalValue + splitString[1];
	} else {
		nStr = splitString[0];
	}
	
	// negative and currency
	// $[c0] -[n0] $[c1] -[n1] #.#[nStr] -[n2] $[c2] -[n3] $[c3]
	var c0 = '';
	var n0 = '';
	var c1 = '';
	var n1 = '';
	var n2 = '';
	var c2 = '';
	var n3 = '';
	var c3 = '';
	var negSignL = (this.negativeFormat == this.PARENTHESIS) ? this.LEFT_PAREN : this.DASH;
	var negSignR = (this.negativeFormat == this.PARENTHESIS) ? this.RIGHT_PAREN : this.DASH;
		
	if (this.currencyPosition == this.LEFT_OUTSIDE) {
		// add currency sign in front, outside of any negative. example: $-1.00	
		if (nNum < 0) {
			if (this.negativeFormat == this.LEFT_DASH || this.negativeFormat == this.PARENTHESIS) n1 = negSignL;
			if (this.negativeFormat == this.RIGHT_DASH || this.negativeFormat == this.PARENTHESIS) n2 = negSignR;
		}
		if (this.hasCurrency) c0 = this.currencyValue;
	} else if (this.currencyPosition == this.LEFT_INSIDE) {
		// add currency sign in front, inside of any negative. example: -$1.00
		if (nNum < 0) {
			if (this.negativeFormat == this.LEFT_DASH || this.negativeFormat == this.PARENTHESIS) n0 = negSignL;
			if (this.negativeFormat == this.RIGHT_DASH || this.negativeFormat == this.PARENTHESIS) n3 = negSignR;
		}
		if (this.hasCurrency) c1 = this.currencyValue;
	}
	else if (this.currencyPosition == this.RIGHT_INSIDE) {
		// add currency sign at the end, inside of any negative. example: 1.00$-
		if (nNum < 0) {
			if (this.negativeFormat == this.LEFT_DASH || this.negativeFormat == this.PARENTHESIS) n0 = negSignL;
			if (this.negativeFormat == this.RIGHT_DASH || this.negativeFormat == this.PARENTHESIS) n3 = negSignR;
		}
		if (this.hasCurrency) c2 = this.currencyValue;
	}
	else if (this.currencyPosition == this.RIGHT_OUTSIDE) {
		// add currency sign at the end, outside of any negative. example: 1.00-$
		if (nNum < 0) {
			if (this.negativeFormat == this.LEFT_DASH || this.negativeFormat == this.PARENTHESIS) n1 = negSignL;
			if (this.negativeFormat == this.RIGHT_DASH || this.negativeFormat == this.PARENTHESIS) n2 = negSignR;
		}
		if (this.hasCurrency) c3 = this.currencyValue;
	}

	nStr = c0 + n0 + c1 + n1 + nStr + n2 + c2 + n3 + c3;
	
	// negative red
	if (this.negativeRed && nNum < 0) {
		nStr = '<font color="red">' + nStr + '</font>';
	}

	return (nStr);
}


function toPercentageNF()
{
	nNum = this.num * 100;
	
	// round decimal places
	nNum = this.getRounded(nNum);
	
	return nNum + '%';
}


function getZerosNF(places)
{
		var extraZ = '';
		var i;
		for (i=0; i<places; i++) {
			extraZ += '0';
		}
		return extraZ;
}


function expandExponentialNF(origVal)
{
	if (isNaN(origVal)) return origVal;
	
	var newVal = parseFloat(origVal) + ''; // parseFloat to let JavaScript evaluate number
	var eLoc = newVal.toLowerCase().indexOf('e');

	if (eLoc != -1) {
		var plusLoc = newVal.toLowerCase().indexOf('+');
		var negLoc = newVal.toLowerCase().indexOf('-', eLoc); // search for - after the e
		var justNumber = newVal.substring(0, eLoc);
		
		if (negLoc != -1) {
			// shift decimal to the left
			var places = newVal.substring(negLoc + 1, newVal.length);
			justNumber = this.moveDecimalAsString(justNumber, true, parseInt(places));
		} else {
			// shift decimal to the right
			// Check if there's a plus sign, and if not refer to where the e is.
			// This is to account for either formatting 1e21 or 1e+21
			if (plusLoc == -1) plusLoc = eLoc;
			var places = newVal.substring(plusLoc + 1, newVal.length);
			justNumber = this.moveDecimalAsString(justNumber, false, parseInt(places));
		}
		
		newVal = justNumber;
	}

	return newVal;
} 


function moveDecimalRightNF(val, places)
{
	var newVal = '';
	
	if (places == null) {
		newVal = this.moveDecimal(val, false);
	} else {
		newVal = this.moveDecimal(val, false, places);
	}
	
	return newVal;
}

function moveDecimalLeftNF(val, places)
{
	var newVal = '';
	
	if (places == null) {
		newVal = this.moveDecimal(val, true);
	} else {
		newVal = this.moveDecimal(val, true, places);
	}
	
	return newVal;
}


function moveDecimalAsStringNF(val, left, places)
{
	var spaces = (arguments.length < 3) ? this.places : places;
	if (spaces <= 0) return val; // to avoid Mozilla limitation
			
	var newVal = val + '';
	var extraZ = this.getZeros(spaces);
	var re1 = new RegExp('([0-9.]+)');
	if (left) {
		newVal = newVal.replace(re1, extraZ + '$1');
		var re2 = new RegExp('(-?)([0-9]*)([0-9]{' + spaces + '})(\\.?)');		
		newVal = newVal.replace(re2, '$1$2.$3');
	} else {
		if (re1.test(newVal)) {
			newVal = RegExp.leftContext + RegExp.$1 + extraZ + RegExp.rightContext;
		}
		var re2 = new RegExp('(-?)([0-9]*)(\\.?)([0-9]{' + spaces + '})');
		newVal = newVal.replace(re2, '$1$2$4.');
	}
	newVal = newVal.replace(/\.$/, ''); // to avoid IE flaw
	
	return newVal;
}


function moveDecimalNF(val, left, places)
{
	var newVal = '';
	
	if (places == null) {
		newVal = this.moveDecimalAsString(val, left);
	} else {
		newVal = this.moveDecimalAsString(val, left, places);
	}
	
	return parseFloat(newVal);
}


function getRoundedNF(val)
{
	val = this.moveDecimalRight(val);
	val = Math.round(val);
	val = this.moveDecimalLeft(val);
	
	return val;
}


function preserveZerosNF(val)
{
	var i;

	// make a string - to preserve the zeros at the end
	val = this.expandExponential(val);
	
	if (this.places <= 0) return val; // leave now. no zeros are necessary - v1.0.1 less than or equal
	
	var decimalPos = val.indexOf('.');
	if (decimalPos == -1) {
		val += '.';
		for (i=0; i<this.places; i++) {
			val += '0';
		}
	} else {
		var actualDecimals = (val.length - 1) - decimalPos;
		var difference = this.places - actualDecimals;
		for (i=0; i<difference; i++) {
			val += '0';
		}
	}
	
	return val;
}


function justNumberNF(val)
{
	newVal = val + '';
	
	var isPercentage = false;
	
	// check for percentage
	// v1.5.0
	if (newVal.indexOf('%') != -1) {
		newVal = newVal.replace(/\%/g, '');
		isPercentage = true; // mark a flag
	}
		
	// Replace everything but digits - + ( ) e
	var re = new RegExp('[^\\' + this.inputDecimalValue + '\\d\\-\\+\\(\\)e]', 'g');		
	newVal = newVal.replace(re, '');
	// Replace the first decimal with a period and the rest with blank
	// The regular expression will only break if a special character
	//  is used as the inputDecimalValue
	//  e.g. \ but not .
	// By calling test, it will fill RegExp.leftContext et al
	// The leftContext is what's to the left of the first match
	// Search again in what's in the rightContext
	var tempRe = new RegExp('[' + this.inputDecimalValue + ']', 'g');
	if (tempRe.test(newVal)) {
		newVal = RegExp.leftContext + this.PERIOD + RegExp.rightContext.replace(tempRe, '');
	}
	
	// If negative, get it in -n format
	if (newVal.charAt(newVal.length - 1) == this.DASH ) {
		newVal = newVal.substring(0, newVal.length - 1);
		newVal = '-' + newVal;
	}
	else if (newVal.charAt(0) == this.LEFT_PAREN
	 && newVal.charAt(newVal.length - 1) == this.RIGHT_PAREN) {
		newVal = newVal.substring(1, newVal.length - 1);
		newVal = '-' + newVal;
	}
	
	newVal = parseFloat(newVal);
	
	if (!isFinite(newVal)) {
		newVal = 0;
  }

  if (isPercentage) {
  	newVal = this.moveDecimalLeft(newVal, 2);
  }
		
	return newVal;
}

//format angka================================================
function _formatted(x,y,dec){ //x is string eg._formatted('123')
						  //y is decimal eg _formatted(2342.234)
						  //y or x can be blank from caller source
						  //call this with _formatted(source obj)
	if (typeof dec == 'undefined') dec = 2;
	if(typeof x =='number') {
		var numberTest = new NumberFormat(parseFloat(x),y);
	} else {
		var numberTest = new NumberFormat(parseFloat(x.value),y);
	}
	//numberTest.setCurrency(true);
	numberTest.setCommas(true);
	numberTest.setPlaces(dec);
	//var POUND = unescape('%A3');
	//numberTest.setCurrencyPrefix(POUND);
	return(numberTest.toFormatted());
}

function conv_to_dec(x)
{
	temp=x.replace(',','');
	if(temp.indexOf('.')>-1)
		return parseFloat(temp);
	else
		return parseInt(temp);
}
//=========================================================================================
/**
 * @uthor nangkoel Gutul
 *Juhar, Indonesia
 * http://www.nangkoel.com
 *+(62) 081311351132
 */
//Rupiah dalam characterSet
//panggil load_rupiah(obj,tujuan,e) untuk menggunakan 
function load_rupiah(obj,tujuan,e)//e adalah event, tujuan adalah tempat text akan dirampilkan
{								  //obj adalah nama object yang menyimpan angka/number	
	
	tombol=getKey(e);
	if(tombol==13)
	{
		rupiahkan(obj,tujuan);
		return true;
	}
	else if(angka_doang(e))
	{
		return true;
	}
	else
	{
		return false;
	}
}
//==================================
function rupiahkan(obj,tujuan,sen)
{
	if (typeof sen == 'undefined') {
		sen = false;
	}
	after='';
	nilai=obj.value;
	while(nilai.indexOf(",")>-1)
	{
		nilai=nilai.replace(",","");
	}
	coma=nilai.length ;
	if(nilai.lastIndexOf('.')>0)
	{
			after=nilai.substr(nilai.lastIndexOf('.')+1,nilai.length);
			c=nilai.substr(0,nilai.lastIndexOf('.'));
	}
	else
	c=nilai.substr(0,coma);

	output=document.getElementById(tujuan);
	var angka=new Array();
	angka[0]='nol';	
	angka[1]='satu';
	angka[2]='dua';
	angka[3]='tiga';
	angka[4]='empat';
	angka[5]='lima';
	angka[6]='enam';
	angka[7]='tujuh';
	angka[8]='delapan';
	angka[9]='sembilan';
	tval='';
	mval='';
	jval='';
	rval='';
	raval='';
	tex='';
      say_after='';

	if(after.length>0 && after.length<4)
	{
		/*
			for(h=0;h<=after.length-1;h++)
			{
			   _o=parseInt(after.substr(h,1));
			   if(h==0)
			   say_after+=' koma '+angka[_o];
			   else
			   say_after+=' '+angka[_o];
	
			}
		*/
		if (sen) {
			if(after.length==1) after += '0';
			else if(after.length>2) after = after.substring(after,0,2);
			say_after+=' koma '+ ratusan(after,'');
		} else {
			say_after+=' koma '+ ratusan(after,'');
		}
	}else if(after){
		alert('Maximum 3 digit decimal');
	}
	//999.999.999.999.999	
	//123 456 789 012 345
	//012 345 678 901 234

	panjang=c.length;

	t=false;
	m=false;
	j=false;
	r=false;
	ra=false;
	if(panjang<4)
	{
		ra=true;
	}
	else if(panjang<7)
	{
		r=true;
	}
	else if(panjang<10)
	{
		j=true;
	}
	else if(panjang<13)
	{
		m=true;
	}
	else if(panjang<16)
	{
		t=true;
	}


	raval=_ra();
	rval=_r();
	jval=_j();
	mval=_m();
  	tval=_t();
	
loadgroup();	

function _t()
{
	if(panjang==15)   
       tval=nilai.substr(0,3);
	else if(panjang==14)
	   tval=nilai.substr(0,2);
	else if(panjang==13)
	   tval=nilai.substr(0,1);   
return tval;	   
}
function _m()
{
	if (panjang==15)
	   mval=nilai.substr(3,3);
	if (panjang==14)
	   mval=nilai.substr(2,3);
	if (panjang==13)
	   mval=nilai.substr(1,3);
	if (panjang==12)
	   mval=nilai.substr(0,3);
	if (panjang==11)
	   mval=nilai.substr(0,2);
	if (panjang==10)
	   mval=nilai.substr(0,1);	   	   	     
return mval;	        
}

function _j()
{
	if (panjang==15)
	   jval=nilai.substr(6,3);
	if (panjang==14)
	   jval=nilai.substr(5,3);
	if (panjang==13)
	   jval=nilai.substr(4,3);
	if (panjang==12)
	   jval=nilai.substr(3,3);
	if (panjang==11)
	   jval=nilai.substr(2,3);
	if (panjang==10)
	   jval=nilai.substr(1,3);
	if (panjang==9)
	   jval=nilai.substr(0,3);
	if (panjang==8)
	   jval=nilai.substr(0,2);
	if (panjang==7)
	   jval=nilai.substr(0,1);		   	   	            
return jval;
}

function _r()
{
	if (panjang==15)
	   rval=nilai.substr(9,3);
	if (panjang==14)
	   rval=nilai.substr(8,3);
	if (panjang==13)
	   rval=nilai.substr(7,3);
	if (panjang==12)
	   rval=nilai.substr(6,3);
	if (panjang==11)
	   rval=nilai.substr(5,3);
	if (panjang==10)
	   rval=nilai.substr(4,3);
	if (panjang==9)
	   rval=nilai.substr(3,3);
	if (panjang==8)
	   rval=nilai.substr(2,3);
	if (panjang==7)
	   rval=nilai.substr(1,3);
	if (panjang==6)
	   rval=nilai.substr(0,3);
	if (panjang==5)
	   rval=nilai.substr(0,2);
	if (panjang==4)
	   rval=nilai.substr(0,1);				       
return rval;
}
function _ra()
{
	if (panjang==15)
	   raval=nilai.substr(12,3);
	if (panjang==14)
	   raval=nilai.substr(11,3);
	if (panjang==13)
	   raval=nilai.substr(10,3);
	if (panjang==12)
	   raval=nilai.substr(9,3);
	if (panjang==11)
	   raval=nilai.substr(8,3);
	if (panjang==10)
	   raval=nilai.substr(7,3);
	if (panjang==9)
	   raval=nilai.substr(6,3);
	if (panjang==8)
	   raval=nilai.substr(5,3);
	if (panjang==7)
	   raval=nilai.substr(4,3);
	if (panjang==6)
	   raval=nilai.substr(3,3);
	if (panjang==5)
	   raval=nilai.substr(2,3);
	if (panjang==4)
	   raval=nilai.substr(1,3);
	if (panjang==3)
	   raval=nilai.substr(0,3);
	if (panjang==2)
	   raval=nilai.substr(0,2);
	if (panjang==1)
	   raval=nilai.substr(0,1);						
         
return raval;
}

function loadgroup()
{
  if(t)
  {
  	tex=ratusan(tval,' triliun ');
	tex+=ratusan(mval,' miliar ');
	tex+=ratusan(jval,' juta ');
	tex+=ratusan(rval,' ribu ');
	tex+=ratusan(raval,'');
    	         
  }
  else if(m)
  {
	tex+=ratusan(mval,' miliar ');
	tex+=ratusan(jval,' juta ');
	tex+=ratusan(rval,' ribu ');
	tex+=ratusan(raval,'');
  }	
  else if(j)
  {
	tex+=ratusan(jval,' juta ');
	tex+=ratusan(rval,' ribu ');
	tex+=ratusan(raval,'');
  }
  else if(r)
  {
	tex+=ratusan(rval,' ribu ');
	tex+=ratusan(raval,'');
  }
 else if(ra)
  {
	tex=ratusan(raval,'');
  }  
}

function ratusan(nx,group) {
	switch (nx.length) {
		case 2:
			if(nx.substr(0,1)=='0')
			nx=nx.substr(1,1);
			break;
		case 3:
			if(nx.substr(0,1)=='0') nx=nx.substr(1,2);
			if(nx.substr(0,1)=='0') nx=nx.substr(1,1);
			break;
	}
	panj=nx.length;
	
	if(panj==3) {
		ix=angka[parseInt(nx.substr(0,1))];
		if(ix=='satu')
			r1='seratus';
		else if(ix=='nol')
			r1='';
		else
			r1=ix+' ratus';
		
		i0=angka[nx.substr(1,1)];
		i1=angka[nx.substr(2,1)];
		if(i0=='nol' && i1=='nol')
			puluh=r1;
		else if(i0=='satu') {
			if(i1=='nol')
				puluh=r1+' sepuluh';
			else if(i1=='satu')
				puluh=r1+' sebelas';
			else
				puluh=r1+' '+i1+' belas';
		} else if(i1=='nol')
			puluh=r1+' '+i0+' puluh ';
		else if(i0=='nol' && i1!='nol')
			puluh=r1+' '+i1;	
		else
			puluh=r1+' '+i0+' puluh '+i1;
		
		if(ix=='satu' && i0=='nol' && i1=='nol')
			puluh='seratus';
		
		puluh+=group;
	
		return puluh;	
	}
    
	if(panj==2)
	{
		i0=angka[nx.substr(0,1)];
		i1=angka[nx.substr(1,1)];
		if(i0=='nol' && i1=='nol')
		{
		 puluh='';	
		}
		else if(i0=='satu')
		{
			if(i1=='nol')
			puluh='sepuluh';
			else if(i1=='satu')
			puluh='sebelas';
			else
			puluh=i1+'belas';
		}	
		else if(i1=='nol')
		puluh=i0+' puluh ';
		else
		puluh=i0+' puluh '+i1;
		
		puluh+=group;
		return puluh;		
	}
	
	if(panj==1)
    {
		puluh=angka[parseInt(nx)];
		if(nx=='0')
		return '';
		else {
			if(puluh=='satu' && group==' ribu ')
				puluh='seribu '
			else	 
			puluh+=group;
			return puluh;
		}  
	}	
	return '';
}
try	{
	if(tex.length>0) {
		output.innerHTML=tex+say_after;
	} else{
		output.innerHTML=tex;
	}
} catch(x){alert('Enter some number!');}
}
//==================================================================================

function lockScreen(type)
{
	try{
	  if(document.getElementById('lock')){
	     document.getElementById('lock').style.display ='';
		 document.getElementById('front').style.display='';
		 if (trim(type).toLowerCase() == 'wait') {
            document.getElementById('front').innerHTML="<img src='images/progress.gif'><br><b>P l e a s e &nbsp  w a i t . ...!</b>";		 	
		 }
		 else if (trim(type).toLowerCase() == 'progress') {
		 	tempstr="<div id=progressLegend></div>";
			tempstr+="<div id=progressBar class=pBarBackground><div id=progressBarTop class=pBarTop></div></div>";
			document.getElementById('front').innerHTML=tempstr;
		 	} 
		 else{}							 	
	   }
	  else{
		dheight=docHeight();
		dwidth =docWidth();
		if(dheight<600)
		   dheight=600;
		c=document.createElement('div');
		c.setAttribute('id','lock');
		document.body.appendChild(c);	
		c.style.position='fixed';
		c.style.top='0px';
		c.style.left='0px';
	    c.style.width=dwidth+'px';	
	    c.style.height=dheight+'px';
		c.style.backgroundColor='#999999';	
        c.style.zIndex=1000;		
		test=document.createElement('div');
		test.setAttribute('id','front');
		test.setAttribute('class','dragdyn');
		document.body.appendChild(test);	
		test.style.position='fixed';
		test.style.top=(dheight/2)+'px';
		test.style.left=(dwidth/2-100)+'px';
		test.style.textAlign='center';
		test.style.padding='10px';
	//	test.style.backgroundColor='#8AC4F0';
		test.style.border='#A9CAF5 solid 1px';	
		test.style.zIndex=1001;	
		  if(trim(type).toLowerCase()=='wait'){
			test.innerHTML="<img src='images/progress.gif'><br><b>P l e a s e &nbsp  w a i t . ...!</b>";
		  }
		  else if(type=='progress'){
		 	tempstr="<div id=progressLegend class=pLegend>Progress Bar:</div>";
			tempstr+="<div id=progressBar class=pBarBackground><div id=progressBarTop class=pBarTop></div></div>";
			test.innerHTML=tempstr;		  	
		  } 
		  else{
		  	//default do nothing
		  }	
	  }
	}
	catch(e){}
    setOpacity('lock',0,30, 1);	
}
function unlockScreen()
{
	document.getElementById('lock').style.display='none';
	document.getElementById('front').style.display='none';
}
//====================================hide show object
function hideObject(obj)
{
	obj.style.display='none';
}
function showObject(obj)
{
	obj.style.display='';
}

function hideById(id)
{
	document.getElementById(id).style.display='none';
}
function showById(id)
{
	document.getElementById(id).style.display='';
}
function logout()
{
	param='';
	post_response_text('logout.php', param, respog);
	
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					window.location='login.html'; 
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}	
		}
	}
}
//+++tab controller++++++++++++++++++++++++++++++++++
activeTab='tab0';
function tabAction(cur,numactive,tabID,max,theme)
{
	if(theme=='skyblue'){
		img='images/tab3.png';
	}else if(theme=='red'){
		img='images/tab3Red.png';
	}else{
		img='images/tab3Gray.png';
	}

	activeTab = 'tab'+tabID  + numactive;
	try {
		for (x = 0; x <= max; x++) {
			if(numactive!==x){
			document.getElementById('tab'+tabID + x).style.background = '#235183';
			document.getElementById('tab'+tabID  + x).style.color = '#e0ecfe';
			document.getElementById('tab'+tabID  + x).style.fontWeight = 'normal';
			document.getElementById('content'+tabID  + x).style.display = 'none';
			}
		}
		//cur.style.backgroundImage = 'url(images/tab2.png)';
		cur.style.color = '#dedede';
		cur.style.fontWeight = '800';

		document.getElementById('content'+tabID  + numactive).style.display = '';
	}
	catch(e)
	{
		alert(e.toString()+"\nMaybe Tab's component not loaded correctly");
		
	}
}
function chgBackgroundImg(obj,img,color)
{
	if (obj.id != activeTab) {
		obj.style.background = '#1E5896';
		obj.style.color=color;
	}
}
//+++++++++++++++++++++++++++++++++++++++++++++++++++
function verify()
{
	if(!window.top.left)
	{
                   // alert('You may follow the system flow')
                    //window.location='logout.php';
                    return true;
	}
                    else{
                            return true;
                        }
	//reminder dimatikan dan akan berjalan setelah dibuka mini windownya.
	//startReminder();
	//createMiniWin();
}

function isSaveResponse(txt)
{
	txt=txt.toUpperCase();
	if (txt.lastIndexOf('GAGAL') > -1 || txt.lastIndexOf('ERROR') > -1 || txt.lastIndexOf('WARNING') > -1)
      return false
	else
	  return true;  
}

function emailCheck (emailStr) {

		/* The following variable tells the rest of the function whether or not
		to verify that the address ends in a two-letter country or well-known
		TLD.  1 means check it, 0 means don't. */
		
		var checkTLD=1;
		
		/* The following is the list of known TLDs that an e-mail address must end with. */
		
		var knownDomsPat=/^(com|net|org|edu|int|mil|gov|arpa|biz|aero|name|coop|info|pro|museum)$/;
		
		/* The following pattern is used to check if the entered e-mail address
		fits the user@domain format.  It also is used to separate the username
		from the domain. */
		
		var emailPat=/^(.+)@(.+)$/;
		
		/* The following string represents the pattern for matching all special
		characters.  We don't want to allow special characters in the address. 
		These characters include ( ) < > @ , ; : \ " . [ ] */
		
		var specialChars="\\(\\)><@,;:\\\\\\\"\\.\\[\\]";
		
		/* The following string represents the range of characters allowed in a 
		username or domainname.  It really states which chars aren't allowed.*/
		
		var validChars="\[^\\s" + specialChars + "\]";
		
		/* The following pattern applies if the "user" is a quoted string (in
		which case, there are no rules about which characters are allowed
		and which aren't; anything goes).  E.g. "jiminy cricket"@disney.com
		is a legal e-mail address. */
		
		var quotedUser="(\"[^\"]*\")";
		
		/* The following pattern applies for domains that are IP addresses,
		rather than symbolic names.  E.g. joe@[123.124.233.4] is a legal
		e-mail address. NOTE: The square brackets are required. */
		
		var ipDomainPat=/^\[(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})\]$/;
		
		/* The following string represents an atom (basically a series of non-special characters.) */
		
		var atom=validChars + '+';
		
		/* The following string represents one word in the typical username.
		For example, in john.doe@somewhere.com, john and doe are words.
		Basically, a word is either an atom or quoted string. */
		
		var word="(" + atom + "|" + quotedUser + ")";
		
		// The following pattern describes the structure of the user
		
		var userPat=new RegExp("^" + word + "(\\." + word + ")*$");
		
		/* The following pattern describes the structure of a normal symbolic
		domain, as opposed to ipDomainPat, shown above. */
		
		var domainPat=new RegExp("^" + atom + "(\\." + atom +")*$");
		
		/* Finally, let's start trying to figure out if the supplied address is valid. */
		
		/* Begin with the coarse pattern to simply break up user@domain into
		different pieces that are easy to analyze. */
		
		var matchArray=emailStr.match(emailPat);
		
		if (matchArray==null) {
		
		/* Too many/few @'s or something; basically, this address doesn't
		even fit the general mould of a valid e-mail address. */
		
		alert("Email salah");
		return false;
		}
		var user=matchArray[1];
		var domain=matchArray[2];
		
		// Start by checking that only basic ASCII characters are in the strings (0-127).
		
		for (i=0; i<user.length; i++) {
		if (user.charCodeAt(i)>127) {
		alert("Email mengandung karakter yang dilarang");
		return false;
		   }
		}
		for (i=0; i<domain.length; i++) {
		if (domain.charCodeAt(i)>127) {
		alert("Email mengandung karakter yang dilarang.");
		return false;
		   }
		}
		
		// See if "user" is valid 
		
		if (user.match(userPat)==null) {
		
		// user is not valid
		
		alert("Username pada email tidak valid.");
		return false;
		}
		
		/* if the e-mail address is at an IP address (as opposed to a symbolic
		host name) make sure the IP address is valid. */
		
		var IPArray=domain.match(ipDomainPat);
		if (IPArray!=null) {
		
		// this is an IP address
		
		for (var i=1;i<=4;i++) {
		if (IPArray[i]>255) {
		alert("IP address salah!");
		return false;
		   }
		}
		return true;
		}
		
		// Domain is symbolic name.  Check if it's valid.
		 
		var atomPat=new RegExp("^" + atom + "$");
		var domArr=domain.split(".");
		var len=domArr.length;
		for (i=0;i<len;i++) {
		if (domArr[i].search(atomPat)==-1) {
		alert("Domain pada alamat email salah.");
		return false;
		   }
		}
		
		/* domain name seems valid, but now make sure that it ends in a
		known top-level domain (like com, edu, gov) or a two-letter word,
		representing country (uk, nl), and that there's a hostname preceding 
		the domain or country. */
		
		if (checkTLD && domArr[domArr.length-1].length!=2 && domArr[domArr.length-1].search(knownDomsPat)==-1) {
		alert("Email harus diakhiri dengan domain yang benar");
		return false;
		}
		
		// Make sure there's a host name preceding the domain.
		
		if (len<2) {
		alert("Hostname pada alamat email salah!");
		return false;
		}
		
		// If we've gotten this far, everything's valid!
		return true;
}

function closeDetail(){
    document.getElementById('dynamic').innerHTML = '';
    document.getElementById('dynamic').style.display = 'none';
}







function showDialog1(title,content,width,height,ev)
{
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
	   	c.style.top = '120px';
	   	c.style.left = '100px';
		c.style.width = width+'px';
	   	c.style.paddingTop = '3px';
	   	c.style.zIndex = 1000;
	   	document.body.appendChild(c);
	   }
        cont="<b style='color:#FFFFFF;'>"+title+"</b><img src=images/closebig.gif align=right onclick=closeDialog() title='Close detail' class=closebtn onmouseover=\"this.src='images/closebigon.gif';\" onmouseout=\"this.src='images/closebig.gif';\" id='tutupdialogsatu'><br><br>";
	    cont+="<div style='background-color:#FFFFFF;border:#777777 solid 2px;height:"+height+"px'>";
	    cont+=content;
	    cont+="</div>";
		document.getElementById('dynamic1').innerHTML=cont;
			pos = new Array();
            pos = getMouseP(ev);
            document.getElementById('dynamic1').style.top  		= pos[1] + 'px';
            document.getElementById('dynamic1').style.left 		= '75px';
			document.getElementById('dynamic1').style.display 	= '';
}




function showDialog6(title,content,width,height,ev)
{
       if (document.getElementById('dynamic6')) {
		c = document.createElement('div');   
		c.style.width = width+'px';
	   }
	   else {
	   	c = document.createElement('div');
	   	c.setAttribute('id', 'dynamic6');
	   	c.setAttribute('class', 'drag');
	   	c.style.position = 'absolute';
	   	c.style.display = 'none';
	   	c.style.top = '120px';
	   	c.style.left = '100px';
		c.style.width = width+'px';
	   	c.style.paddingTop = '3px';
	   	c.style.zIndex = 1000;
	   	document.body.appendChild(c);
	   }
        cont="<b style='color:#FFFFFF;'>"+title+"</b><img src=images/closebig.gif align=right onclick=closeDialog6() title='Close detail' class=closebtn onmouseover=\"this.src='images/closebigon.gif';\" onmouseout=\"this.src='images/closebig.gif';\" id='tutupdialogsatu'><br><br>";
	    cont+="<div style='background-color:#FFFFFF;border:#777777 solid 2px;height:"+height+"px'>";
	    cont+=content;
	    cont+="</div>";
		document.getElementById('dynamic6').innerHTML=cont;
			pos = new Array();
            pos = getMouseP(ev);
            document.getElementById('dynamic6').style.top = pos[1] + 'px';
            document.getElementById('dynamic6').style.left = '75px';
			document.getElementById('dynamic6').style.display='';
}


function showDialog1bi(title,content,width,height,ev)
{
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
	   	c.style.top = '120px';
	   	c.style.left = '100px';
		c.style.width = width+'px';
	   	c.style.paddingTop = '3px';
	   	c.style.zIndex = 1000;
	   	document.body.appendChild(c);
	   }
        cont="<b style='color:#FFFFFF;'>"+title+"</b><img src=../images/closebig.gif align=right onclick=closeDialog() title='Close detail' class=closebtn onmouseover=\"this.src='../images/closebigon.gif';\" onmouseout=\"this.src='../images/closebig.gif';\"><br><br>";
	    cont+="<div style='background-color:#FFFFFF;border:#777777 solid 2px;height:"+height+"px'>";
	    cont+=content;
	    cont+="</div>";
		document.getElementById('dynamic1').innerHTML=cont;
			pos = new Array();
            pos = getMouseP(ev);
            document.getElementById('dynamic1').style.top = pos[1] + 'px';
            document.getElementById('dynamic1').style.left = '75px';
		document.getElementById('dynamic1').style.display='';
}

function showDialog2(title,content,width,height,ev)
{
	
	if (document.getElementById('dynamic2')) {
		c = document.createElement('div');
		c.style.width = width+'px';
	} else {
	   	c = document.createElement('div');
	   	c.setAttribute('id', 'dynamic2');
	   	c.setAttribute('class', 'drag');
	   	c.style.position = 'absolute';
	   	c.style.display = 'none';
	   	c.style.top = '120px';
	   	c.style.left = '100px';
		c.style.width = width+'px';
	   	c.style.paddingTop = '3px';
	   	c.style.zIndex = 2000;
	   	document.body.appendChild(c);
	}
        cont="<b style='color:#FFFFFF;'>"+title+"</b><img src=images/closebig.gif align=right onclick=closeDialog2() title='Close detail' class=closebtn onmouseover=\"this.src='images/closebigon.gif';\" onmouseout=\"this.src='images/closebig.gif';\"><br><br>";
	cont+="<div style='background-color:#FFFFFF;border:#777777 solid 2px;height:"+height+"px'>";
	cont+=content;
	cont+="</div>";
	document.getElementById('dynamic2').innerHTML=cont;
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = '75px';
	document.getElementById('dynamic2').style.display='';
}
function showDialog3(title,content,width,height)
{
	
	if (document.getElementById('dynamic2')) {
		c = document.createElement('div');
		c.style.width = width+'px';
	} else {
	   	c = document.createElement('div');
	   	c.setAttribute('id', 'dynamic2');
	   	c.setAttribute('class', 'drag');
	   	c.style.position = 'absolute';
	   	c.style.display = 'none';
	   	c.style.top = '120px';
	   	c.style.left = '100px';
		c.style.width = width+'px';
	   	c.style.paddingTop = '3px';
	   	c.style.zIndex = 2000;
	   	document.body.appendChild(c);
	}
        cont="<b style='color:#FFFFFF;'>"+title+"</b><img src=images/closebig.gif align=right onclick=closeDialog3() title='Close detail' class=closebtn onmouseover=\"this.src='images/closebigon.gif';\" onmouseout=\"this.src='images/closebig.gif';\"><br><br>";
	cont+="<div style='background-color:#FFFFFF;border:#777777 solid 2px;height:"+height+"px'>";
	cont+=content;
	cont+="</div>";
	document.getElementById('dynamic2').innerHTML=cont;
	document.getElementById('dynamic2').style.left = '75px';
	document.getElementById('dynamic2').style.display='';
}


function showDialog4(title,content,width,height,ev)
{
	
	if (document.getElementById('dynamic4')) {
		c = document.createElement('div');
		c.style.width = width+'px';
	} else {
	   	c = document.createElement('div');
	   	c.setAttribute('id', 'dynamic4');
	   	c.setAttribute('class', 'drag');
	   	c.style.position = 'absolute';
	   	c.style.display = 'none';
	   	c.style.top = '120px';
	   	c.style.left = '100px';
		c.style.width = width+'px';
	   	c.style.paddingTop = '3px';
	   	c.style.zIndex = 2000;
	   	document.body.appendChild(c);
	}
        cont="<b style='color:#FFFFFF;'>"+title+"</b><img src=images/closebig.gif align=right onclick=closeDialog4() title='Close detail' class=closebtn onmouseover=\"this.src='images/closebigon.gif';\" onmouseout=\"this.src='images/closebig.gif';\"><br><br>";
	cont+="<div style='background-color:#FFFFFF;border:#777777 solid 2px;height:"+height+"px'>";
	cont+=content;
	cont+="</div>";
	document.getElementById('dynamic4').innerHTML=cont;
	pos = new Array();
    pos = getMouseP(ev);
    document.getElementById('dynamic4').style.top = pos[1] + 'px';
    document.getElementById('dynamic4').style.left = pos[0] + 'px';
	document.getElementById('dynamic4').style.display='';
}


function showDialog5(title,content,width,height,ev)
{
	$.newDialog('showDialog5',title,content,width,height,ev);
	
	
	/*if (document.getElementById('dynamic5')) {
		c = document.createElement('div');
		c.style.width = width+'px';
	} else {
	   	c = document.createElement('div');
	   	c.setAttribute('id', 'dynamic5');
	   	c.setAttribute('class', 'drag');
	   	c.style.position = 'absolute';
	   	c.style.display = 'none';
	   	c.style.top = '120px';
	   	c.style.left = '100px';
		c.style.width = width+'px';
	   	c.style.paddingTop = '3px';
	   	c.style.zIndex = 2000;
	   	document.body.appendChild(c);
	}
        cont="<b style='color:#FFFFFF;'>"+title+"</b><img src=images/closebig.gif align=right onclick=closeDialog5() title='Close detail' class=closebtn onmouseover=\"this.src='images/closebigon.gif';\" onmouseout=\"this.src='images/closebig.gif';\"><br><br>";
	cont+="<div style='background-color:#FFFFFF;border:#777777 solid 2px;height:"+height+"px'>";
	cont+=content;
	cont+="</div>";
	document.getElementById('dynamic5').innerHTML=cont;
	pos = new Array();
    pos = getMouseP(ev);
    document.getElementById('dynamic5').style.top = pos[1] + 'px';
    document.getElementById('dynamic5').style.left = pos[0] + 'px';
	document.getElementById('dynamic5').style.display='';*/
}

function showDialogBi(title,content,width,height,ev){
	if (document.getElementById('dynamic1')){
		c = document.createElement('div');   
		c.style.width = width+'px';
	}else{
	   	c = document.createElement('div');
	   	c.setAttribute('id', 'dynamic1');
	   	c.setAttribute('class', 'drag');
	   	c.style.background = 'black';
	   	c.style.position = 'absolute';
		c.style.display = 'none';
	   	c.style.top = '120px';
	   	c.style.left = '100px';
		c.style.width = width+'px';
	   	c.style.paddingTop = '3px';
	   	c.style.zIndex = 1000;
	   	c.style.opacity = 0.9;
	   	document.body.appendChild(c);
	}
	cont="<b style='color:#FFFFFF;'>&nbsp;&nbsp;"+title+"</b><img src=../images/closebig.gif align=right onclick=closeDialogBi() title='Close detail' class=closebtn onmouseover=\"this.src='../images/closebigon.gif';\" onmouseout=\"this.src='../images/closebig.gif';\"><br><br>";
	cont+="<div style='background-color:#FFFFFF;border:#777777 solid 2px;height:"+height+"px;opacity:0.8'>";
	cont+=content;
	cont+="</div>";
	document.getElementById('dynamic1').innerHTML=cont;
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic1').style.top = pos[1] + 'px';
	document.getElementById('dynamic1').style.left = '75px';
	document.getElementById('dynamic1').style.display='';
}

function closeDialogBi(){
	document.getElementById('dynamic1').innerHTML='';
	document.getElementById('dynamic1').style.display='none';
}

function showDialogBi2(title,content,width,height,ev){
	if (document.getElementById('dynamic2')){
		c = document.createElement('div');   
		c.style.width = width+'px';
	}else{
	   	c = document.createElement('div');
	   	c.setAttribute('id', 'dynamic2');
	   	c.setAttribute('class', 'drag');
	   	c.style.background = 'black';
	   	c.style.position = 'absolute';
		c.style.display = 'none';
	   	c.style.top = '120px';
	   	c.style.left = '100px';
		c.style.width = width+'px';
	   	c.style.paddingTop = '3px';
	   	c.style.zIndex = 1000;
	   	c.style.opacity = 0.9;
	   	document.body.appendChild(c);
	}
	cont="<b style='color:#FFFFFF;'>&nbsp;&nbsp;"+title+"</b><img src=../images/closebig.gif align=right onclick=closeDialogBi2() title='Close detail' class=closebtn onmouseover=\"this.src='../images/closebigon.gif';\" onmouseout=\"this.src='../images/closebig.gif';\"><br><br>";
	cont+="<div style='background-color:#FFFFFF;border:#777777 solid 2px;height:"+height+"px;opacity:0.8'>";
	cont+=content;
	cont+="</div>";
	document.getElementById('dynamic2').innerHTML=cont;
	pos = new Array();
	pos = getMouseP(ev);
	document.getElementById('dynamic2').style.top = pos[1] + 'px';
	document.getElementById('dynamic2').style.left = '75px';
	document.getElementById('dynamic2').style.display='';
}

function closeDialogBi2(){
	document.getElementById('dynamic2').innerHTML='';
	document.getElementById('dynamic2').style.display='none';
}

function closeDialog()
{
	if(document.getElementById('dynamic2')) {
		closeDialog2();
	}
	if(document.getElementById('dynamic5')) {
		closeDialog5();
	}	
	try{
		document.getElementById('dynamic1').innerHTML='';
		document.getElementById('dynamic1').style.display='none';		
	}catch(e){

	}
}

function closeDialog2()
{
	document.getElementById('dynamic2').innerHTML='';
	document.getElementById('dynamic2').style.display='none';
}

function closeDialog3()
{
	document.getElementById('dynamic3').innerHTML='';
	document.getElementById('dynamic3').style.display='none';
}


function closeDialog4()
{
	document.getElementById('dynamic4').innerHTML='';
	document.getElementById('dynamic4').style.display='none';
}


function closeDialog5()
{
	document.getElementById('dynamic5').innerHTML='';
	document.getElementById('dynamic5').style.display='none';
}

function closeDialog6()
{
	document.getElementById('dynamic6').innerHTML='';
	document.getElementById('dynamic6').style.display='none';
}


function change_number(object)
{
	   while(object.value.indexOf(",")>-1)
	   {
	   	object.value=object.value.replace(",","");
	   }
	//number format cleared and verified
	str=object.value.replace(".","");
	rex=/[^0-9]/;
	if ((!str.match(rex)) || (parseFloat(str)==0.00)) {
			try{
				object.value=_formatted(object);
				}
			catch(ex)
				{
				alert(ex.toString());
				}
	}
	else {
		if (object.value.length > 0) {
			//alert('Nominal salah');
			object.focus();
		}		
	}
}

function remove_comma(object){//object adalah textbox atau componen yang memiliki atribut 'value'
	x = object.value;
	while (x.indexOf(",") > -1) {
		x = x.replace(",", "");
	}
	return x;
}
function remove_comma_var(nilai){//nilai adalah string yang bisa berupa 9,001.50 atau 9,0000
	while (nilai.indexOf(",") > -1) {
		nilai = nilai.replace(",", "");
	}
	return nilai;
}


function remove_kutip(object){
	x = object.value;
	while (x.indexOf('"') > -1) {
		x = x.replace('"', '');
	}
	while (x.indexOf("'") > -1) {
		x = x.replace("'", "");
	}
	object.value=x;
}

function getOptionsValue(zObj) {
	if(zObj.options) {
		return zObj.options[zObj.selectedIndex].value;
	} else {
		return false;
	}
	
}

function getOptionsText(zObj) {
	if(zObj.options) {
		return zObj.options[zObj.selectedIndex].text;
	} else {
		return false;
	}
}
//===============================
//====================================khusus untuk reminder dan chat
function post_param(tujuan,param,functiontoexecute)
{

zz=verify();
    if(zz){
        par=parent.location.href.replace("http://","");
        param+='&par='+par;
	con.open("POST", tujuan, true);
	con.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	con.setRequestHeader("Content-length", param.length);
	con.setRequestHeader("Connection", "close");
	
	con.onreadystatechange = eval(functiontoexecute);
	con.send(param);
    }
    else
    window.location='logout.php';
    
}
/*
//==================================================================
//parameter reminder
var mainReminder;
var blink;
var lastMess;
var idle=1;//is idle
function startReminder(){

	//default looping 
	//1000 adalah 1 detik(a secon)
    interval=2000;
	mainReminder= window.setInterval("getReminderData()",interval);
}

function getReminderData()
{
	reminderSlave='masterReminder.php';
	container=document.getElementById('warningContainer');
	
	//cek apakah sedang terbuka atau tidak
	//jika terbuka tunda request
	xyz=document.getElementById('miniWin');
	if (xyz.style.display == '') {
	//do nothisng
	}
	else {
		if (idle == 0) {
		//if prev query has not response than wait
		}
		else {
			//post request
			post_param(reminderSlave, '', respot);
			idle = 0;//waiting for response;
		}
	}
	    function respot() {
        if (con.readyState == 4) {
            if (con.status == 200) {
				idle=1;//set idle=true
                if (!isSaveResponse(con.responseText)) {
                    container.innerHTML='Reminder Error';
					container.style.color='darkred';
					alert(con.responseText);
                } else {
                     if(trim(con.responseText)=='')
                      {
						container.style.color='#ffffff';
						container.style.weight='normal';
					  	container.innerHTML='Reminder System';
						document.getElementById('messContainer').innerHTML='No Message';					    
						window.clearInterval(blink);
						subj=document.getElementById('warningContainer');
	     				subj.style.backgroundColor='#D3DAED';						
					  }
					  else
					  {
						container.style.weight='bolder';
						container.innerHTML="You've Got Message";
						if(con.responseText!=lastMess)
						{
							container.style.color='blue';
							fillMiniWin(con.responseText);
						} 
						//jika data masih sama dengan yang sebelumnya maka diabaikan
		               lastMess=con.responseText;
					  }
                }
            } else {
                error_catch(con.status);
            }
        }
    }
}

function fillMiniWin(cont)
{
	   document.getElementById('messContainer').innerHTML=cont;	
	   blink=window.setInterval("blinkReminder()",60000);
}
function createMiniWin()
{
       if (document.getElementById('miniWin')) {
		c.style.width = '150px';
	   }
	   else {
	   	c = document.createElement('div');
	   	c.setAttribute('id', 'miniWin');
	   	c.setAttribute('class', 'miniwin');
	   	c.style.position = 'fixed';
		c.style.display='none';
	   	c.style.left = '5px';
	   	c.style.bottom = '5px';
		c.style.width = '250px';
		c.style.height = '150px';
		c.style.backgroundColor='#FFFFFF';
	   	c.style.zIndex = 1001;
		c.style.border ='black solid 1px';
	   	document.body.appendChild(c);
	   }	
	   cont="<div windth=100% height=20px style='background-color:#000000;'>";
	   cont+="<table width=100%><tr><td align=left style='color:white;'>Last Message:</td>";
	   cont+="<td align=right><span style='cursor:pointer;color:white;' onclick=minimizeMiniwin()>X</span>";
	   cont+="</td></tr></table></div><div id=messContainer style='overflow:scroll;height:120px;'>No Message</div>";
	   c.innerHTML=cont;
}

function minimizeMiniwin()
{
	document.getElementById('miniWin').style.display='none';
    window.clearInterval(blink);
	window.clearInterval(mainReminder);
	}
function displayMiniWin()
{
	document.getElementById('miniWin').style.display='';
    startReminder();
	createMiniWin();
	subj=document.getElementById('warningContainer');
	subj.style.color='#ffffff';
	subj.style.weight='normal';
	subj.style.backgroundColor='#D3DAED';
}

function blinkReminder()
{
	subj=document.getElementById('warningContainer');
	if(subj.style.backgroundColor=='orange')
	     subj.style.backgroundColor='#D3DAED';
	else
	      subj.style.backgroundColor='orange';	 
}
*/

/*******************************************************************************
 ** zTools *********************************************************************
 *******************************************************************************/
/** z Class */
z = {
	elem : undefined,
	elSearch: function (id,ev,parentFlag) {
		
		var cont = "<div class='elsearch-judul noselect'><div class=''><div class=''><div class='col-2'>Search</div><input id='elSearchBox' class='myinputtext col-8' type=search onkeypress='var tmp=getKey(event);if(tmp==13){z.doSearch(\""+id+"\"",
			el;//popup = document.getElementById('dynamic1'),
		if(typeof parentFlag != 'undefined') {
			cont += ',"'+parentFlag+'"';
			el = getById(parentFlag).firstChild;
		} else {
			el = getById(id);
		}
		cont +=");}'><span class='mybutton search pull-right' onclick='z.doSearch(\""+id+"\""
		if(typeof parentFlag != 'undefined') {
			cont += ',"'+parentFlag+'"';
		}
		cont +=")' ><i class='fa fa-search'></i></span></div></div><div class='clearfix'></div></div>";
		if(el.disabled==false) {
			cont += "<div id='elSearchResult' "+
				"style=''><div>";
			z.elem = $.newDialog('elSearch','Find '+id,cont,300,250,ev);
			z.elem.target.body.style.border = "3px solid #c8e1e7";
			z.elem.target.panel.style.border = "1px solid rgb(169 182 199)";
			z.elem.target.panel.style.boxShadow  = "0px 0px 1px rgba(0,0,0,0.3)";
			//border: 3px solid #c8e1e7;
		}
	},
	
	doSearch: function(id,parentFlag) {
		var el,
			result = getById('elSearchResult'),
			query = getById('elSearchBox').value,
			tmpStr;
		tmpStr = '<table class="sortable noselect" cellpadding=1 cellspacing=1 border=0 >';
		tmpStr += '<thead><tr class=rowheader><th>Code</th><th>Name</th></tr></thead>';
		tmpStr += '<tbody>';
		if(typeof parentFlag != 'undefined') {
			el = getById(parentFlag).firstChild;
		} else {
			el = getById(id)
		}
		for(i in el.options) {
			var elText = el.options[i].text,
				elValue = el.options[i].value,
				show = false;
			if(elText) {
				elText = elText.toLowerCase();
				if(elText.search(query.toLowerCase())>-1) {
					show = true;
				}
			}
			if(elValue) {
				elValue = elValue.toLowerCase();
				if(elValue.search(query.toLowerCase())>-1) {
					show = true;
				}
			}
			
			if(show) {
				tmpStr += "<tr style='cursor:pointer;' title='Choose..'  class=rowcontent onclick='z.passParam(\""+id+"\",\""+el.options[i].value+"\"";
				if(typeof parentFlag != 'undefined') {
					tmpStr += ',"'+parentFlag+'"';
				}
				tmpStr += ")'><td>"+el.options[i].value+"</td><td>"+el.options[i].text+"</td></tr>";
			}
		}
		tmpStr += '</tbody></table>';
		result.innerHTML = tmpStr;
	},
	
	passParam: function(id,value,parentFlag) {
		var el;
		if(typeof parentFlag != 'undefined') {
			el = getById(parentFlag).firstChild;
		} else {
			el = getById(id)
		}
		if(el.disabled==false) {
			for(i in el.options) {
				if(el.options[i].value==value) {
					el.selectedIndex = i;
					if ("createEvent" in document) {
						var evt = document.createEvent("HTMLEvents");
						evt.initEvent("change", false, true);
						el.dispatchEvent(evt);
					}
					else
						el.fireEvent("onchange");
					z.elem.close();return;//closeDialog()
				}
			}
		}
	},
	
	/** Number Format for onkeyup, check decimal point up to 2 digit */
	numberFormat: function(id, dec) {
		var el = getById(id),val;
		el.value=remove_comma(el);
		val = el.value;
		if(typeof dec=='undefined') dec=2;
		
		var tmp = val.split('.');
		if (dec==0) {
			el.value = _formatted(el,null,0);
		}
		if(tmp.length>1) {
			if(tmp[1].length>0) {
				if(tmp[1].length>parseInt(dec)) {
					el.value = _formatted(el,null,dec);
				} else {
					el.value = _formatted(el,null,tmp[1].length);
				}
			} else {
				el.value = _formatted(el,null,0)+'.';
			}
		} else {
			el.value = _formatted(el,null,0);
		}
	},
	
	/** Manual Event Trigger 
	 * @param	element/string	el		Registered Element or Element Id
	 * @param	string			type	Event to be triggered
	 */
	trigger: function(el, type) {
		if(typeof el=='string') {
			el = document.getElementById(el);
		}
		
		if ("createEvent" in document) {
			var evt = document.createEvent("HTMLEvents");
			evt.initEvent(type, false, true);
			el.dispatchEvent(evt);
		}
		else
			el.fireEvent("on"+type);
	},
	
	/** Check if element has class */
	hasClass: function(element, className) {
		return element.className && new RegExp("(^|\\s)" + className + "(\\s|$)").test(element.className);
	}
};

/* Function autoFill
 * Fungsi untuk mengisi element dengan suatu nilai
 * I : element,nilai
 * O : element dengan value val
 */
function autoFill(id,val) {
    // Check if element exist
    if(!id) {
        alert('DOM Definition Error');
        exit;
    }
    
    if(id.options) {
        // Options Element
        var index = 0;
        for(i=0;i<id.options.length;i++) {
            if(id.options[i].value==val) {
                id.selectedIndex = i;
                break;
            }
        }
    } else if(id.getAttribute('type')=='checkbox') {
        // Options Checkbox
        if(val==0) {
            id.checked = true;
        } else {
            id.checked = false;
        }
    } else {
        // Options Text
        id.value = val;
    }
}

/* Function getValue
 * Fungsi mengambil nilai suatu element
 * I : id element
 * O : nilai
 */
function getValue(id) {
    var tmp = document.getElementById(id);
    if(!tmp) {
        alert("DOM Definition Error : "+id);
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
	var el = document.getElementById(id);
	if(el) {
		return el;
	} else {
		if(console) {
			console.log("DOM Definition Error: "+id);
		} else {
			alert("DOM Definition Error: "+id);
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

/*******************************************************************************
 ** Mail Box Function **************************************************************
 *******************************************************************************/
var mailBoxNotif = createXMLHttpRequest();
var intervalMailBox;

function createNotifMailPop(ele,option){
	var mailWindow = document.createElement("ul");
		mailWindow.classList.add("mail-box");
	function dateDiff(datepart, fromdate, todate) {	
	  datepart = datepart.toLowerCase();	
	  var diff = todate - fromdate;	
	  var divideBy = { w:604800000, 
					   d:86400000, 
					   h:3600000, 
					   n:60000, 
					   s:1000 };	
	  
	  return Math.floor(diff/divideBy[datepart]);
	}
	var optionsDefaults = {
		open: {}, 
		interval: 60000, 
		url: "master_slave_mailbox.php" 
	}
	var setupOption = optExtend(optExtend({}, optionsDefaults),option);
	function isObject(o){
		return Object.prototype.toString.call(o) === '[object Object]';
	}
	function isElement(o){
		return (
		  o instanceof HTMLElement || //DOM2
		  (o && typeof o === 'object' && o !== null && o.nodeType === 1 && typeof o.nodeName === 'string')
		);
	}
	function optExtend(target, source) {
		target = target || {};
		for (var prop in source) {
		  // Go recursively
		  if (isObject(source[prop])) {
			target[prop] = optExtend(target[prop], source[prop])
		  } else {
			target[prop] = source[prop]
		  }
		}
		return target;
	}
	function getResponseMailNotif(tujuan,funct){
		paramNotif=parent.location.href.replace("http://","");
		paramNotif=paramNotif.replace("https://","");
		tujuan+='&par='+paramNotif;  
				
		mailBoxNotif.open("GET",tujuan,true);
		mailBoxNotif.onreadystatechange= eval(funct);
		mailBoxNotif.send(null);
	}
	
	function getTanggalDiff(tanggal,now){
		var y2k  = new Date(tanggal);
		var today= new Date(now);
		nDiff = parseInt(dateDiff('n', y2k, today));
		hDiff = parseInt(dateDiff('h', y2k, today));
		dDiff = parseInt(dateDiff('d', y2k, today));
		wDiff = parseInt(dateDiff('w', y2k, today));
		if(wDiff > 7){
			result = getTanggalMail(y2k);
		}else if(wDiff>0){
			result = wDiff+" Minggu yang lalu";
		}else if(dDiff>0){
			result = dDiff+" Hari yang lalu";
		}else if(hDiff>0){
			result = hDiff+" Jam yang lalu";
		}else if(nDiff>=1){
			result = nDiff +" menit yang lalu";
		}else if(nDiff>=0){
			result = "Beberapa menit yang lalu";
		}
		return result;
	}
	function getTanggalMail(tanggal){
		d = new Date(tanggal);
		let result = "";
		day = d.getDate();
		jam = d.getHours();
		menit = d.getMinutes();
		bln = (d.getMonth()+1);
		thn = d.getFullYear();
		result = day+"-"+bln+"-"+thn;
		return result;
	}
	function insertData(mailWindow,dataArr,num=0){
		if(dataArr.length>0){
			var id = dataArr[num]['id'];
			var sumberjenis = dataArr[num]['sumberjenis'];
			namajenis = dataArr[num]['namajenis'];
			detail = dataArr[num]['detail'];
			var readnotif = dataArr[num]['readnotif'];
			tanggal = dataArr[num]['tanggal'];
			tanggalsekarang = dataArr[num]['tanggalsekarang'];
			span = document.createElement("span");
			span.classList.add('judul');
			spanTanggal = document.createElement("span");
			spanTanggal.classList.add('tgl');
			p = document.createElement("p");
			span.innerHTML = namajenis;
			spanTanggal.setAttribute("tanggal",tanggal);
			spanTanggal.innerHTML = getTanggalDiff(tanggal,tanggalsekarang);
			p.innerHTML = detail;
			listMail = document.createElement("li");
			
			if(readnotif == "0"){
				listMail.classList.add('unread');
				listMail.style.cursor = "pointer";
				
			}
			if(isMobile.any()){
				listMail.ontouchstart = function(){
					if(readnotif == "0"){
						readnotifAction(listMail,id,function(){
							tujuan = site_url(sumberjenis+'.php');
							getPageOwlProject(tujuan,sumberjenis);
						});
					}else{
						tujuan = site_url(sumberjenis+'.php');
						getPageOwlProject(tujuan,sumberjenis);
					}
				}
			}else{
				listMail.onclick = function(){
					if(readnotif == "0"){
						readnotifAction(listMail,id,function(){
							tujuan = site_url(sumberjenis+'.php');
							getPageOwlProject(tujuan,sumberjenis);
						});
					}else{
						tujuan = site_url(sumberjenis+'.php');
						getPageOwlProject(tujuan,sumberjenis);
					}
				}
			}
			listMail.appendChild(span);
			listMail.appendChild(p);
			listMail.appendChild(spanTanggal);
			
			mailWindow.insertBefore(listMail, mailWindow.firstChild);
			num++;
			if(dataArr.length > num){
				insertData(mailWindow,dataArr,num);
			}else if(dataArr.length == num){
				mailWindow.setAttribute("updated",dataArr[num-1]['tanggal']);
				updateContent(mailWindow,dataArr[num-1]['tanggal']);
			}
		}
	}
	function updateContent(mailWindow,lastdate){
		intervalMailBox = setTimeout(function(){
			url = site_url(setupOption.url)+"?case=0";
			loadDataMailNotif(mailWindow,url,function(ev){
				if (ev.currentTarget.readyState == 4) {
					if (ev.currentTarget.status == 200) {
						var dataArr = JSON.parse(ev.currentTarget.responseText);
						dataTanggal = dataArr.lastdate;
						if(lastdate != dataTanggal){
							getDataMailNotif(mailWindow,lastdate);
						}else{
							updateContent(mailWindow,lastdate);
						}
					}else{
						error_catch(ev.currentTarget.status);
					}
				}
			});
		},setupOption.interval);
	}
	function loadDataMailNotif(mailWindow,url,callF){
		callBack = callF || responsMailNotif;
		getResponseMailNotif(url, callBack);
		function responsMailNotif(ev){
            if (ev.currentTarget.readyState == 4) {
				if (ev.currentTarget.status == 200) {
					if (!isSaveResponse(ev.currentTarget.responseText)) {
							console.log('ERROR Mail Box,\n' + ev.currentTarget.responseText);
					}else {
						try{
							var dataArr = JSON.parse(ev.currentTarget.responseText);
							//console.log(dataArr);
							//lebel = dataArr.lbl.trim();
							content = dataArr.data;
							insertData(mailWindow,content);
							
						}catch(e){
							console.log(e);
							//console.log(ev.currentTarget.responseText);
						}
					}
				}else{
					error_catch(ev.currentTarget.status);
				}
			}
        }
	}
	function getDataMailNotif(mailWindow,lastdate){
		if(isElement(ele)){
			if(typeof lastdate == 'undefined'){
				lastdate = "";
			}
			ele.appendChild(mailWindow);
			url = site_url(setupOption.url)+"?case=data&lastdate="+lastdate;
			loadDataMailNotif(mailWindow,url);
		}
	}
	
	function readnotifAction(e,id,callback){
		param = "id="+id;
		post_response_text('slave_listnotification.php?method=readnotif', param, respon);
		
		function respon() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						e.classList.remove("unread");
						callback();
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
		
	}
	getDataMailNotif(mailWindow);
}
/*******************************************************************************
 ** Chat Function **************************************************************
 *******************************************************************************/
 var hideChatList;
var conNotif = createXMLHttpRequest();

function createChatPop(ele,option){
	var intervalChat;
	// init
	var optionsDefaults = {
		open: {}, 
		interval: 60000, 
		url: "master_slave_chat.php" 
	}
	var setupOption = optExtend(optExtend({}, optionsDefaults),option); 
	var chatWindow = document.createElement("div");
	chatWindow.id = "chatWindow";
	chatWindow.classList.add("chat-window");
	chatWindow.classList.add("hide");
	var title = document.createElement("div");
	title.classList.add("title");
	title.style = "cursor:pointer";
	title.onclick = function(){
		chatPop();
	}
	if(Array.isArray(setupOption.open) && setupOption.open.length > 0){
		for(i=0; i<setupOption.open.length; i++){
			setupOption.open[i].onclick = function(){
				chatPop();
			}
			setupOption.open[i].onmouseover = function(){
				alwaysUp(this);
			}
			setupOption.open[i].onmouseleave = function(){
				alwaysHide(this);
			}
		}
	}
	var numNewMssg = document.createElement("span");
	numNewMssg.id = "lblnotification";
	numNewMssg.classList.add("lblnotification");
	var titleTxt = document.createElement("span");
	titleTxt.innerHTML = "Reminder System";
	var titleTxt_2 = document.createElement("span");
	titleTxt_2.id = "chatWindowTitle";
	title.appendChild(numNewMssg);
	title.appendChild(titleTxt);
	title.appendChild(titleTxt_2);
	
	var contentBox = document.createElement("div");
	contentBox.id = "chatWindowContact";
	contentBox.style = "display:none;";
	chatWindow.appendChild(title);
	chatWindow.appendChild(contentBox);
	if(isElement(ele)){
		ele.appendChild(chatWindow);
		loadDataNotif();
	}
	
	function optExtend(target, source) {
		target = target || {};
		for (var prop in source) {
		  // Go recursively
		  if (isObject(source[prop])) {
			target[prop] = optExtend(target[prop], source[prop])
		  } else {
			target[prop] = source[prop]
		  }
		}
		return target;
	}
	function isObject(o){
		return Object.prototype.toString.call(o) === '[object Object]';
	}
	function isElement(o){
		return (
		  o instanceof HTMLElement || //DOM2
		  (o && typeof o === 'object' && o !== null && o.nodeType === 1 && typeof o.nodeName === 'string')
		);
	}
	
	/*
	<div id="chatWindow" class="chat-window <?php if($lbl != ""){echo "show";}else{echo "hide";}; ?>">
		<div class='title' onclick='chatPop()' style='cursor:pointer'>
			<span id='lblnotification' class="lblnotification" style=""><?php echo $lbl; ?></span>
			<span>Reminder System</span><span id='chatWindowTitle'></span>
		</div>
		<!--<input id='chatWindowSearch' class='search-box'>-->
		<!--<div id='chatWindowContact' style='margin-bottom:250px'>-->
			<div id="chatWindowContact" style='display: none'>
				<div id="div3" style='padding:5px;'><?php echo $tab ?></div>
			</div>
		<!--</div>-->
	</div>*/
	
	function getResponseNotif(tujuan,funct){
		paramNotif=parent.location.href.replace("http://","");
		paramNotif=paramNotif.replace("https://","");
		tujuan+='&par='+paramNotif;                           
		conNotif.open("GET",tujuan,true);
		conNotif.onreadystatechange= eval(funct);
		conNotif.send(null);
	}
	function loadDataNotif(){
		//ajax
		getResponseNotif(setupOption.url, responsNotif);
		function responsNotif(){
            if (conNotif.readyState == 4) {
				if (conNotif.status == 200) {
					if (!isSaveResponse(conNotif.responseText)) {
							alert('ERROR Notification,\n' + conNotif.responseText);
					}else {
						try{
							var dataArr = JSON.parse(conNotif.responseText);
							lebel = dataArr.lbl.trim();
							content = dataArr.content.trim();
							updateContent(lebel,content);
						}catch(e){
							console.log(e);
							console.log(conNotif.responseText);
						}
					}
				}else{
					error_catch(conNotif.status);
				}
			}
        }
	}
	function updateContent(lbl,content){
		numNewMssg.innerHTML = lbl;
		contentBox.innerHTML = content;
		if(contentBox.style.display=='none') {
			if(lbl != ""){
				alwaysUp();
			}
		}
		intervalChat = setTimeout(function(){
			loadDataNotif();
		},setupOption.interval);
	}
	function alwaysHide(){
		var chatContainer = getById('chatContainer');
		if(contentBox.style.display == "none"){
			hideChatList = setTimeout(function(){
				notifAct = numNewMssg.getElementsByClassName('badge1');
				if(notifAct.length == 0 ){
					chatWindow.classList.remove("show");
					chatWindow.classList.add("hide");
					chatContainer.innerHTML = "Reminder System";
				}
			},3000);
		}
	}
	function alwaysUp(ele){
		var chatContainer = getById('chatContainer');
		if(contentBox.style.display == "none"){
			if(hideChatList){
				clearTimeout(hideChatList); 
			}
			chatWindow.classList.remove("hide");
			chatWindow.classList.add("show");
			chatContainer.innerHTML = "";
		}
	}
	function chatPop() {
		if(contentBox.style.display=='none') {
			contentBox.style.display = "";
			chatWindow.classList.remove("hide");
			chatWindow.classList.add("show");
			if(document.getElementById('chatContainer')){
				document.getElementById('chatContainer').innerHTML = "";
			}
			if(hideChatList){
				clearTimeout(hideChatList); 
			}
		} else {
			contentBox.style.display = "none";
			hideChatList = setTimeout(function(){
				notifAct = numNewMssg.getElementsByClassName('badge1');
				if(notifAct.length == 0 ){
					chatWindow.classList.remove("show");
					chatWindow.classList.add("hide");
					if(document.getElementById('chatContainer')){
						document.getElementById('chatContainer').innerHTML = "Reminder System";
					}
				}
			},3000);
		}
	}
}



function showmorenotif(){
	document.getElementById('divmore').style.display='none';
	xnotif=document.getElementById('xnotif').value;
	param='method=showmorenotif&xnotif='+xnotif;
	tujuan='slave_listnotification.php';
	post_response_text(tujuan, param, respog);
	
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) 
			{
				busy_off();
                if (!isSaveResponse(con.responseText)) 
				{
					alert(con.responseText);
				}
				else 
				{
					split = con.responseText.split("####");
					document.getElementById('divshowmore_'+xnotif).innerHTML=split[0];
					document.getElementById('xnotif').value=split[1];
					if(split[2]=='1'){
						document.getElementById('divmore').style.display='';
					}
				}
			}
			else 
			{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}

var timernotif;
function readnotif(e,id) {
	clearTimeout(timernotif);
}

function readnotif2(e,id){
	if(document.getElementById('divnotif_'+id).style.background=='#FFFFFF'){
		
	}else{
		timernotif = setTimeout(function() {
			param = "id="+id;
			post_response_text('slave_listnotification.php?method=readnotif', param, respon);
		},1000);
		function respon() {
			if (con.readyState == 4) {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					} else {
						document.getElementById('divnotif_'+id).style.background = '#FFFFFF';
						document.getElementById('lblnotification').innerHTML=con.responseText;
					}
				} else {
					busy_off();
					error_catch(con.status);
				}
			}
		}
	}
}

function markasread(id){	
	param = "id="+id;
	post_response_text('slave_listnotification.php?method=markasread', param, respon);
	
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('tablenotif_'+id).className = "tagntfoff";
					document.getElementById('imgnotif_'+id).src = "images/read.png";
					document.getElementById('imgnotif_'+id).title = "Mark as Unread";
					document.getElementById('imgnotif_'+id).setAttribute('onclick','markasunread('+id+')');
					document.getElementById('lblnotification').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function markasunread(id){	
	param = "id="+id;
	post_response_text('slave_listnotification.php?method=markasunread', param, respon);
	
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('tablenotif_'+id).className = "tagntf";
					document.getElementById('imgnotif_'+id).src = "images/unread.png";
					document.getElementById('imgnotif_'+id).title = "Mark as Read";
					document.getElementById('imgnotif_'+id).setAttribute('onclick','markasread('+id+')');
					document.getElementById('lblnotification').innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}


function upperCaseF(a)
{
	setTimeout(function(){
        a.value = a.value.toUpperCase();
    }, 1);
}

function enterkey(ev,func)
{
	key = getKey(ev);
	if(key==13)
	{
		func();
	}
}

function zeroPad(number,length)
{
	var my_string = ''+number;
    while (my_string.length < length) {
        my_string = '0' + my_string;
    }

    return my_string;
}

function getdatenow(tipe)
{
	var valtgl = "";
	var tgl = new Date();
	
	if(tipe==2)
	{
		var tgl = new Date(tgl.getFullYear(), tgl.getMonth(), tgl.getDate()+7);
	}
	var tmphari = tgl.getDate();
	var tmpbulan = tgl.getMonth()+1;
	
	var hari = zeroPad(tmphari,2);
	var bulan = zeroPad(tmpbulan,2);
	var tahun = tgl.getFullYear();
	
	if(tipe==1 || tipe==2)
	{
		valtgl = hari+'-'+bulan+'-'+tahun;
	}
	else
	{
		valtgl = hari+'-'+bulan+'-'+tahun;		
	}
	return valtgl;
}

function nextweek()
{
    var today = new Date();
    
    return nextweek;
}

/*******************************************************************************
	#Author - Atwal
	function headerfixed ada di js/headerfixed.min.js atau js/headerfixed.js
	##Cara Pemanggilanya :
	1. Buat Button dengan class yang sudah di tentukan =>
		a. Name Tag Bebas, namun class harus "fixheadbtn" untuk memanggil table.
		b  Name Tag Bebas, namun class harus "clearfixheadbtn" untuk membuang fix header table.
	2. Masukkan nama table dengan attribute (table=""). *table wajib class name.
	3. Masukkan pembungkusnya (DIV) dengan attribute (both=""). *DIV wajib id name atau tulis window apabila tanpa pembungkus.
	4. masukkan anggka 0 dengan attribute (shown="") apabila pakai DIV (pembungkus)
	
	##example button :
		<a class='fixheadbtn' table='data-table' both='tabel' shown='0' >Header Table Fixed</a>
		<a class='clearfixheadbtn' table='data-table'>Remove Header Table Fixed</a>

 *******************************************************************************/
var fxhead = [];
function Create_HeadFixed(e,b,n,num){
		if(fxhead[num]){
			fxhead[num].clean();
		}
		var tables = document.getElementsByClassName(e);
		_yo = parseInt(n);
		if(b === 'window'){
			b = window;
		}else{
			b = b;
		}
		if(_yo === 0){
			fxhead[num] = new KepalaTableNgegantung(e,b,0);
		}else{
			fxhead[num] = new KepalaTableNgegantung(e,b);
		}
		
		for(fx=0; fx<tables.length; fx++){
			if (tables[fx].getAttribute("position") === "fullscreen"){
				document.getElementById(e+'-owl'+fx).style.zIndex = "1000";
			}
			th = tables[fx].tHead.querySelectorAll("th");
			for (var i = 0; i < th.length; ++i) {
				th[i].style.top = PageY(tables[fx],tables[fx].parentNode)+"px";
				th[i].onclick = function(){
					idx = this.cellIndex;
					leftFix(tables[fx],idx);
				}
			}
		}

	}
	function leftFix(table,idx){
			var leftRc = new Array();
			var reduse = 0;
			reactTable =table.getBoundingClientRect();
			var rt = reactTable.left;
			for (var i = 0; i < table.rows.length; ++i) {
				cells = table.rows[i].cells;
				reduse = 0;
				for (var xi = 0; xi < cells.length; ++xi) {
					if(cells[xi].cellIndex <= idx){
						cells[xi].classList.add("left-fixed");
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
						
					}else if(cells[xi].cellIndex > idx && cells[xi].classList.contains('left-fixed')){
						cells[xi].classList.remove("left-fixed");
						cells[xi].style.left = null;
					}else{
						break;
					}
				}
			}
		}
	function PageY(a,tobody){
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
	function paint(){
		for (var i_0 = 0; i_0< dataTable.length; ++i_0) {
			
		}
	}
	function full_screen(b,bh,bb,e){
		document.documentElement.scrollTop = 0; // For IE and Firefox
		document.body.scrollTop = 0; // For Chrome, Safari and Opera 
		for(fx=0; fx<fxhead.length; fx++){
			if(fxhead[fx]){
				fxhead[fx].clean();
			}
		}
		var both 	= document.getElementById(b);
		var headB 	= document.getElementById(bh);
		var bodyB 	= document.getElementById(bb);
		//console.log(bodyB);
		var styH	= headB.getAttribute("style");
		var styB	= bodyB.getAttribute("style");
		var getPos	= both.getAttribute("position");
		var tbl		= e.getAttribute("table");
		var th		= document.getElementsByClassName(tbl);
		var html 	= document.body.parentNode;
		
		if(getPos === "fullscreen"){
			both.setAttribute("position", "noscreen");
			for(i=0; i<th.length; i++){
				th[i].setAttribute("position","noscreen");
			}
			var tempstyH		= headB.getAttribute("tempsty");
			var tempstyB		= bodyB.getAttribute("tempsty");
			headB.setAttribute("tempsty", styH);
			bodyB.setAttribute("tempsty", styB);
			headB.style = tempstyH;
			bodyB.style = tempstyB;
			both.style 	= "";
			if(e.tagName === "input"){
				e.value = "Full Screen";
			}else{
				e.innerHTML = "<img title='Full Screen' class=resicon src=images/full-screen.png>";
			}
			html.style = '';
		}else{
			both.setAttribute("position", "fullscreen");
			for(x=0; x<th.length; x++){
				th[x].setAttribute("position","fullscreen");
			}
			var h		= window.innerHeight;
			var w		= window.innerWidth;
			Hh 	= 20;
			pdH = 5;
			pdH2= (pdH*2);
			headB.setAttribute("tempsty", styH);
			bodyB.setAttribute("tempsty", styB);
			headB.style = "background:#76B1DA;height:"+Hh+"px;padding:"+pdH+"px;";
			bodyB.style = "background-color:rgba(255,255,255,0.8);overflow:auto;width:100%;height:"+(parseInt(h)-(parseInt(Hh)+parseInt(pdH2)))+"px;";
			both.style = "position:absolute;top:0;bottom:0;left:0;right:0;z-index:1000;";
			if(e.tagName === "input"){
				e.value = "Close";
			}else{
				e.innerHTML = "<img title='Full Screen' class=resicon src='images/remove-full-screen.png'>";
			}
			html.style = 'overflow:hidden;';
		}
	}
window.addEventListener("load",function(){
	var fixheadbtn = document.getElementsByClassName('fixheadbtn');
	//console.log(fixheadbtn.length);
	var clearfixheadbtn = document.getElementsByClassName('clearfixheadbtn');
	for(xi=0; xi<fixheadbtn.length; xi++){
		fixheadbtn[xi].addEventListener("click",function(){
			var table	= this.getAttribute('table');
			var both	= this.getAttribute('idbothbody');
			var shown	= this.getAttribute('shown');
			Create_HeadFixed(table,both,shown,xi);
			
		});
	}
	
	for(fx=0; fx<clearfixheadbtn.length; fx++){
		clearfixheadbtn[fx].addEventListener("click",function(){
			for(fx=0; fx<fxhead.length; fx++){
				if(fxhead[fx]){
					fxhead[fx].clean();
				}
			}	
		});
	}
	
	var fc_btn = document.getElementsByClassName('fc_btn');
	for(fx=0; fx<fc_btn.length; fx++){
		fc_btn[fx].addEventListener("click",function(){
			var idboth		= this.getAttribute('idboth');
			var idbothhead	= this.getAttribute('idbothhead');
			var idbothbody	= this.getAttribute('idbothbody');
			full_screen(idboth,idbothhead,idbothbody,this);
		});
	}
	
});
/***  
format duit prototype javascript
 ***/
Number.prototype.formatMoney = function(c, d, t){
var n = this, 
    c = isNaN(c = Math.abs(c)) ? 2 : c, 
    d = d == undefined ? "." : d, 
    t = t == undefined ? "," : t, 
    s = n < 0 ? "-" : "", 
    i = String(parseInt(n = Math.abs(Number(n) || 0).toFixed(c))), 
    j = (j = i.length) > 3 ? j % 3 : 0;
   return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
 };

/*******************************************************************************
	#END : function headerfixed
 *******************************************************************************/

 function numberFormat(number,digit) {
      number = parseFloat(number.toString().match(/^-?\d+\.?\d{0,2}/));
      //Seperates the components of the number
      var components = (parseFloat(number).toFixed(digit)).split(".");
      //Comma-fies the first part
      components [0] = components [0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      //Combines the two sections
      return components.join(".");
}

function showontop(){
	(function smoothscroll(){
		var currentScroll = document.documentElement.scrollTop || document.body.scrollTop;
		if (currentScroll > 0) {
			 window.requestAnimationFrame(smoothscroll);
			 window.scrollTo (0,currentScroll - (currentScroll/5));
		}
	})();
}

function isNumber(num,evt){
	var reg = /^0+/gi;
    if (num.value.match(reg)) {
        num.value = num.value.replace(reg, '');
    }
	evt = (evt) ? evt : window.event;
	var charCode = (evt.which) ? evt.which : evt.keyCode;
	if(charCode > 31 && (charCode < 48 || charCode > 57)){
		return false;
	}
	return true;
}
/** Author : Atwal Arifin  
	@param fileattach : id input type="file"
	@param callback : function collback untuk result 
	@param button	: button (this) button untuk getElement event
	Exam : log_pnwrharga.js
**/
function readFileCSV(fileattach,callback,button){
	ele = document.getElementById(fileattach);
	ele.onchange = function(){
		uploadCSV_openFileExcel(this);
	}
	ele.click();
	//readFIle 
	function uploadCSV_openFileExcel(ele){
		function csvJSON(csv,dm){
			var data = [];
			var lines=csv.split('\r');
			var newLine = new Array();
			for(let i = 0; i<lines.length; i++){
				lineClear = lines[i].replace(/\s+/g,'');//delete all blanks and space 
				if(lineClear != ""){
					newLine.push(lineClear);
				}
			}
			var delimiter = "";
			if(newLine.length > 0){
				for(var x=0; x<dm.length; x++){
					checkDelim = newLine[0].split(dm[x]);
					if(checkDelim.length >1){
						delimiter = dm[x];
						break;
					}
				}
				
				if(delimiter == ""){
					alert("Delimeter tidak bisa membaca isi file.")
					return false;
				}
				for(var i=0;i<newLine.length;i++){
					row = new Array();
					var currentline = newLine[i].split(delimiter);
					for(var j=0;j<currentline.length;j++){
						row.push(currentline[j]);
					}
					if(row.length > 0){
						data.push(row);
					}
				}				
			}
			return data; //JavaScript object
			//return JSON.stringify(result); //JSON
		}
		var f = ele.files[0];
		var dm = new Array();
		var delimeter = ele.getAttribute("delimiter");
		if(delimeter == null || delimeter == ""){
			alert("Delimeter tidak diketahui.");
			return false;
		}else{
			for(i_0x=0; i_0x<delimeter.length; i_0x++){
				dm.push(delimeter[i_0x]);
			}
		}
		if (f) {
			var r = new FileReader();
			r.onload = function(e) { 
				ele.value = "";
				fileData  = e.target.result;
				result = csvJSON(fileData,dm);
				if(typeof callback !== "undefined"){
					eval(callback(result,button));
				}
			};
			r.onerror = function (ex) {
				console.log(ex);
			};
			r.readAsText(f);
		} else { 
			alert("Failed to load file");
		}

	}
}
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

/***  
Author : Atwal Arifin  
tahun  : 2019 
Copy from excel
 ***/

//Cara pakai di php
//<input type="text" onkeypress=\"MaskForm(event, '99.999.999.9-999.999')\">
function MaskForm(event, mask) {
	with (event) {
		stopPropagation()
		preventDefault()
		if (!charCode) return
		var c = String.fromCharCode(charCode)
		if (c.match(/\D/)) return
		with (target) {
			var val = value.substring(0, selectionStart) + c + value.substr(selectionEnd)
			var pos = selectionStart + 1
		}
	}
	var nan = count(val, /\D/, pos) // nan va calcolato prima di eliminare i separatori
	val = val.replace(/\D/g,'')

	var mask = mask.match(/^(\D*)(.+9)(\D*)$/)
	if (!mask) return // meglio exception?
	if (val.length > count(mask[2], /9/)) return

	for (var txt='', im=0, iv=0; im<mask[2].length && iv<val.length; im+=1) {
		var c = mask[2].charAt(im)
		txt += c.match(/\D/) ? c : val.charAt(iv++)
	}

	with (event.target) {
		value = mask[1] + txt + mask[3]
		selectionStart = selectionEnd = pos + (pos==1 ? mask[1].length : count(value, /\D/, pos) - nan)
	}

	function count(str, c, e) {
		e = e || str.length
		for (var n=0, i=0; i<e; i+=1) if (str.charAt(i).match(c)) n+=1
		return n
	}
}
function exportTabletoExcel(filename,idboth) {
	var appname= "";
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
	
	function exec(){
		//data-type : ["DateTime","Number","Boolean","Error"];
		//data-style : ["Date","Currency"];
		//data-value : isi yang akan di keluarkan;
		both = document.getElementById(idboth);
		tables = both.getElementsByTagName("table");
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
	exec();
}


function export_table_to_csv(filename,idboth,spliter) {
	
	var both = document.getElementById(idboth);
	var tables = both.getElementsByTagName("table");
	var namafiledownload = filename || 'Workbook.csv';
	if(typeof spliter == 'undefined' || spliter == null || spliter == ""){
		spliter = ",";
	}
	if(tables.length > 0){
		for (var i = 0; i < tables.length; i++) {
			var csv = new Array();
			check_print = tables[i].getAttribute("data-print");
			if(check_print == "true"){
				name = tables[i].getAttribute("name");
				if (!tables[i].nodeType) tables[i] = document.getElementById(tables[i]);
				//console.log(tables[i].rows);
				for (var j = 0; j < tables[i].rows.length; j++) {
					var row =  new Array();
					for (var k = 0; k < tables[i].rows[j].cells.length; k++) {
						row.push(tables[i].rows[j].cells[k].innerText.trim());
					}
					csv.push(row.join(spliter));	
				}
				if(typeof name !== 'undefined' && spliter !== null && spliter !== ""){
					namafiledownload = name;
				}
				namafile = nameFileCSV(namafiledownload);
				 // Download CSV
				download_csv(csv.join("\n"), namafile);
			}
		}
	}
	function nameFileCSV(name) {
		var ext = "csv";
		var result = name.toLowerCase();
		var namefile = result.split(".");
		if(namefile.length > 1){
			var found = namefile.find(function(element) { 
			  return element == ext; 
			}); 
			if(found){
				return result;
			}else{
				return result+"."+ext;
			}
		}else{
			return result+"."+ext;
		}
	}
	function download_csv(csv,namafiledownload) {
		var csvFile;
		var downloadLink;
		
		 // CSV FILE
		csvFile = new Blob([csv], {type: "text/csv"});

		// Download link
		downloadLink = document.createElement("a");

		// File name
		downloadLink.download = namafiledownload;
		// We have to create a link to the file
		downloadLink.href = window.URL.createObjectURL(csvFile);

		// Make sure that the link is not displayed
		downloadLink.style.display = "none";

		// Add the link to your DOM
		document.body.appendChild(downloadLink);
		// Lanzamos
		downloadLink.click();
	}
}

function printnopopup(url) {
    var ifrm = document.createElement("iframe");
    ifrm.setAttribute("src", url);
    ifrm.style.display = 'none';
    document.body.appendChild(ifrm);
}
