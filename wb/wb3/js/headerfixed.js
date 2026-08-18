function _GetElementById(a){
	if(typeof(a)=="string"){
		if(document.getElementById){
			a=document.getElementById(a);
			//alert(a.getAttribute('class'));
		}else{
			if(document.all){
				a=document.all[a]
			}else{
				a=null
			}
			//alert('document.all');
		}
	}
	return a
}

function _PageX(b){
	var a=0;
	b=_GetElementById(b);
	while(b){
		if(_Def(b.offsetLeft)){
			a+=b.offsetLeft
		}
		b=_Def(b.offsetParent)?b.offsetParent:null
	}
	return a
}

function _PageY(a){
	var b=0;a=_GetElementById(a);
	while(a){if(_Def(a.offsetTop)){
		b+=a.offsetTop}a=_Def(a.offsetParent)?a.offsetParent:null
	}
	return b
}

function _ScrollLeft(c,b){
	var a,d=0;
	if(!_Def(c)||b||c==document||c.tagName.toLowerCase()=="html"||c.tagName.toLowerCase()=="body"){
			a=window;
			if(b&&c){a=c}
			if(a.document.documentElement&&a.document.documentElement.scrollLeft){
				d=a.document.documentElement.scrollLeft
			}else{
				if(a.document.body&&_Def(a.document.body.scrollLeft)){
					d=a.document.body.scrollLeft
				}
			}
		}else{
			c=_GetElementById(c);
			if(c&&_Num(c.scrollLeft)){
			d=c.scrollLeft
			}
		}
	return d
}

function _Camelize(d){
	var e,g,b,f;b=d.split("-");
	f=b[0];
	for(e=1;e<b.length;++e){
		g=b[e].charAt(0);
		f+=b[e].replace(g,g.toUpperCase())
		}
	return f
}

function _GetComputedStyle(g,f,c){
	if(!(g=_GetElementById(g))){
		return null
	}
	var d,a="undefined",b=document.defaultView;
	if(b&&b.getComputedStyle){
		d=b.getComputedStyle(g,"");
		if(d){
			a=d.getPropertyValue(f)
		}
	}
	else{
		if(g.currentStyle){
			a=g.currentStyle[_Camelize(f)]
			}else{
		return null
		}
	}return c?(parseInt(a)||0):a
}
		
function _GetElementsByTagName(a,c){
	var b=null;
	a=a||"*";
	c=_GetElementById(c)||document;
	if(typeof c.getElementsByTagName!="undefined"){
		b=c.getElementsByTagName(a);
		if(a=="*"&&(!b||!b.length)){
			b=c.all
		}
	}else{
		if(a=="*"){
			b=c.all
		}else{
			if(c.all&&c.all.tags){
				b=c.all.tags(a)
			}
		}
	}
return b||[]
}

function _ScrollTop(c,b){
	var a,d=0;
	if(!_Def(c)||b||c==document||c.tagName.toLowerCase()=="html"||c.tagName.toLowerCase()=="body"){
		a=window;
		
		if(b&&c){
			a=c
		}
		if(a.document.documentElement&&a.document.documentElement.scrollTop){
			d=a.document.documentElement.scrollTop
		}else{
			if(a.document.body&&_Def(a.document.body.scrollTop)){
				d=a.document.body.scrollTop
			}
		}
	}else{
		c=_GetElementById(c);
		if(c&&_Num(c.scrollTop)){
			d=c.scrollTop
		}
	}
	//alert(d);
	return d
}

