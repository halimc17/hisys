// Copyright 2012 © Gavin Kistner, !@phrogz.net
// License: http://phrogz.net/JS/_ReuseLicense.txt

/*******************************************************************
 SVGPanUnscale.js
 Allows selected elements that have been zoomed by SVGPan to keep
 their original size while still being placed correctly.

 Also undoes rotation/skew (any transform other than translation).

 USAGE:  unscaleEach('.non-scaling');
*******************************************************************/
function getRoot(root) {
	if(typeof(svgRoot) == "undefined") {
		var g = null;

		g = root.getElementById("viewport");

		if(g == null)
			g = root.getElementsByTagName('g')[0];

		if(g == null)
			alert('Unable to obtain SVG root element');

		setCTM(g, g.getCTM());

		g.removeAttribute("viewBox");

		svgRoot = g;
	}

	return svgRoot;
}

function setCTM(element, matrix) {
	var s = "matrix(" + matrix.a + "," + matrix.b + "," + matrix.c + "," + matrix.d + "," + matrix.e + "," + matrix.f + ")";

	element.setAttribute("transform", s);
}


// Undo the scaling to selected elements inside an SVGPan viewport
function unscaleEach(selector){
  if (!selector) selector = "g.non-scaling > *";
  window.addEventListener('mousewheel',     unzoom, false);
  window.addEventListener('DOMMouseScroll', unzoom, false);
  function unzoom(evt){
    // getRoot is a global function exposed by SVGPan
    var r = getRoot(evt.target.ownerDocument);
    [].forEach.call(r.querySelectorAll(selector), unscale);
  }
}

// Counteract all transforms applied above an element.
// Apply a translation to the element to have it remain at a local position
/*function unscale(el){
  var svg = el.ownerSVGElement;
  var xf = el.scaleIndependentXForm;
  if (!xf){
    // Keep a single transform matrix in the stack for fighting transformations
    // Be sure to apply this transform after existing transforms (translate)
    xf = el.scaleIndependentXForm = svg.createSVGTransform();
    el.transform.baseVal.appendItem(xf);
  }
  
  // SVGElement.prototype.getTransformToElement = SVGElement.prototype.getTransformToElement || function(toElement) {
	// return toElement.getScreenCTM().inverse().multiply(this.getScreenCTM());  
	// };
  var m = svg.getScreenCTM().inverse().multiply(el.getScreenCTM());
  // var m = svg.getTransformToElement(el.parentNode);
  m.e = m.f = 0; // Ignore (preserve) any translations done up to this point
  xf.setMatrix(m);
}*/

function unscale(el){
    var svg = el.ownerSVGElement;
	var xf = el.ownerSVGElement.createSVGTransform();
    var m = svg.getScreenCTM().inverse().multiply(el.getScreenCTM());
    m.e = m.f = 0; // Ignore (preserve) any translations done up to this point
    xf.setMatrix(m);

    // Keep a single transform matrix in the stack for fighting transformations
    // Be sure to apply this transform after existing transforms (translate)
    var SVG_TRANSFORM_MATRIX = 1;
    var SVG_TRANSFORM_TRANSLATE = 2;
    var baseVal = el.transform.baseVal;
    if(baseVal.numberOfItems == 0)
        baseVal.appendItem(xf);
    else{
        for(var i = 0; i < baseVal.numberOfItems; ++i){
            if(baseVal.getItem(i).type == SVG_TRANSFORM_TRANSLATE && i == baseVal.numberOfItems - 1){
				baseVal.appendItem(xf);
            }
			
			if(baseVal.getItem(i).type != SVG_TRANSFORM_TRANSLATE){
				if(baseVal.getItem(i).type == SVG_TRANSFORM_MATRIX)
                    baseVal.replaceItem(xf, i);
                else
                    baseVal.insertItemBefore(xf, i);
                break;
            }
        }
    }
}