function _RemoveEventListener(d,c,b,a){
	if(!(d=_GetElementById(d))){
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

function _GetElementsByClassName(k,b,n,h){
	var a=[],m,j,g,d;
	m=new RegExp("(^|\\s)"+k+"(\\s|$)");
	j=_GetElementsByTagName(n,b);
	for(g=0,d=j.length;g<d;++g){
		if(m.test(j[g].className)){
			a[a.length]=j[g];
			if(h){
				h(j[g])
			}
		}
	}
	return a
}

function _Def(){
	for(var b=0,a=arguments.length;b<a;++b){
		if(typeof(arguments[b])==="undefined"){
			return false
		}
	}
	return true
}

function _Num(){
	for(var b=0,a=arguments.length;b<a;++b){
		if(isNaN(arguments[b])||typeof(arguments[b])!=="number"){
			return false
		}
	}
	return true
}

var _IE4Up, _IE8Up, _WebKit = navigator.userAgent.indexOf('AppleWebKit') > 0, _Gecko = !_WebKit && navigator.product == 'Gecko';

/*@cc_on
@if (@_jscript)
  _IE4Up = true;
  @if (@_jscript_version > 5.7)
    _IE8Up = true;
  @end
@end @*/
//------------------------------------------------------------------------------

function KepalaTableNgegantung(tCls, tCon, yOfs)
{
	// Public Methods
  this.init = function(a, b, c, d)
  {
    this.clean();
    return _ctor(a, b, c, d);
  };

  this.paint = function()
  {
    _event({type:'resize'});
  };

  this.clean = function()
  {
    _dtor();
  };
	// Private Properties
  var _i = this,
    _ic, // is collapse
    _ce, // container element
    _ta, // table array
    _iw, // container is window
    _bl = 0, _bt = 0, // container border left and top
    _yo; // y-offset
	// Constructor Code
  if (tCls) { _ctor(tCls, tCon, yOfs); }
//
  // Private Methods
	function _ctor(tCls, tCon, yOfs)
  {
    var i, h, t;
	//Get Element 
    _ta = _GetElementsByClassName(tCls, document, 'table');
    _ce = _GetElementById(tCon);

	//END : Get Element 
    if (!_ta || !_ta.length || !_ce) { 
		return false;
	}
    if (!(_iw = _Def(_ce.location))){ 
		_ce.scrollTop = 0; // Definision of scrollTop container
	}
    _yo = yOfs;
    // Create a header table for each table with tCls.
    for (i = 0; i < _ta.length; ++i) {
      h = _ta[i].tHead;
      if (h){
        t = document.createElement('table');
        t.style.position = (_iw ? 'fixed' : 'absolute');
        t.className = tCls + ' owl-no-print';
        t.style.top = '0';
        if (_ta[i].cellSpacing !== '') { t.cellSpacing = _ta[i].cellSpacing; }
        t.appendChild(h.cloneNode(true));
        t.id = _ta[i].xthfHdrTblId = tCls + '-owl' + i;
        _ta[i].xthfResize = 3; // do resize while > 0
        if (_iw && _Num(_yo)) {
          if (_yo === 0) { t.style.top = _PageY(_ta[i]) + 'px'; }
          else { t.style.top = _yo + 'px'; }
        }
        document.body.appendChild(t);
      }
      else {
        _ta[i] = null;
      }
    }
    _ic = _GetComputedStyle(_ta[0], 'border-collapse') == 'collapse';
    if (!_iw && !_IE8Up && !window.opera) {
      _bl = _GetComputedStyle(_ce, 'border-left-width', true),
      _bt = _GetComputedStyle(_ce, 'border-top-width', true);
    }
    _event({type:'resize'});
    _ce.addEventListener('scroll', _event, false);
    window.addEventListener('resize', _event, false);
    window.addEventListener('unload', _dtor, false);
    return true;
  }	
	function _dtor()
	{
		var i, ht;
		if (_ce) {
		  _RemoveEventListener(_ce, 'scroll', _event);
		  _RemoveEventListener(window, 'resize', _event);
		  _RemoveEventListener(window, 'unload', _dtor);
		  // Remove the header tables from the DOM.
		  for (i = 0; i < _ta.length; ++i) {
			ht = _GetElementById(_ta[i].xthfHdrTblId);
			if (ht) { document.body.removeChild(ht); }
			_ta[i] = null;
		  }
		  _ta = null;
		  _ce = null;
		}
	}
	
	function _event(e) // handles scroll and resize events
	{
		var i, r;
		e = e || window.event;
		r = e.type == 'resize';
		for (i = 0; i < _ta.length; ++i) {
	//      if (s.length) s += ', '; // DEBUG
	//      s += _paint(_ta[i], r); // DEBUG
		  _paint(_ta[i], r);
		}
	//    Console.log(s); // DEBUG
	}
	function _paint(t, r)
	{
		var i, bl = 0, br = 0, c1, c2, cl, ct, ht, pl, sl, st, ty, thy;
		if (!t) { return; }
		ht = _GetElementById(t.xthfHdrTblId);
		st = _ScrollTop(_ce, _iw) + (_yo || 0);
		if (_iw) { ty = _PageY(t); }
		else { ty = t.offsetTop; }
		thy = ty + t.rows[0].offsetTop;
		//if (!_iw && !_IE8Up && !window.opera) {
		if (!_iw && !window.opera) {
		  thy -= _GetComputedStyle(t, 'border-top-width', 1);
		}

    if (st <= thy || st > ty + t.offsetHeight - ht.offsetHeight){
      t.xthfResize = 3; // do resize while > 0
      if (_yo !== 0) {
        ht.style.left = '-10000px'; // hide it
//        s += 'hide'; // DEBUG
        return;
//        return s + ']'; // DEBUG
      }
//      else s += 'no-hide'; // DEBUG
    } // else show it...
//    else s += 'show'; // DEBUG

    // Position
    ht.style.left = (_PageX(t) - _ScrollLeft(_ce, _iw) + _bl) + 'px';
    if (!_iw) {
		if (_yo === 0) { 
			ht.style.top = (_PageY(t) + _bt) + 'px';
		}else { 
			ht.style.top = (_PageY(_ce) + _bt) + 'px';
		}
    }

    // Resize
    if (t.xthfResize || r) {
      if (window.opera) {
        copyCssWidth(t, ht);
      }
      else {
        bl = _GetComputedStyle(t, 'border-left-width', 1);
        br = _GetComputedStyle(t, 'border-right-width', 1);
        if (_IE8Up || (_Gecko && _ic)) {
          bl = br = 0;
        }
        else if (_WebKit && _ic) {
          bl = Math.round((bl + br) / 2);
          br = 0;
        }
        ht.style.width = (t.clientWidth + bl + br) + 'px';
      }
      c1 = _GetElementsByTagName('th', t.tHead);
      c2 = _GetElementsByTagName('th', ht.tHead);
      for (i = 0; i < c1.length; ++i) {
        if (_IE4Up) {
          c2[i].style.width = (c1[i].clientWidth - parseInt(c1[i].currentStyle.paddingLeft) - parseInt(c1[i].currentStyle.paddingRight)) + 'px';
        }
        else {
          copyCssWidth(c1[i], c2[i]);
        }
      }
      if (t.xthfResize > 0) --t.xthfResize;
    }

    if (!_iw) {
      sl = _ScrollLeft(_ce);
      pl = _GetComputedStyle(_ce, 'padding-left', 1);
      cr = _ce.clientWidth + sl - pl;
      cl = sl - pl;
      ht.style.clip = 'rect(auto,' + cr + 'px,auto,' + (cl < 0 ? '0' : cl) + 'px)';
    }

//    return s + ']'; // DEBUG
  }
	// alert('test');
} // end

function copyCssWidth(s, d)
{
  d.style.borderLeftWidth = _GetComputedStyle(s, 'border-left-width');
  d.style.paddingLeft = _GetComputedStyle(s, 'padding-left');
  d.style.width = _GetComputedStyle(s, 'width');
  d.style.paddingRight = _GetComputedStyle(s, 'padding-right');
  d.style.borderRightWidth = _GetComputedStyle(s, 'border-right-width');

}
